<?php

$includeDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR;

include_once $includeDirectory . 'bootstrap.inc.php';

use app\base\Application;

(new Application(include $includeDirectory . 'config.inc.php'))->run();