<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\Tests\Fixtures;

use Elao\FormSimpleObjectMapper\DataMapper\FormDataToObjectConverterInterface;

class MoneyTypeConverter implements FormDataToObjectConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * @param Money|null $originalData
     */
    public function convertFormDataToObject(array $data, $originalData)
    {
        // Logic to determine if the result should be considered null according to form fields data.
        if (null === $data['amount'] && null === $data['currency']) {
            return;
        }

        return new Money($data['amount'], $data['currency']);
    }
}
