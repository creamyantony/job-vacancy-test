<?php

namespace app\components;

use Exception;

/**
 * Class CurlRequest
 * @package app\components
 *
 * todo: cookies handling
 */
class CurlRequest
{
    const MAX_REDIRECTS = 5;
    
    const METHOD_GET = 0;
    
    const METHOD_POST = 1;
    
    /**
     * Helper to resolve method codes by string.
     * 
     * @var array
     */
    static private $methodMap = [
        'get' => self::METHOD_GET,
        'post' => self::METHOD_POST,
    ];
    
    private $ch;
    
    private $method = self::METHOD_GET;
    
    private $params = [];
    
    /**
     * This method serves multiple tasks:
     * 1. Changes URL of current session for the next request (if required to do so);
     * 2. Validates next request URL;
     * 3. Returns next request URL.
     * 
     * @param string $url
     * @return string
     * @throws Exception
     */
    private function handleCurrentURL($url)
    {
        if (null !== $url) {
            curl_setopt($this->ch, CURLOPT_URL, $url);
        }
        $currentURL = curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);
        if (!filter_var($currentURL, FILTER_VALIDATE_URL)) {
            throw new Exception('Try to make request on invalid URI.');
        }
        return $currentURL;
    }
    
    /**
     * Method designed for setting parameters for the next request if it's
     * an initial query and clears parameters if it's not. It also sets
     * required HTTP method.
     * 
     * @param string $url Next request URL
     * @param bool $initial Is the next request initial or NOT (ex. some redirects was detected)
     */
    private function handleParameters($url, $initial = false)
    {
        if ($initial) {
            if (self::METHOD_POST === $this->method) {
                curl_setopt_array($this->ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $this->params,
                ]);
            } else {
                $url = rtrim($url, '?');
                $concatVia = (false === strpos($url, '?', 0)) ? '?' : '&';
                curl_setopt($this->ch, CURLOPT_URL, $url . $concatVia . http_build_query($this->params));
            }
        } else {
            curl_setopt($this->ch, CURLOPT_POST, false);
        }
    }
    
    /**
     * Parses headers from HTTP formatted string into an array. It also
     * detects if any value of Location header was sent and sets its
     * value into $location parameter. Initial HTTP response line will be
     * passed to the result array by key "HTTP/{VERSION}".
     * 
     * @param string $string
     * @param string& $location
     * @return array
     */
    private function parseHeaders($string, &$location)
    {
        $headers = [];
        foreach(explode(PHP_EOL, $string) as $line) {
            if (false !== ($offset = strpos($line, ':', 0))) {
                $name = trim(substr($line, 0, $offset));
                $headers[$name] = trim(substr($line, $offset + 1));
                if ('location' === strtolower($name)) {
                    $location = $headers[$name];
                }
            } elseif (!strncmp('HTTP', $line, 4)) {
                $offset = strpos($line, ' ', 0);
                $headers[trim(substr($line, 0, $offset))] = trim(substr($line, $offset + 1));
            }
        }
        return $headers;
    }
    
    public function __construct($url = null)
    {
        if (false === $this->ch = curl_init($url)) {
            throw new Exception('Could not instantiate cURL session.');
        }
        curl_setopt_array($this->ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => false,
        ]);
    }
    
    /**
     * Attaches some parameters to the next request.
     * 
     * @param string $name
     * @param string $value
     */
    public function addParam($name, $value)
    {
        $this->params[$name] = $value;
    }
    
    /**
     * Method handles external or internal (recursive) calls to get and fetch
     * HTTP requests.
     * 
     * @param array& $headers Array of array of request headers
     * @param string|null $url
     * @param int $count Order number of current request (counting from 0)
     * @return array
     * @throws Exception
     */
    public function exec(array& $headers, $url = null, $count = 0)
    {
        // process passed params:
        $this->handleParameters($this->handleCurrentURL($url), $count < 1);
        // executing session:
        if (!$response = curl_exec($this->ch)) {
            throw new Exception('Could not execute cURL session.');
        }
        $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $location = null;
        $headers[$count++] = $this->parseHeaders(substr($response, 0, $headerSize), $location);
        if ((301 == $status) || (302 == $status)) {
            if ((null !== $location) && $count <= self::MAX_REDIRECTS) {
                return $this->exec($headers, $location, $count);
            }
        }
        return [$status, substr($response, $headerSize)];
    }
   
    /**
     * @param int $method
     * @throws Exception
     */
    public function setMethod($method = self::METHOD_GET)
    {
        $throw = false;
        if (!is_numeric($method)) {
            if (!isset(self::$methodMap[$method])) {
                $throw = true;
            } else {
                $this->method = self::$methodMap[$method];
            }
        } elseif (!in_array($method, self::$methodMap)) {
            $throw = true;
        } else {
            $this->method = $method;
        }
        if ($throw) {
            throw new Exception('Unknown request method passed.');
        }
    }
    
    /**
     * @param string $userAgentString
     */
    public function setUserAgent($userAgentString)
    {
        curl_setopt($this->ch, CURLOPT_USERAGENT, $userAgentString);
    }
}