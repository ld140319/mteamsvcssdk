<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

class HttpCallUntil extends BaseUntil
{
    const GET = 'get';
    const POST = 'post';
    
    public static function execHttpCall($api, array $params, $method, $timeout = 2000, $options = array(), &$header = null)
    {
        if ($method == self::POST) {
            $jsonRet = HttpUntil::postMs($api, $params, $timeout, $options);
        } elseif ($method == self::GET) {
            $jsonRet = HttpUntil::getMs($api, $timeout, null, $options, $header);
        } else {
            throw new \Exception('unsupported http request method:' . $method);
        }
        if ($jsonRet === false) {
            throw new \Exception('network failure, curl_exec returned false'."errorMsg: ".HttpUntil::getLastErrorMsg());
        }
    
        $ret = json_decode($jsonRet, true);
        if (null === $ret) {
            throw new \Exception('json decode failed caused by ret is empty');
        }

        if (!array_key_exists('status', $ret)) {
            throw new \Exception('unexpected json structure, missing "status":' . json_encode($jsonRet));
        }

        if (!array_key_exists('message', $ret)) {
            throw new \Exception('unexpected json structure, missing "message":' . json_encode($jsonRet));
        }
    
        if (!array_key_exists('data', $ret)) {
            throw new \Exception('unexpected json structure, missing "data":' . json_encode($jsonRet));
        }
    
        if ($ret['status'] != 200) {
            throw new \Exception($ret['message'] . ' with status ' . $ret['status'], $ret['status']);
        }
    
        return $ret['data'];
    }
}
