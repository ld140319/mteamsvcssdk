<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

use phpseclib\Crypt\RSA;

class CookieUntil
{
    private $publickey = null;

    private $privatekey = null;

    private $rsa = null;

    private $env = 'prod'; //default

    private $domain = null;

    private $expire = 0;

    public function __construct($domain = '', $expire = 2592000)
    {
        error_reporting(~E_USER_NOTICE);
        $this->env = (function_exists('env') && env('SVCSSDK_ENV')) ? env('SVCSSDK_ENV') :
            (isset($_SERVER['SVCSSDK_ENV']) ? $_SERVER['SVCSSDK_ENV'] : 'prod');
        $keyPath = function_exists('env') ? env('RSA_KEY_PATH') : dirname(__FILE__);

        $this->publickey = $this->loadKey('pub');
        $this->privatekey = $this->loadKey('pri');

        $this->rsa = new RSA();
        $this->domain = $domain;
        $this->expire = $expire;
    }

    private function loadKey($key = 'pub')
    {
        $keyPath = function_exists('env') ? env('RSA_KEY_PATH') : dirname(__FILE__);
        //keyPath
        if (file_exists($keyPath . '/' . $this->env . '/rsa.' . $key)) {
            return file_get_contents($keyPath . '/' . $this->env . '/rsa.' . $key);
        }
        //default config path
        if (file_exists(dirname(__FILE__) . '/../config/keys/' . $this->env . '/rsa.' . $key)) {
            return file_get_contents(dirname(__FILE__) . '/../config/keys/' . $this->env . '/rsa.' . $key);
        }

        return null;
    }

    public function set($key, $data)
    {
        if (!$this->privatekey) {
            return false;
        }
        $cookie = $this->encode($data);
        if (is_null($cookie)) {
            return false;
        }

        return setcookie($key, $cookie, time() + $this->expire, '/', $this->domain);
    }

    public function get($key)
    {
        if (!$this->publickey) {
            return false;
        }
        if (!isset($_COOKIE[$key])) {
            return false;
        }
        try {
            $cookie = $this->decode($_COOKIE[$key]);
        }catch (\Exception $e){
            $cookie = false;
        }
        if (!$cookie) {
            $this->clear($key);
        }

        return $cookie;
    }

    private function encode($data)
    {
        if (!$this->privatekey) {
            return null;
        }

        $this->rsa->loadKey($this->privatekey);

        return base64_encode($this->rsa->encrypt(json_encode($data)));
    }

    private function decode($data)
    {
        if (!$this->publickey) {
            return null;
        }

        $this->rsa->loadKey($this->publickey);

        return json_decode($this->rsa->decrypt(base64_decode($data)), true);
    }

    public function clear($key)
    {
        setcookie($key, null, 0, '/', $this->domain);
        setcookie('PHPSESSID', null, 0, '/', $this->domain);
    }
}
