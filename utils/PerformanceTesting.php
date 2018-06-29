<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */
/**
 * for xhprof debug
 */
namespace TFF\MTeamServicesSDK\Utils;


class PerformanceTesting
{
    /**
     * @return float
     */
    public static function microtimeFloat()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * @return bool
     */
    public static function isNeedXhprof()
    {
        return  isset($_REQUEST['xhprof']) && ($_REQUEST['xhprof'] ==1) && extension_loaded('xhprof');
    }

    /**
     * get curl time and write into xhprof dir .
     * @param $time_start
     * @param string $request_url
     * @param array $request_params
     * @param string $request_method
     * @param string $respone 
     */
    public static function getCurlTime($time_start, $request_url = '', $request_params = [], $request_method = 'GET', $respone = '')
    {
        if (self::isNeedXhprof()) {
            $time_end = self::microtimeFloat();
            $curl_time = $time_end - $time_start;
            $xhprof_dir = empty(ini_get("xhprof.output_dir")) ? sys_get_temp_dir() : ini_get("xhprof.output_dir");
            $xhprof_dir .= "/";
            $pathinfo = pathinfo($_SERVER['REQUEST_URI']);
            $xhprof_path_name = !empty($pathinfo['filename']) ? str_replace(['?','&','=','[',']','%','.'], '-', $pathinfo['filename']) : date("Y-m-d-H-i-s");
            $xhprof_host = empty($_SERVER['HTTP_HOST']) ? '' : $_SERVER['HTTP_HOST'];
            $xhprof_curl_name = $xhprof_dir  . "curl-" . $xhprof_host . '-' . $xhprof_path_name;
            if(strlen($xhprof_curl_name)>250) {
                $xhprof_curl_name = substr($xhprof_curl_name, 0, 150);
            }
            if (is_writable($xhprof_dir)) {
                file_put_contents($xhprof_curl_name, "url:" . print_r($request_url, true) . ' ' . $curl_time .PHP_EOL, FILE_APPEND);
                file_put_contents($xhprof_curl_name, "method:" . print_r($request_method, true) . ' ' . $curl_time .PHP_EOL, FILE_APPEND);
                file_put_contents($xhprof_curl_name, "time:" . print_r($curl_time, true). ' '  . $curl_time .PHP_EOL, FILE_APPEND);
                file_put_contents($xhprof_curl_name, "params:" . print_r($request_params, true) . ' ' . PHP_EOL, FILE_APPEND);
                if (isset($_REQUEST['rs']) && $_REQUEST['rs']==1) {
                    file_put_contents($xhprof_curl_name, "respone:" . print_r($respone, true) . ' ' . PHP_EOL, FILE_APPEND);
                }
            }
        }
    }
}
