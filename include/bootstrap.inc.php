<?php

define('APP_DIR_SRC', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR);

include_once APP_DIR_SRC . 'base' . DIRECTORY_SEPARATOR . 'Application.php';
include_once APP_DIR_SRC . 'base' . DIRECTORY_SEPARATOR . 'AutoLoad.php';

use app\base\AutoLoad;

new AutoLoad(APP_DIR_SRC);