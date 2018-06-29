<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Service;

use TFF\MTeamServicesSDK\Utils\HttpClient as HttpClient;

/**
 * Class BaseService
 * @package Services
 */
class BaseService
{
    protected $errorMsg = [];

    protected $httpClient = null;

    protected $className = null;

    protected $host = null;

    protected $globalConfig = [];

    protected $config = [];

    protected $zipkin;

    const SUCCESS_CODE = 0;

    public function __construct()
    {
        $classes = explode('\\', get_called_class());

        $this->className = end($classes);

        $this->config = $this->loadConfig();

        //由于Service本身就是单例,所以每个实例自己创建一个httpClient,不需共享,以免干扰

        $this->httpClient = new HttpClient();
    }

    private function loadConfig()
    {
        if (function_exists('env') && env('SVCSSDK_ENV')) {
            $env = env('SVCSSDK_ENV');
        } else {
            $env = isset($_SERVER['SVCSSDK_ENV']) ? $_SERVER['SVCSSDK_ENV'] : 'prod';
        }
        $path = dirname(__FILE__) . '/../config';
        if (file_exists($path.'/local.php')) {
            $env = 'local';
        }
        if (is_dir($path)) {
            $config = require($path . '/' . strtolower($env) . '.php');

            $this->globalConfig = $config;

            return isset($config[strtolower($this->className)]) ? $config[strtolower($this->className)] : [];
        }

        return [];
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param $path
     * @return string
     * @throws \Exception
     */
    protected function makeUrlByClassName($path = '')
    {
        if (!$this->host) {
            throw new \Exception(': Please set up property host in ' . static::class  . ' __construct' . PHP_EOL);
        }

        return $this->host . $path;
    }

    /**
     * @param $uri
     * @param array $params
     * @return mixed
     */
    protected static function parseRestfulURI($uri, array $params)
    {
        foreach ($params as $key => $value) {
            $uri = str_replace('{' . $key . '}', $value, $uri);
        }
        return $uri;
    }

    /**
     * @param $option
     */
    public function httpBuilder($option)
    {
        $method = isset($option['method']) ? $option['method'] : 'get';

        $this->httpClient->url($this->makeUrlByClassName($option['path']));
        $this->httpClient->addHeaders($option['headers']);
        
        //isset($option['query']) and $this->httpClient->queryString($option['query']);
        //isset($option['body']) and $this->httpClient->body($option['body']);

        $query = isset($option['query']) ? $option['query'] : [];
        $body = isset($option['body']) ? $option['body'] :[];

        $this->httpClient->queryString($query);

        $this->httpClient->body($body);

        $ret = $this->httpClient->$method();

        return $ret;
    }

    protected function setErrorMessage($err)
    {
        $this->errorMsg = $err;
    }

    public function getErrorMessage()
    {
        $msg = $this->errorMsg;

        $this->errorMsg = [];

        return $msg;
    }

    public function ping()
    {
        $response = $this->httpBuilder(
            [
                'path'=>'/',
                'method'=>'get'
            ]
        );
        echo ($this->httpClient->lastCode === 200) ? 'ok' : 'error';
        echo PHP_EOL;
    }

    protected function parseReturnRepData($response)
    {
        if (isset($response['code']) && $response['code'] === static::SUCCESS_CODE) {
            return $response['data'];
        } else {
            $response['message'] = isset($response['message']) ? $response['message'] : [];
            $this->setErrorMessage($response['message']);
            return false;
        }
    }

    protected function parseReturnStatusData($response)
    {
        if (isset($response['status']) && $response['status'] == '200') {
            return $response['data'];
        } else {
            $this->setErrorMessage(json_encode($response));
            return false;
        }
    }

}
