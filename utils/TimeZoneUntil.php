<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

use CMS\MTeamServicesSDK\Until\LogUntil;

class TimeZoneUntil extends BaseUntil
{

    /**
     * 格式化获取标准的时区
     *
     * @param string $timeZone 时区 eg: Asia/Shanghai (GMT+8) offset 28800
     * @return string eg: 'Asia/Shanghai'
     * @throws null
     */
    public static function fStandardTimeZone($timeZone = '')
    {
        if (empty($timeZone)) {
            return '';
        }

        if (stripos($timeZone, 'Asia/Hanoi') !== false) { // php不识别Asia/Hanoi
            $timeZone = 'Asia/Ho_Chi_Minh';
        }

        if (stripos($timeZone, ' ') === false) {
            $o = new \DateTimeZone($timeZone); // 时区名称不合法时，会抛出异常
            return $o->getName();
        }
        // 处理eg：(Asia/Shanghai (GMT+8) offset 28800) 情况.
        $ex = null;
        foreach (explode(' ', $timeZone) as $zone) {
            try {
                $zone = ltrim(rtrim($zone, ')'), '(');
                $o = new \DateTimeZone($zone); // 判断时区名称是否合法
                return $o->getName();
            } catch (\Exception $e) {
                $ex = $e;
            }
        }
        throw $ex;
    }

    /**
     * 时区时间转换
     * 
     * @param string $time      $time 字符串格式时间比如2015-12-12 19:26:36
     * @param string $toZone    需要转换的时区比如Asia/Shanghai
     * @param string $fromZone  当前时区比如Asia/Shanghai
     * @param string $format    返回时间格式
     */
    public static function transferTimeZone($time, $toZone, $fromZone = null, $format = "H")
    {
        if ($fromZone === null) {
            $fromZone = date_default_timezone_get();
        }
        $datetime = new \DateTime($time, new \DateTimeZone($fromZone));
        $datetime->setTimezone(new \DateTimeZone($toZone));
        return $datetime->format($format);
    }

    /**
     * 查询时区offset,时区不存在，默认返回+8偏移量
     *
     * @param string $timeZone 'America/Dawson_Creek'
     */
    public static function getOffsetByTimeZone($timeZone)
    {
        $gtm = 8;
        if (empty($timeZone)) {
            return $gtm;
        }
        try {
            $dateTimeZone = new \DateTimeZone($timeZone);
            $offset = $dateTimeZone->getOffset(new \DateTime());
            $gtm = $offset / 3600;
        } catch (\Exception $e) {
            LogUntil::error("getOffsetByTimeZone时区不能识别，返回默认+8时区");
        }
        return intval(round($gtm));
    }
}
