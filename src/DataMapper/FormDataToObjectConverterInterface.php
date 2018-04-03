<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\DataMapper;

/**
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 */
interface FormDataToObjectConverterInterface
{
    /**
     * Convert the form data into an object.
     *
     * @param array       $data         Array of form data indexed by fields names
     * @param object|null $originalData Original data set in the form (after FormEvents::PRE_SET_DATA)
     *
     * @return object|null
     */
    public function convertFormDataToObject(array $data, $originalData);
}
