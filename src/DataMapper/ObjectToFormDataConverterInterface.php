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
interface ObjectToFormDataConverterInterface
{
    /**
     * Convert given object to form data.
     *
     * @param object|null $object The object to map to the form
     *
     * @return array The array of form data indexed by fields names
     */
    public function convertObjectToFormData($object);
}
