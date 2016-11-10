<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) 2016 Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\Tests\Integration;

use Elao\FormSimpleObjectMapper\DataMapper\CallbackFormDataToObjectConverter;
use Elao\FormSimpleObjectMapper\DataMapper\SimpleObjectMapper;
use Elao\FormSimpleObjectMapper\Tests\Fixtures\Money;
use Elao\FormSimpleObjectMapper\Tests\Fixtures\MoneyType;
use Elao\FormSimpleObjectMapper\Tests\Fixtures\MoneyTypeConverter;
use Elao\FormSimpleObjectMapper\Tests\Fixtures\SimpleObjectMapperFormExtension;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class FormTypeTest extends FormIntegrationTestCase
{
    /** @var FormBuilder */
    protected $builder;

    /** @var EventDispatcher */
    protected $dispatcher;

    protected function setUp()
    {
        parent::setUp();

        $this->dispatcher = $this->prophesize(EventDispatcher::class)->reveal();
        $this->builder = new FormBuilder(null, null, $this->dispatcher, $this->factory);
    }

    protected function getExtensions()
    {
        return [new SimpleObjectMapperFormExtension()];
    }

    public function provideSimpleObjectMapperOption()
    {
        return [
            [false, true],
            ['foo', true],
            [function (array $data, $originalData) {
            }, false],
            [new CallbackFormDataToObjectConverter(function (array $data, $originalData) {
            }), false],
            [null, false],
        ];
    }

    /**
     * @dataProvider provideSimpleObjectMapperOption
     */
    public function testSimpleObjectMapperOptionExpectations($value, $expectsException = false)
    {
        if ($expectsException) {
            $this->expectException(InvalidOptionsException::class);
        }

        $this->factory->create(
            FormType::class,
            null,
            [
                'simple_object_mapper' => $value,
            ]
        );
    }

    public function testSimpleObjectMapperOptionSetsDataMapper()
    {
        $form = $this->factory->create(
            FormType::class,
            null,
            [
                'simple_object_mapper' => function (array $data, $originalData) {
                },
            ]
        );

        $this->assertInstanceOf(SimpleObjectMapper::class, $form->getConfig()->getDataMapper());
    }

    public function testEmptyDataCallableReturnsNullWithSimpleObjectMapper()
    {
        $form = $this->factory->create(
            FormType::class,
            null,
            [
                'data_class' => \stdClass::class,
                'simple_object_mapper' => function (array $data, $originalData) {
                },
            ]
        );

        $this->assertNull($form->getConfig()->getEmptyData(), $form);
    }

    public function testSimpleObjectMapperOptionProperlyMapsObject()
    {
        $money = new Money(20.5, 'EUR');

        $form = $this->factory->create(
            MoneyType::class,
            $money,
            [
                'simple_object_mapper' => function (array $data, $originalData) use ($money) {
                    $this->assertSame($money, $originalData);

                    $converter = new MoneyTypeConverter();

                    return $converter->convertFormDataToObject($data, $originalData);
                }, ]
        );

        $this->assertSame($money->getAmount(), $form->get('amount')->getData());
        $this->assertSame($money->getCurrency(), $form->get('currency')->getData());

        $form->submit(['amount' => 15.0, 'currency' => 'USD']);

        $newMoney = $form->getData();

        $this->assertNotSame($money, $newMoney);
        $this->assertInstanceOf(Money::class, $newMoney);
        $this->assertSame(15.0, $newMoney->getAmount());
        $this->assertSame('USD', $newMoney->getCurrency());
    }

    public function provideTestSimpleObjectMapperOptionData()
    {
        return [
            [new Money(20.5, 'EUR'), ['amount' => 15.0, 'currency' => 'USD'], new Money(15.0, 'USD')],
            [new Money(20.5, 'EUR'), [], null],
            [null, ['amount' => 15.0, 'currency' => 'USD'], new Money(15.0, 'USD')],
            [null, [], null],
        ];
    }

    /**
     * @dataProvider provideTestSimpleObjectMapperOptionData
     */
    public function testSimpleObjectMapperOption($initialData, $submittedData, $expectedData)
    {
        $form = $this->factory->create(MoneyType::class, $initialData);

        $form->submit($submittedData);

        $this->assertEquals($expectedData, $form->getData());
    }
}
