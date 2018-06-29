<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace TFF\MTeamServicesSDK\Utils;

use TFF\MTeamServicesSDK\Utils\PerformanceTesting;


class HttpClient
{
    private static $instance = null;

    public $lastCode = null;

    public $httpContainer = [];

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new HttpClient();
        }
        return self::$instance;
    }

    /**
     * @param $data array headers数组
     * @return $this
     */
    public function headers($data)
    {
        if (is_array($data)) {
            $headers = [];
            foreach ($data as $key => $value) {
                $headers[$key] = $key . ':' . $value;
            }
            $this->httpContainer['headers'] = $headers;
        }

        return $this;
    }

    public function addHeaders($data)
    {
        if (!isset($this->httpContainer['headers'])) {
            $this->httpContainer['headers'] = [];
        }
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->httpContainer['headers'][$key] = $key . ':' . $value;
            }
        }
        return $this;
    }

    public function queryString($data)
    {
        $this->httpContainer['queryString'] = http_build_query($data);
        return $this;
    }

    /**
     * @param $data mixed request body
     * @return $this
     */
    public function body($data)
    {
        if (in_array('content-type:application/json', isset($this->httpContainer['headers']) ?
            $this->httpContainer['headers'] : [])) {
            $data = json_encode($data);
        }
        $this->httpContainer['body'] = $data;
        return $this;
    }

    public function url($url)
    {
        $this->httpContainer['url'] = $url;
        return $this;
    }

    /**
     * @param $url
     * @return mixed
     */
    public function get()
    {
        $this->httpContainer['method'] = 'GET';
        return $this->run();
    }

    /**
     * @param $url
     * @return mixed
     */
    public function post()
    {
        $this->httpContainer['method'] = 'POST';
        return $this->run();
    }

    /**
     * @param $url
     * @return mixed
     */
    public function put()
    {
        $this->httpContainer['method'] = 'PUT';
        return $this->run();
    }

    public function patch()
    {
        $this->httpContainer['method'] = 'PATCH';
        return $this->run();
    }
    public function initialCurl(){
        $this->httpContainer['body'] = null;
    }

    /**
     * @param $url
     * @return mixed
     */
    public function delete()
    {
        $this->httpContainer['method'] = 'DELETE';
        return $this->run();
    }

    private function run()
    {
        if (class_exists('TFF\MTeamServicesSDK\Utils\PerformanceTesting') && PerformanceTesting::isNeedXhprof()) {
            $time_start = PerformanceTesting::microtimeFloat();
            is_array($this->httpContainer['queryString']) ? $this->httpContainer['queryString'][ 'xhprof' ] = 1 : '';
            $suffix = (strpos($this->httpContainer['queryString'], '?') !== false) ? '&' : '?';
            $this->httpContainer['url'] .= $suffix . 'xhprof=1&rs=' . (int)$_REQUEST[ 'rs' ];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        isset($this->httpContainer['headers']) && curl_setopt($ch, CURLOPT_HTTPHEADER, $this->httpContainer['headers']);
        isset($this->httpContainer['body']) && curl_setopt($ch, CURLOPT_POSTFIELDS, $this->httpContainer['body']);

        if (isset($this->httpContainer['url'])) {
            $url = (false !== strpos($this->httpContainer['url'], '?')) ?
                $this->httpContainer['url'] . '&' . (isset($this->httpContainer['queryString']) ?
                    $this->httpContainer['queryString'] : '')
                    : $this->httpContainer['url'] . '?' . (isset($this->httpContainer['queryString']) ?
                        $this->httpContainer['queryString'] : '');
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->httpContainer['method']);
            $res = curl_exec($ch);
            $this->lastCode = curl_getinfo($ch)['http_code'];
            curl_close($ch);
            $obj = json_decode($res, true);
            isset($time_start) ? PerformanceTesting::getCurlTime($time_start, $url, $this->httpContainer['queryString'], $this->httpContainer['method'], $res) : '';
            return $obj ? $obj : $res;
        } else {
            curl_close($ch);
            return false;
        }
    }
}
/*
USAGE
$ret = HttpClient::getInstance()
    ->headers(['content-type'=>'application/json'])
    ->body(['a'=>'b'])
    ->queryString(['_t'=>123])
    ->url('http://127.0.0.1:3001')
    ->post();
*/
