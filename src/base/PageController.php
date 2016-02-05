<?php

namespace app\base;

use Exception;
use app\view\Layout;
use app\view\RendererInterface;
use app\view\View;

abstract class PageController implements RendererInterface
{
    private $classViewPath;
    
    /**
     * @var Application
     */
    protected $app;
    
    protected $layoutId = 'default';
    
    /**
     * @var Layout
     */
    protected $layout;
    
    public function __construct(Application $app)
    {
        $this->app = $app;
        $classPath = explode('\\', get_called_class());
        // lower case only just for compatibility with *nix
        $this->classViewPath = strtolower(str_replace('Controller', '', array_pop($classPath)));
    }
    
    public function getViewFileName($viewName)
    {
        return $this->app->getOption('viewsPath') . $viewName . '.inc.php';
    }
    
    public function handleRenderException(Exception $e)
    {
        $this->app->handleException($e);
    }
    
    public function invokeAction($actionId)
    {
        $actionMethodName = str_replace('-', '', $actionId) . 'action';
        if (!method_exists($this, $actionMethodName)) {
            throw new Exception('Required action is missing.', Application::HTTP_NOT_FOUND);
        } else {
            return $this->{$actionMethodName}();
        }
    }
    
    /**
     * @return Layout
     */
    public function layout()
    {
        if (null === $this->layout) {
            $this->layout = new Layout('layouts' . DIRECTORY_SEPARATOR . $this->layoutId, $this);
        }
        return $this->layout;
    }
    
    public function renderView($viewName, $params = [])
    {
        echo $this->view($viewName, $params);
    }
    
    public function view($viewName, $params = [])
    {
        if (false === strpos($viewName, DIRECTORY_SEPARATOR)) {
            $viewName = $this->classViewPath . DIRECTORY_SEPARATOR . $viewName;
        }
        return new View($viewName, $this, $params);
    }
}