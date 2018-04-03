<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\Tests\Unit\DataMapper;

use Elao\FormSimpleObjectMapper\DataMapper\CallbackFormDataToObjectConverter;
use PHPUnit\Framework\TestCase;

class CallbackFormDataToObjectConverterTest extends TestCase
{
    public function testConvertFormDataToObject()
    {
        $data = ['amount' => 15.0, 'currency' => 'EUR'];
        $originalData = (object) $data;

        $converter = new CallbackFormDataToObjectConverter(function ($arg1, $arg2) use ($data, $originalData) {
            $this->assertSame($data, $arg1);
            $this->assertSame($originalData, $arg2);

            return 'converted';
        });

        $this->assertSame('converted', $converter->convertFormDataToObject($data, $originalData));
    }
}
