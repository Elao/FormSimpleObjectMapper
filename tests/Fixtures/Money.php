<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) 2016 Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\Tests\Fixtures;

class Money
{
    /** @var float */
    private $amount;

    /** @var string */
    private $currency;

    public function __construct($amount, $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getCurrency()
    {
        return $this->currency;
    }
}
