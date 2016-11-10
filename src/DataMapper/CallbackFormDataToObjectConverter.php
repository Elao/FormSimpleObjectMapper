<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) 2016 Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\DataMapper;

/**
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 */
class CallbackFormDataToObjectConverter implements FormDataToObjectConverterInterface
{
    /**
     * The callable used to map form data to an object.
     *
     * @var callable
     */
    private $converter;

    /**
     * @param callable $converter
     */
    public function __construct(callable $converter)
    {
        $this->converter = $converter;
    }

    /**
     * {@inheritdoc}
     */
    public function convertFormDataToObject(array $data, $originalData)
    {
        return call_user_func($this->converter, $data, $originalData);
    }
}
