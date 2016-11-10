<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) 2016 Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\Tests\Fixtures\Integration\Symfony\Acme\Command;

class AddItemToCartCommand
{
    /** @var string */
    private $reference;

    /** @var int */
    private $quantity;

    public function __construct($reference, $quantity)
    {
        $this->reference = $reference;
        $this->quantity = $quantity;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}
