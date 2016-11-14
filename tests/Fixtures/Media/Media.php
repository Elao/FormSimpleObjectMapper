<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) 2016 Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\FormSimpleObjectMapper\Tests\Fixtures\Media;

abstract class Media
{
    private $author;

    public function __construct($author)
    {
        $this->author = $author;
    }

    public function getAuthor()
    {
        return $this->author;
    }
}
