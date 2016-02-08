<?php

namespace app\view;

use Exception;
use app\base\Application;

class View
{
    static private function renderFile()
    {
        extract(func_get_arg(1));
        include func_get_arg(0);
    }
    
    /**
     * @var RendererInterface
     */
    private $context;
    
    private $fileName;
    
    private $params;
    
    public function __construct($name, RendererInterface $context, $params = [])
    {
        if (!file_exists($fileName = $context->getViewFileName($name))) {
            throw new Exception('View file does not exist.', Application::HTTP_SERVER_ERROR);
        }
        $this->context = $context;
        $this->fileName = $fileName;
        $this->params = $params;
    }
    
    public function __get($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }
    
    public function __set($name, $value)
    {
        $this->params[$name] = $value;
    }
    
    public function __toString()
    {
        $rendered = '';
        try {
            ob_start();
            $this->render();
            $rendered = ob_get_clean();
        } catch (Exception $e) {
            $this->context->handleRenderException($e);
        }
        return $rendered;
    }
    
    public function render()
    {
        $params = $this->params;
        $params['context'] = $this->context;
        self::renderFile($this->fileName, $params);
    }
}