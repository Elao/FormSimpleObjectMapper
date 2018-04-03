<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\DataMapper;

use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;

/**
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 */
class SimpleObjectMapper implements DataMapperInterface
{
    /** @var FormDataToObjectConverterInterface */
    private $converter;

    /** @var DataMapperInterface|null */
    private $originalMapper;

    public function __construct(
        FormDataToObjectConverterInterface $converter,
        DataMapperInterface $originalMapper = null
    ) {
        $this->converter = $converter;
        $this->originalMapper = $originalMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDataToForms($data, $forms)
    {
        // Fallback to original mapper instance or default to "PropertyPathMapper"
        // mapper implementation if not an "ObjectToFormDataConverterInterface" instance:
        if (!$this->converter instanceof ObjectToFormDataConverterInterface) {
            $propertyPathMapper = $this->originalMapper ?: new PropertyPathMapper();
            $propertyPathMapper->mapDataToForms($data, $forms);

            return;
        }

        $data = $this->convertObjectToFormData($this->converter, $data);

        foreach ($forms as $form) {
            $config = $form->getConfig();

            if ($config->getMapped() && isset($data[$form->getName()])) {
                $form->setData($data[$form->getName()]);

                continue;
            }

            $form->setData($config->getData());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mapFormsToData($forms, &$data)
    {
        $fieldsData = [];
        foreach ($forms as $form) {
            $fieldsData[$form->getName()] = $form->getData();
        }

        $data = $this->converter->convertFormDataToObject($fieldsData, $data);
    }

    private function convertObjectToFormData(ObjectToFormDataConverterInterface $converter, $data)
    {
        if (!is_object($data) && null !== $data) {
            throw new UnexpectedTypeException($data, 'object or null');
        }

        $data = $converter->convertObjectToFormData($data);

        if (!is_array($data)) {
            throw new UnexpectedTypeException($data, 'array');
        }

        return $data;
    }
}
