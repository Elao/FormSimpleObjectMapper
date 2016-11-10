Symfony Form Simple Object Mapper
=================================

[![Build Status](https://img.shields.io/travis/Elao/FormSimpleObjectMapper/master.svg?style=flat-square)](https://travis-ci.org/Elao/FormSimpleObjectMapper)
[![Coveralls](https://img.shields.io/coveralls/Elao/FormSimpleObjectMapper.svg?style=flat-square)](https://coveralls.io/github/Elao/FormSimpleObjectMapper)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/Elao/FormSimpleObjectMapper.svg?style=flat-square)](https://scrutinizer-ci.com/g/Elao/FormSimpleObjectMapper/?branch=master)

This library aims to ease immutable or value objects mapping, until a decision on https://github.com/symfony/symfony/pull/19367 is made.

# Installation

Soon on packagist.org...

## With Symfony

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Elao\FormSimpleObjectMapper\Bridge\Symfony\Bundle\ElaoFormSimpleObjectMapperBundle(),
        );
    }

    // ...
}
```

# Usage

Coming soon...
