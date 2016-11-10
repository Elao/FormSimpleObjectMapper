<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) 2016 Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\Tests\Fixtures;

use Elao\FormSimpleObjectMapper\Type\Extension\SimpleObjectMapperTypeExtension;
use Symfony\Component\Form\AbstractExtension;

class SimpleObjectMapperFormExtension extends AbstractExtension
{
    public function getTypeExtensions($name)
    {
        $ext = new SimpleObjectMapperTypeExtension();

        return $ext->getExtendedType() === $name ? [$ext] : [];
    }
}
