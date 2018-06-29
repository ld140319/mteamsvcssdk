<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

use CMS\MTeamServicesSDK\Exception\ParameterIntegrityException;

/**
 * 封装公用的一些或者方法
 * Class CommonUntil
 * @package CMS\MTeamServicesSDK\Until
 */
class CommonUntil extends BaseUntil
{
    //域名
    const PRODUCT_DOMAIN = "product.com";

    //默认分页大小
    const DEFAULT_PAGESIZE = 2;

    //默认分页页码
    const DEFAULT_PAGE_NUMBER = 1;

    //加密的盐
    const CacheSecret = 'secret';

    /**
     * 格式化文件URL地址为字符串
     * @param $file_type
     * @param $url
     * @return array|mixed|string
     */
    public static  function parseUrlToFilePath($file_type, $url)
    {
        try {
            if (!empty($url)) {
                $url = json_decode($url, true);
            }
            if (is_array($url)) {
                $url = array_map(function ($val) use ($file_type){
                    $start = "/uploads/".$file_type;
                    return strstr($val, $start);
                }, $url);
                $url = implode(',', $url);
            }
            return $url;
        } catch (\Exception $e) {
            LogUntil::error("CommonUntil parseUrlToFilePath --- exception:".$e->getMessage());
            return "";
        }
    }

    /**
     * 格式化文件地址为数组
     * @param $url
     * @return array
     */
    public static function parseFilePathToArray($url)
    {
        try {
            if (!empty($url)) {
                $url = explode(',', $url);
                $url = array_map(function ($val){
                    if (preg_match('/(http|https)/', $val)) {
                        return $val;
                    } else {
                        if (self::PRODUCT_DOMAIN) {
                            return 'https://'.self::PRODUCT_DOMAIN.$val;
                        }
                        return 'https://'.$_SERVER['HTTP_HOST'].$val;
                    }
                }, $url);
            } else {
                $url = array();
            }
            return $url;
        } catch (\Exception $e) {
            LogUntil::error("CommonUntil parseFilePathToArray --- exception:".$e->getMessage());
            return array();
        }
    }

    /**
     * 获取当前毫秒数
     * @return float
     */
    public  static function get_millisecond()
    {
        list($usec, $sec) = explode(" ", microtime());
        $msec = round($usec*1000);
        return $msec;
    }

    /**
     * 校验请求参数完整性
     * @param $requiredKey
     * @param $requestParams
     * @throws ParameterIntegrityException
     * @return bool
     */
    public static function checkParameterIntegrity($requiredKey, $requestParams)
    {
        foreach ($requiredKey as $column) {
            if (!isset($requestParams[$column])) {
                throw new ParameterIntegrityException("$column is required");
            }
        }
        return true;
    }

    /**
     * 格式化时间
     * @param $datetime
     * @return string
     */
    public static function formatDatetime($datetime, $is_end=false)
    {
        if (!preg_match('/\d{4}-\d{2}-\d{2}\s{1}\d{2}:\d{2}:\d{2}/', $datetime)) {
            if (!$is_end) {
                $datetime = $datetime." 00:00:00";
            } else {
                $datetime = $datetime." 23:59:59";
            }
        }
        return $datetime;
    }

    public static function getDomain($domain, $uri = '', $prefix = '', $query = [])
    {
        if ($prefix) {
            $domain = $prefix.'.'.$domain;
        }

        if (strpos($domain, 'http://') !== false || strpos($domain, 'https://') !== false) {
            $url = $domain;
        } else {
            $url = 'http://'.$domain;
        }

        if ($uri) {
            $url = $url.(substr($url, -1) == '/' ? '' : '/').$uri;
        }

        if ($query) {
            $url .= ('?'.http_build_query($query));
        }
        return $url;
    }

    public static function getEnv()
    {
        if (function_exists('env')) {
            $env = env('APP_ENV');
        } elseif (isset($_SERVER['SVCSSDK_ENV']) && !empty($_SERVER['SVCSSDK_ENV'])) {
            $env = $_SERVER['SVCSSDK_ENV'];
        } else {
            $env = 'prod';
        }
        return  $env;
    }

}
