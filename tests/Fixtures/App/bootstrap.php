<?php

declare(strict_types=1);

namespace ConfigurationConverter\Test\Fixtures\App;

use Doctrine\Common\Annotations\AnnotationRegistry;

date_default_timezone_set('UTC');

$loader = require __DIR__.'/../../../vendor/autoload.php';
require __DIR__.'/AppKernel.php';

AnnotationRegistry::registerLoader('class_exists');

return $loader;
