<?php

/*
 * This file is part of the "elao/form-simple-object-mapper" package.
 *
 * Copyright (C) Elao
 *
 * @author Elao <contact@elao.com>
 */

use Symfony\Component\Filesystem\Filesystem;

date_default_timezone_set('UTC');

$loader = require __DIR__ . '/../../../../../vendor/autoload.php';

require __DIR__ . '/AppKernel.php';

// Empty generated symfony cache
(new Filesystem())->remove(__DIR__ . '/cache');

return $loader;
