<?php

namespace app\view;

use Exception;

interface RendererInterface
{
    public function getViewFileName($viewName);
    
    public function handleRenderException(Exception $e);
    
    public function renderView($viewName, $params = []);
}