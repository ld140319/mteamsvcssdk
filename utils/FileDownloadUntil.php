<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

class FileDownloadUntil extends BaseUntil
{
    /**
     * 下载文件
     * @param $filePath
     */
    public static function download($filePath)
    {
        if (!file_exists($filePath)) {
            LogUntil::error("FileDownloadUntil download --- 文件不存在");
            exit("文件不存在,下载失败");
        }
        header('Content-Type: application/octet-stream');
        header('Content-Description: File Transfer');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Disposition: attachment; filename=' . basename($filePath));
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

}