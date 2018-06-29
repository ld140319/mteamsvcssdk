<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LogUntil extends BaseUntil
{
    //日志文件目录
    private static $logPath = "/var/log/project/";

    private static $log;

    public static function getInstance($project="default")
    {
        self::$log = new Logger($project);
        $logFileName = "$project.main-".date("Ymd").".log";
        $logFile = self::$logPath.$project.DIRECTORY_SEPARATOR.$logFileName;
        self::$log->pushHandler(new StreamHandler($logFile));
    }

    public static function info($msg)
    {
        self::getInstance();
        if (empty($msg)) {
            return false;
        }
        return self::$log->info($msg);
    }

    public static function error($msg)
    {
        self::getInstance();
        if (empty($msg)) {
            return false;
        }
        return self::$log->error($msg);
    }

    public static function warning($msg)
    {
        self::getInstance();
        if (empty($msg)) {
            return false;
        }
        return self::$log->warning($msg);
    }

    public static function notice($msg)
    {
        self::getInstance();
        if (empty($msg)) {
            return false;
        }
        return self::$log->notice($msg);
    }

}