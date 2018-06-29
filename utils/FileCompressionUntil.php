<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

use \ZipArchive;

class FileCompressionUntil extends BaseUntil
{
    protected static  $zipPath = "";

    public function __construct($storagePath="")
    {
        if (empty($storagePath) && function_exists('public_path')) {
            $storagePath = public_path();
        }
        if (empty($storagePath) && !function_exists('public_path')) {
            $storagePath = 'public';
        }
        self::$zipPath = $storagePath. DIRECTORY_SEPARATOR.'zip'.DIRECTORY_SEPARATOR;
    }

    /**
     * 创建压缩文件
     * @param $originFilePathList
     * 源文件列表
     * @param $zipFilePath
     * 压缩文件名称
     * @return bool
     */
    public static function compression($originFilePathList, $zipFilePath)
    {
        $is_success = false;
        try {
            if (!file_exists($zipFilePath)) {
                $zip = new ZipArchive();
                if ($zip->open($zipFilePath, ZIPARCHIVE::CREATE) !== true) {
                    LogUntil::error("FileCompressionUntil compression:  无法打开文件，或者文件创建失败");
                    echo date('Y-m-d H:i:s')."  FileCompressionUntil compression:  无法打开文件，或者文件创建失败".PHP_EOL;
                    exit('无法打开文件，或者文件创建失败');
                }
                foreach($originFilePathList as $originFilePath){
                    if(file_exists($originFilePath)){
                        //$zip->addFromString();
                        $zip->addFile($originFilePath, basename($originFilePath));
                        //unlink($originFilePath);
                    }
                }
                $zip->close();
                $is_success = true;
            }
            return $is_success;
        } catch (\Exception $e) {
            LogUntil::error("FileCompressionUntil compression  ---   find Exception:".$e->getMessage());
            echo date('Y-m-d H:i:s')."  FileCompressionUntil compression  ---   find Exception:".$e->getMessage().PHP_EOL;
            return $is_success;
        }
    }

    /**
     * 获取远程文件内容,保存为临时文件
     * @param $url
     * @param $saveDir
     */
    public static function createTmpFile($url, $saveDir="")
    {
        try {
            $saveDir = empty($saveDir) ? self::$zipPath : $saveDir;
            if (empty($url) || empty($saveDir)) {
                return false;
            }

            $temp = explode('/', $url);
            $filename = end($temp);

            if($filename == "") {
                $ext = strrchr($url,".");
                $filename = date("YmdHis").$ext;
            }

            ob_start();
            readfile($url);
            $content = ob_get_contents();
            ob_end_clean();

            $filePath = $saveDir.$filename;
            touch($filePath);
            $fp = @fopen($filePath, "a");
            fwrite($fp, $content);
            fclose($fp);

            if (file_exists($filePath)) {
                return $filePath;
            }
            return false;
        } catch (\Exception $e) {
            echo date('Y-m-d H:i:s').'  FileCompressionUntil createTmpFile --- exception:'.$e->getMessage().PHP_EOL;
            LogUntil::error('FileCompressionUntil createTmpFile --- exception:'.$e->getMessage());
            return false;
        }

    }

    /**
     * 获取文件内容
     * @param $url
     */
    public static function getFileContent($url, $saveDir)
    {
        if (empty($url)) {
            return false;
        }
        $temp = explode('/', $url);
        $filename = end($temp);

        if($filename == "") {
            $ext = strrchr($url,".");
            $filename = date("YmdHis").$ext;
        }

        $filePath = $saveDir.$filename;
        $fp = fopen($filePath,'wb');

        $hander = curl_init();

        curl_setopt($hander,CURLOPT_URL, $url);
        curl_setopt($hander,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($hander,CURLOPT_FILE, $fp);
        curl_setopt($hander,CURLOPT_HEADER,false);
        curl_setopt($hander,CURLOPT_FOLLOWLOCATION,true);
        curl_setopt($hander,CURLOPT_TIMEOUT,90);
        curl_setopt($hander,CURLINFO_CONNECT_TIME,30);
        curl_exec($hander);

        if (curl_errno($hander) === CURLE_OK ) {
            return $filePath;
        }

        echo date('Y-m-d H:i:s').'  curl error --- error:'.curl_error($hander).PHP_EOL;
        LogUntil::error('curl error --- error:'.curl_error($hander));
        return false;
    }

}