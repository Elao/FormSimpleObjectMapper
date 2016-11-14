<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) 2016 Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\Tests\Unit\DataMapper;

use Elao\FormSimpleObjectMapper\DataMapper\FormDataToObjectConverterInterface;
use Elao\FormSimpleObjectMapper\DataMapper\ObjectToFormDataConverterInterface;
use Elao\FormSimpleObjectMapper\DataMapper\SimpleObjectMapper;
use Elao\FormSimpleObjectMapper\Tests\Fixtures\Media\Book;
use Elao\FormSimpleObjectMapper\Tests\Fixtures\Media\Media;
use Elao\FormSimpleObjectMapper\Tests\Fixtures\Media\MediaConverter;
use Elao\FormSimpleObjectMapper\Tests\Fixtures\Media\Movie;
use Elao\FormSimpleObjectMapper\Tests\Fixtures\Money;
use Elao\FormSimpleObjectMapper\Tests\Fixtures\MoneyTypeConverter;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;

class SimpleObjectMapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var FormFactoryInterface */
    protected $factory;

    protected function setUp()
    {
        $this->factory = Forms::createFormFactoryBuilder()->getFormFactory();
    }

    public function testMapDataToFormsUsesOriginalMapper()
    {
        $converter = $this->getMockBuilder(FormDataToObjectConverterInterface::class)->getMock();

        $originalMapper = $this->getMockBuilder(DataMapperInterface::class)->getMock();
        $originalMapper->expects($this->once())->method('mapDataToForms');

        $simpleObjectMapper = new SimpleObjectMapper($converter, $originalMapper);
        $simpleObjectMapper->mapDataToForms(new \stdClass(), []);
    }

    public function testMapDataToFormsUsesConverterOnObjectToFormDataConverterInterfaceInstance()
    {
        $converter = $this->getMockBuilder(ConverterStub::class)->getMock();
        $converter->expects($this->once())->method('convertObjectToFormData')->willReturn([]);

        $originalMapper = $this->getMockBuilder(DataMapperInterface::class)->getMock();
        $originalMapper->expects($this->never())->method('mapDataToForms');

        $simpleObjectMapper = new SimpleObjectMapper($converter, $originalMapper);
        $simpleObjectMapper->mapDataToForms(new \stdClass(), []);
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "object or null", "string" given
     */
    public function testMapDataToFormsUsesThrowsExceptionOnNonObjectOrNullDataWithConverter()
    {
        $converter = $this->getMockBuilder(ConverterStub::class)->getMock();
        $originalMapper = $this->getMockBuilder(DataMapperInterface::class)->getMock();

        $simpleObjectMapper = new SimpleObjectMapper($converter, $originalMapper);
        $simpleObjectMapper->mapDataToForms('data', []);
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "array", "string" given
     */
    public function testThrowsExceptionOnConverterReturningNonArray()
    {
        $converter = $this->getMockBuilder(ConverterStub::class)->getMock();
        $converter->expects($this->once())->method('convertObjectToFormData')->willReturn('data');

        $originalMapper = $this->getMockBuilder(DataMapperInterface::class)->getMock();

        $simpleObjectMapper = new SimpleObjectMapper($converter, $originalMapper);
        $simpleObjectMapper->mapDataToForms(new \stdClass(), []);
    }

    public function testItProperlyMapsObject()
    {
        $money = new Money(20.5, 'EUR');

        $simpleObjectMapper = new SimpleObjectMapper(new FormDataToMoneyConverter($money));

        /** @var FormInterface[] $forms */
        $forms = [
            'amount' => $this->factory->createNamed('amount', NumberType::class),
            'currency' => $this->factory->createNamed('currency'),
        ];

        $simpleObjectMapper->mapDataToForms($money, $forms);

        $this->assertSame(20.5, $forms['amount']->getData());
        $this->assertSame('EUR', $forms['currency']->getData());

        $newMoney = $money;
        $forms['amount']->setData(15.0);
        $forms['currency']->setData('USD');
        $simpleObjectMapper->mapFormsToData($forms, $newMoney);

        $this->assertNotSame($money, $newMoney);
        $this->assertInstanceOf(Money::class, $newMoney);
        $this->assertSame(15.0, $newMoney->getAmount());
        $this->assertSame('USD', $newMoney->getCurrency());
    }

    public function testSettingSimpleObjectMapperOnForm()
    {
        $money = new Money(20.5, 'EUR');

        $simpleObjectMapper = new SimpleObjectMapper(new FormDataToMoneyConverter($money));

        $builder = $this->factory->createBuilder(FormType::class, $money, ['data_class' => Money::class])
            ->add('amount', NumberType::class)
            ->add('currency')
        ;

        $builder->setDataMapper($simpleObjectMapper);
        $form = $builder->getForm();

        $form->submit(['amount' => 15.0, 'currency' => 'USD']);

        $newMoney = $form->getData();

        $this->assertNotSame($money, $newMoney);
        $this->assertInstanceOf(Money::class, $newMoney);
        $this->assertSame(15.0, $newMoney->getAmount());
        $this->assertSame('USD', $newMoney->getCurrency());
    }

    public function testItProperlyMapsObjectWithObjectToFormDataConverter()
    {
        $media = new Book('foo');

        $simpleObjectMapper = new SimpleObjectMapper(new MediaConverter());

        /** @var FormInterface[] $forms */
        $forms = [
            'author' => $this->factory->createNamed('author'),
            'mediaType' => $this->factory->createNamed('mediaType'),
        ];

        $simpleObjectMapper->mapDataToForms($media, $forms);

        $this->assertSame('foo', $forms['author']->getData());
        $this->assertSame('book', $forms['mediaType']->getData());

        $newMedia = $media;
        $forms['author']->setData('bar');
        $forms['mediaType']->setData('movie');
        $simpleObjectMapper->mapFormsToData($forms, $newMedia);

        $this->assertNotSame($media, $newMedia);
        $this->assertInstanceOf(Movie::class, $newMedia);
        $this->assertSame('bar', $newMedia->getAuthor());
    }

    public function testSettingSimpleObjectOnFormWithObjectToFormDataConverter()
    {
        $media = new Book('foo');

        $simpleObjectMapper = new SimpleObjectMapper(new MediaConverter());

        $builder = $this->factory->createBuilder(FormType::class, $media, ['data_class' => Media::class])
            ->add('author')
            ->add('mediaType')
        ;

        $builder->setDataMapper($simpleObjectMapper);
        $form = $builder->getForm();

        $this->assertSame('foo', $form->get('author')->getData());
        $this->assertSame('book', $form->get('mediaType')->getData());

        $form->submit(['author' => 'bar', 'mediaType' => 'movie']);

        $newMedia = $form->getData();

        $this->assertNotSame($media, $newMedia);
        $this->assertInstanceOf(Movie::class, $newMedia);
        $this->assertSame('bar', $newMedia->getAuthor());
    }
}

class FormDataToMoneyConverter extends \PHPUnit_Framework_TestCase implements FormDataToObjectConverterInterface
{
    private $originalData;

    public function __construct($originalData)
    {
        $this->originalData = $originalData;
    }

    public function convertFormDataToObject(array $data, $originalData)
    {
        $this->assertSame($this->originalData, $originalData);

        $converter = new MoneyTypeConverter();

        return $converter->convertFormDataToObject($data, $originalData);
    }
}

interface ConverterStub extends FormDataToObjectConverterInterface, ObjectToFormDataConverterInterface
{
}
