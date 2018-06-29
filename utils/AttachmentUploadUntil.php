<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

use CMS\MTeamServicesSDK\Exception\FileException;

/**
 * 附件上传
 * Class AttachmentUploadUntil
 * @package CMS\MTeamServicesSDK\Until
 */
class AttachmentUploadUntil extends BaseUntil
{
    private $fileType; //'txt', 'docx', 'doc', 'md', 'zip', 'tar' one, default attachment

    private $attachmentTypes; //允许上传的附件格式

    private $uploadDir;  //保存到服务器的那个目录(绝对路径)

    private $prefixUploadDir;  //文件保存到数据库的地址(public目录之后的部分)

    public function __construct(
        $fileType="attachment",
        $attachmentTypes=array('txt', 'docx', 'doc', 'md', 'zip', 'tar'),
        $uploadDir="",
        $prefixUploadDir=""
    )
    {
        $this->fileType = $fileType;
        $this->attachmentTypes = $attachmentTypes;
        $this->uploadDir = empty($uploadDir) ? 'public'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$this->fileType.DIRECTORY_SEPARATOR : $uploadDir;
        $this->prefixUploadDir = empty($prefixUploadDir) ? DIRECTORY_SEPARATOR . 'uploads'.DIRECTORY_SEPARATOR.$this->fileType.DIRECTORY_SEPARATOR : $prefixUploadDir;
    }

   public static function getInstance(
       $fileType="attachment",
       $attachmentTypes=array('txt', 'docx', 'doc', 'md', 'zip', 'tar'),
       $uploadDir="",
       $prefixUploadDir=""
   )
   {
       if (self::$instance == null) {
           self::$instance = new static($fileType, $attachmentTypes, $uploadDir, $prefixUploadDir);
       }
       return self::$instance;
   }

    public  function upload($files)
   {
       $attachment_url = array();
       //多文件上传
       if (is_array($files['name'])) {
           foreach ($files['name'] as $index => $filename) {
               if ($files['error'][$index] != 0) {
                   throw new FileException('附件上传过程中出错');
               }
               $extension = pathinfo($filename, PATHINFO_EXTENSION);
               if (!in_array(strtolower($extension), $this->attachmentTypes)) {
                   throw new FileException('附件类型不支持上传');
               }
               $uploadDir = $this->uploadDir.strtolower($extension).DIRECTORY_SEPARATOR;
               $prefixUploadDir = $this->prefixUploadDir.strtolower($extension).DIRECTORY_SEPARATOR;
               if (!is_uploaded_file($files['tmp_name'][$index])) {
                   throw new FileException('非法操作');
               }
               $basename = pathinfo($filename, PATHINFO_BASENAME);
               $msec = self::get_millisecond();
               $saveName = md5($basename).$msec.'.'.$extension;
               if (move_uploaded_file($files['tmp_name'][$index], $uploadDir.$saveName)) {
                   $attachment_url[] = $prefixUploadDir.$saveName;
               } else {
                   throw new FileException('上传附件失败');
               }
           }
       } else {
           //单文件上传
           if ($files['error'] != 0) {
               throw new FileException('上传过程中出错');
           }
           $filename = $files['name'];
           $extension = pathinfo($filename, PATHINFO_EXTENSION);
           if (!in_array(strtolower($extension), $this->attachmentTypes, true)) {
               throw new FileException('附件类型不支持上传');
           }
           $uploadDir = $this->uploadDir.strtolower($extension).DIRECTORY_SEPARATOR;
           $prefixUploadDir = $this->prefixUploadDir.strtolower($extension).DIRECTORY_SEPARATOR;
           if (!is_uploaded_file($files['tmp_name'])) {
               throw new FileException('非法操作');
           }
           $basename = pathinfo($filename, PATHINFO_BASENAME);
           $msec = self::get_millisecond();
           $saveName = md5($basename).$msec.'.'.$extension;
           if (move_uploaded_file($files['tmp_name'], $uploadDir.$saveName)) {
               $attachment_url [] = $prefixUploadDir.$saveName;
           }
       }
       if (is_array($attachment_url) && !empty($attachment_url)) {
           $attachment_url = implode(',', $attachment_url);
       }
       $attachment_name = $files['name'];
       if (!is_array($attachment_name)) {
           $attachment_name = (array)$attachment_name;
       }
       $attachment['attachment_url'] = $attachment_url;
       $attachment['attachment_name'] = $attachment_name;
       return $attachment;
   }

   //避免文件重名,获取当前毫秒数
    public  static function get_millisecond()
    {
        list($usec, $sec) = explode(" ", microtime());
        $msec = round($usec*1000);
        return $msec;
    }

}
