<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

class IPUntil
{
    /**
     * 获取客户端IP
     * 
     * @return string 客户端IP地址，未成功获取返回空字符串
     */
    public static function IP()
    {
        $ip = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            $ip = explode(',', $ip);
            $ip = trim($ip[0]);
        } elseif (isset($_SERVER['HTTP_X_FORWARDED']) && $_SERVER['HTTP_X_FORWARDED']) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR']) && $_SERVER['HTTP_FORWARDED_FOR']) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED']) && $_SERVER['HTTP_FORWARDED']) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = '';
        }
        return $ip;
    }

    /**
     * 计算子网掩码
     * 
     * @param  int    $maskNumber 子网掩码号，1-32
     * 
     * @return string $mask       子网掩码
     */
    public static function ipStrMask($maskNumber)
    {
        $mask = self::calMask($maskNumber);
        if ($mask === false) {
            return false;
        }
        return long2ip($mask);
    }

    /**
     * 根据IP地址和子网掩码号计算子网IP
     * 
     * @param  string $ip          IP地址
     * @param  int    $maskNumber  子网掩码号，1-32
     * 
     * @return string $subIP       子网IP地址
     */
    public static function subIP($ip, $maskNumber)
    {
        $mask = self::calMask($maskNumber);
        if ($mask===false) {
            return false;
        }
        $ip = ip2long($ip);
        if ($ip===false) {
            return false;
        }

        $subIP = $ip & $mask;

        return long2ip($subIP);
    }

    /**
     * 根据IP地址和子网掩码号计算广播地址
     * 
     * @param  string $ip         IP地址
     * @param  int    $maskNumber 子网掩码号，1-32
     * 
     * @return string $broadcast  广播地址
     */
    public static function broadcast($ip, $maskNumber)
    {
        $mask = self::calMask($maskNumber);
        if ($mask===false) {
            return false;
        }
        $ip = ip2long($ip);
        if ($ip===false) {
            return false;
        }

        $mask = ~$mask;

        $broadcast = $ip | $mask;

        return long2ip($broadcast);
    }

    /**
     * 根据IP地址和子网掩码号计算该网段的起始结束IP
     * 
     * @param  string $ip         IP地址
     * @param  int    $maskNumber 子网掩码号，1-32
     * 
     * @return array  $segIPs     IP地址起始
     */
    public static function segIPs($ip, $maskNumber)
    {
        $broadcast = self::broadcast($ip, $maskNumber);
        if ($broadcast===false) {
            return array();
        }

        $subIP = self::subIP($ip, $maskNumber);
        if ($subIP===false) {
            return array();
        }

        if (ip2long($subIP)==ip2long($broadcast)) {
            return array($subIP, $broadcast);
        }
        $ipStart = long2ip(ip2long($subIP) + 1);
        $ipEnd   = long2ip(ip2long($broadcast) - 1);

        return array($ipStart, $ipEnd);
    }

    /**
     * 根据IPV6地址和子网掩码号计算网段
     * 
     * @param  string $ipv6       IPV6地址
     * @param  int    $maskNumber 子网掩码号，1-32
     * 
     * @return string $seg        网段
     */
    public static function IPV6SegIP($ipv6, $maskNumber)
    {
        $mask = self::structIPV6Mask(48);
        if ($mask == '') {
            return '';
        }
        $mask = inet_pton($mask);
        $ipv6 = inet_pton($ipv6);

        $seg  = $ipv6 & $mask;
        $seg  = inet_ntop($seg);
        return $seg;
    }

    /**
     * 判断客户端IP是否属于指定IP集合
     * 
     * @param  array $ips     指定IP集合
     * 
     * @return bool  $isMatch 该IP是否属于该IP集合
     * @description  $ips是一个包含不同ip字符串的数组
     * ipv4字符串支持以下三种形式
     * 1. 192.168.1.100/24
     * 2. 192.168.1.100-192.168.1.110
     * 3. 192.169.1.100
     */
    public static function isMatchIPs($ips)
    {
        $clientIp      = self::IP();
        $isClientIPV6  = false;
        if (preg_match('/:/', $clientIp)) {
            $isClientIPV6 = true;
        }

        $isMatch = false;
        foreach ($ips as $targetIP) {
            $targetIP = trim($targetIP);
            $targetIP = explode('/', $targetIP);
            foreach ($targetIP as $key => $val) {
                $targetIP[$key] = trim($val);
            }
            if (count($targetIP)==2) {      //ip likes 192.168.1.100/24
                $maskNumber = $targetIP[1];
                $targetIP   = $targetIP[0];

                $isTargetIPV6 = false;
                if (preg_match('/:/', $targetIP)) {
                    $isTargetIPV6 = true;
                }

                if ($isClientIPV6 && $isTargetIPV6) {            //IPV6
                    $segIP = self::IPV6SegIP($clientIp, $maskNumber);
                    if ($segIP==$targetIP) {
                        $isMatch = true;
                        break;
                    }
                } elseif (!$isClientIPV6 && !$isTargetIPV6) {   //IPV4
                    $segIPs = self::segIPs($targetIP, $maskNumber);
                    if ($segIPs && $clientIp) {
                        $startIP = ip2long($segIPs[0]);
                        $endIP   = ip2long($segIPs[1]);
                        $cIP     = ip2long($clientIp);
                        if ($startIP <= $cIP && $endIP >= $cIP) {
                            $isMatch = true;
                            break;
                        }
                    }
                } else {
                    continue;
                }
            } else {
                $targetIP = $targetIP[0];
                $targetIP = explode('-', $targetIP);
                foreach ($targetIP as $key => $val) {
                    $targetIP[$key] = trim($val);
                }
                if (count($targetIP)==2) {  //ip likes 192.168.1.100-192.168.1.110
                    $startIP = $targetIP[0];
                    $endIP   = $targetIP[1];

                    $isTargetIPV6 = false;
                    if (preg_match('/:/', $startIP)) {
                        $isTargetIPV6 = true;
                    }
                    if ($isTargetIPV6) {
                        $isMatch = false;
                        continue;
                    } else {
                        $clientIpLong = ip2long($clientIp);
                        $startIPLong  = ip2long($startIP);
                        $endIPLong    = ip2long($endIP);

                        if ($clientIpLong>=$startIPLong && $clientIpLong<=$endIPLong || $clientIpLong<=$startIPLong && $clientIpLong>=$endIPLong) {
                            $isMatch = true;
                            break;
                        } else {
                            $isMatch = false;
                            continue;
                        }
                    }
                } else {    //ip likes 192.168.1.100
                    $targetIP = $targetIP[0];
                    if ($targetIP==$clientIp) {
                        $isMatch = true;
                        break;
                    } else {
                        $isMatch = false;
                        continue;
                    }
                }
            }
        }

        return $isMatch;
    }

    private static function calMask($maskNumber)
    {
        $maskNumber = intval($maskNumber);
        if ($maskNumber > 32 || $maskNumber < 1) {
            return false;
        }

        $full = 0xffffffff;
        $sub  = 32 - $maskNumber;
        $mask = $full << $sub;

        return $mask;
    }

    private static function ipv6mask($len)
    {
        if ($len < 1 || $len > 128) {
            return '';
        }

        $mask = '';
        if ($len >= 4) {
            $len -= 4;
            $mask .= 'f';
            $mask .= self::ipv6mask($len);
        } else {
            switch ($len) {
                case 1:
                    $mask .= '8';
                    break;
                case 2:
                    $mask .= 'c';
                    break;
                case 3:
                    $mask .= 'e';
                    break;
            }
            if (strlen($mask)%4==0) {
                $mask .= ':';
            }
        }

        return $mask;
    }

    private static function structIPV6Mask($len)
    {
        $mask = self::ipv6mask($len);
        if ($mask == '') {
            return '';
        }
        $cmask = '';
        $smask = array();
        $l = strlen($mask);
        for ($i=0; $i!=$l; ++$i) {
            $cmask .= $mask[$i];
            if (($i+1)%4==0) {
                $smask[] = $cmask;
                $cmask = '';
            }
        }
        $smask = implode(':', $smask);
        if ($len < 128) {
            $smask .= '::';
        }
        return $smask;
    }
}
