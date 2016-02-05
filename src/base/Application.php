<?php

namespace app\base;

use Exception;

class Application
{
    const HTTP_OK = 200;
    
    const HTTP_NOT_FOUND = 404;
    
    const HTTP_SERVER_ERROR = 500;
    
    private $actionId;
    
    private $config;
    
    private $controllerId;
    
    private $httpMap;
    
    private $isOnAir = false;
    
    private $statusCode = self::HTTP_OK;
    
    private function dispatch()
    {
        // 1. get route data from string *** throws exceptions
        list($this->controllerId, $this->actionId) = $this->getRouteArray();
        // 2. process route on controller / action *** throws exceptions
        // this is very simple names resolution!
        $controllerClassName = 'app\page\\' . ucfirst($this->controllerId) . 'Controller';
        if (!class_exists($controllerClassName)) {
            throw new Exception('Required controller is missing.', self::HTTP_NOT_FOUND);
        }
        // 3. get controller instance
        $pageController = new $controllerClassName($this);
        /* @var $pageController PageController */
        // 4. invoke action  *** throws exceptions
        $content = $pageController->invokeAction($this->actionId);
        // 5. configure layout  *** throws exceptions
        $layout = $pageController->layout();
        $layout->content = $content;
        // 6. render layout
        echo $layout;
    }
    
    private function finalRoutines($body)
    {
        $statusString = isset($this->httpMap[$this->statusCode]) ?
            $this->httpMap[$this->statusCode] :
            $this->httpMap[self::HTTP_SERVER_ERROR];
        header('HTTP/1.1 ' . $statusString);
        echo $body;
    }
    
    private function getRouteArray()
    {
        $routeParamName = isset($this->config['routeParam']) ? $this->config['routeParam'] : 'q';
        if (null === ($routeString = filter_input(INPUT_GET, $routeParamName, FILTER_SANITIZE_STRING))) {
            if (isset($this->config['defaultRoute'])) {
                $routeString = $this->config['defaultRoute'];
            } else {
                throw new Exception('No route defined by default.');
            }
        } elseif (false === $routeString) {
            throw new Exception('Bad input.');
        }
        $routePath = explode('/', $routeString);
        if (2 !== count($routePath)) {
            throw new Exception('Bad route string.');
        }
        return $routePath;
    }
    
    private function onBeforeDispatch()
    {
        if ($this->isOnAir) {
            throw new Exception('Duplicate app request dispatching.', self::HTTP_SERVER_ERROR);
        }
        $this->isOnAir = true;
        if (!$this->viewsPathValidate()) {
            throw new Exception(
                'Invalid configuration view files directory missing or not a directory.',
                self::HTTP_SERVER_ERROR
            );
        }
    }
    
    private function viewsPathValidate()
    {
        $isValid = false;
        if (isset($this->config['viewsPath']) && !empty($this->config['viewsPath'])) {
            $fixedPath = rtrim($this->config['viewsPath'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            if (file_exists($fixedPath) && is_dir($fixedPath)) {
                $this->config['viewsPath'] = $fixedPath;
                $isValid = true;
            }
        }
        return $isValid;
    }
    
    public function __construct($config = [])
    {
        $this->config = $config;
        
        $this->httpMap = include dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'include' .
                DIRECTORY_SEPARATOR . 'http.inc.php';
    }
    
    public function getOption($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : null;
    }
    
    public function handleException(Exception $e)
    {
        error_log('Exception caught: "' . $e->getMessage() . '" thrown in ' . $e->getFile() . ' on line ' .
                $e->getLine());
        
        $this->statusCode = $e->getCode();
    }
    
    public function run()
    {
        ob_start();
        try {
            $this->onBeforeDispatch();
            $this->dispatch();
        } catch (Exception $e) {
            $this->handleException($e);
            $this->statusCode = $e->getCode();
        }
        $this->finalRoutines(ob_get_clean());
    }
    
    public function sendDataAsJson($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        die();
    }
}