<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) 2016 Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\Tests\Fixtures\Integration\Symfony\TestBundle\Form\Type;

use Elao\FormSimpleObjectMapper\DataMapper\FormDataToObjectConverterInterface;
use Elao\FormSimpleObjectMapper\Tests\Fixtures\Integration\Symfony\Acme\Command\AddItemToCartCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddItemToCartType extends AbstractType implements FormDataToObjectConverterInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reference', HiddenType::class)
            ->add('quantity', IntegerType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AddItemToCartCommand::class,
            'simple_object_mapper' => $this,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function convertFormDataToObject(array $data, $originalData)
    {
        return new AddItemToCartCommand(
            $data['reference'],
            $data['quantity']
        );
    }
}
