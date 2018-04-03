<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\Type\Extension;

use Elao\FormSimpleObjectMapper\DataMapper\CallbackFormDataToObjectConverter;
use Elao\FormSimpleObjectMapper\DataMapper\FormDataToObjectConverterInterface;
use Elao\FormSimpleObjectMapper\DataMapper\SimpleObjectMapper;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Type extension easing the value objects manipulation with form types.
 * It internally sets a dedicated data mapper using the normalizer provided in a "value_object_normalizer" form option.
 *
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 */
class SimpleObjectMapperTypeExtension extends AbstractTypeExtension
{
    const MAPPER_OPTION = 'simple_object_mapper';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (false === $options['compound']) {
            return;
        }

        if (!isset($options[static::MAPPER_OPTION])) {
            return;
        }

        $converter = $options[static::MAPPER_OPTION];

        $valueObjectDataMapper = new SimpleObjectMapper($converter, $builder->getDataMapper());

        $builder->setDataMapper($valueObjectDataMapper);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $simpleObjectMapperNormalizer = function (Options $options, $value) {
            if (null === $value) {
                return null;
            }

            if (!$value instanceof FormDataToObjectConverterInterface && is_callable($value)) {
                return new CallbackFormDataToObjectConverter($value);
            }

            return $value;
        };

        $emptyData = function (Options $options, $value) {
            if (isset($options[static::MAPPER_OPTION])) {
                return null; // `empty_data` should be set to `null` when using the `simple_object_mapper` option.
            }

            return $value;
        };

        $resolver
            ->setDefined(static::MAPPER_OPTION)
            ->setDefault('empty_data', $emptyData)
            ->setAllowedTypes(static::MAPPER_OPTION, [FormDataToObjectConverterInterface::class, 'null', 'callable'])
            ->setNormalizer(static::MAPPER_OPTION, $simpleObjectMapperNormalizer)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FormType::class;
    }
}
