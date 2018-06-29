<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

class RequestUntil extends BaseUntil
{
    public static function getClientIp()
    {
        $ip = null;
        $client  = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : 0;
        $forward = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : 0;
        $remote  = $_SERVER['REMOTE_ADDR'];

        if (!empty($forward)) {
            $forwardArr = explode(',', $forward);
            $firstForward = current($forwardArr);
            if (filter_var($firstForward, FILTER_VALIDATE_IP)) {
                $ip = $firstForward;
            }
        } elseif (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } else {
            $ip = $remote;
        }

        return $ip;
    }
}