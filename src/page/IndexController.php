<?php

namespace app\page;

use Exception;
use app\base\PageController;
use app\components\CurlRequest;

class IndexController extends PageController
{
    private function fetchRequestData()
    {
        $input = [
            'uri' => [FILTER_VALIDATE_URL, null],
            'params' => [FILTER_DEFAULT, '[]'],
            'method' => [FILTER_SANITIZE_STRING, 'get'],
        ];
        
        $data = [];
        
        foreach($input as $varName => $conf) {
            list($filter, $default) = $conf;
            $data[$varName] = filter_input(INPUT_POST, $varName, $filter);
            if (null === $data[$varName]) {
                if (null === $default) {
                    throw new Exception('Missing required parameter {' . $varName .'}');
                } else {
                    $data[$varName] = $default;
                }
            } elseif (false === $data[$varName]) {
                throw new Exception('Invalid data passed in parameter {' . $varName .'}');
            }
        }
        
        return $data;
    }
    
    public function mainAction()
    {
        return $this->view('main');
    }
    
    private function getRequestData($uri, $method = 'get', $params = [])
    {
        $curlRequest = new CurlRequest($uri);
        $curlRequest->setMethod($method);
        if ($userAgent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING)) {
            $curlRequest->setUserAgent($userAgent);
        }
        foreach($params as $name => $value) {
            $curlRequest->addParam($name, $value);
        }
        $headers = [];
        
        list($status, $body) = $curlRequest->exec($headers);
        
        return [
            'code' => $status,
            'headers' => $headers,
            'body' => $body,
        ];
    }
    
    public function queryAction()
    {
        $response = [
            'status' => 'OK'
        ];
        try {
            $data = $this->fetchRequestData();
            $requestParams = json_decode($data['params'], true);
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new Exception('Invalid parameters format.');
            } elseif (!is_array($requestParams)) {
                $requestParams = [];
            }
            $response += $this->getRequestData($data['uri'], $data['method'], $requestParams);
        } catch (Exception $e) {
            $response['status'] = $e->getMessage();
        }
        $this->app->sendDataAsJson($response);
    }
}