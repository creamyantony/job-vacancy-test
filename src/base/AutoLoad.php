<?php

namespace app\base;

class AutoLoad
{
    private $appDir;
    
    public function __construct($applicationDirectory)
    {
        if (!$applicationDirectory || !file_exists($applicationDirectory) || !is_dir($applicationDirectory)) {
            error_log('Invalid app source directory.');
            die();
        }
        $this->appDir = rtrim($applicationDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (false === spl_autoload_register([$this, 'tryToLoadClass'])) {
            error_log('Failed to register autoload handler.');
            die();
        }
    }
    
    public function tryToLoadClass($className)
    {
        $prefix = str_replace(
            [
                'app\\',
                '\\'
            ],
            [
                $this->appDir,
                DIRECTORY_SEPARATOR
            ],
            $className
        );
        if (file_exists($fileName = $prefix . '.php')) {
            include_once $fileName;
        }
    }
}