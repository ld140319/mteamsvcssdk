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
 * 图片上传
 * Class ImageUploadUntil
 * @package CMS\MTeamServicesSDK\Until
 */
class ImageUploadUntil extends BaseUntil
{
    private $fileType; //'jpg', 'jpeg', 'png', 'gif' one, default image

    private $imageTypes; //允许上传的附件格式

    private $uploadDir;  //保存到服务器的那个目录(绝对路径)

    private $prefixUploadDir;  //文件保存到数据库的地址(public目录之后的部分)

    public function __construct(
        $fileType="image",
        $imageTypes=array('jpg', 'jpeg', 'png', 'gif'),
        $uploadDir="",
        $prefixUploadDir=""
    )
    {
        $this->fileType = $fileType;
        $this->imageTypes = $imageTypes;
        $this->uploadDir = empty($uploadDir) ? 'public'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$this->fileType.DIRECTORY_SEPARATOR : $uploadDir;
        $this->prefixUploadDir = empty($prefixUploadDir) ? DIRECTORY_SEPARATOR . 'uploads'.DIRECTORY_SEPARATOR.$this->fileType.DIRECTORY_SEPARATOR : $prefixUploadDir;
    }

    public static function getInstance(
        $fileType="image",
        $imageTypes=array('jpg', 'jpeg', 'png', 'gif'),
        $uploadDir="",
        $prefixUploadDir=""
    )
    {
        if (self::$instance == null) {
            self::$instance = new static($fileType, $imageTypes, $uploadDir, $prefixUploadDir);
        }
        return self::$instance;
    }

   public  function upload($files)
   {
       $img_url = array();
       //多文件上传
       if (is_array($files['name'])) {
           foreach ($files['name'] as $index => $filename) {
               if ($files['error'][$index] != 0) {
                   throw new FileException('图片上传过程中出错');
               }
               $extension = pathinfo($filename, PATHINFO_EXTENSION);
               $uploadDir = $this->uploadDir.strtolower($extension).DIRECTORY_SEPARATOR;
               $prefixUploadDir = $this->prefixUploadDir.strtolower($extension).DIRECTORY_SEPARATOR;
               if (!in_array(strtolower($extension), $this->imageTypes)) {
                   throw new FileException('图片类型不支持上传');
               }
               if (!is_uploaded_file($files['tmp_name'][$index])) {
                   throw new FileException('非法操作');
               }
               $basename = pathinfo($filename, PATHINFO_BASENAME);
               $msec = self::get_millisecond();
               $saveName = md5($basename).$msec.'.'.$extension;
               if (move_uploaded_file($files['tmp_name'][$index], $uploadDir.$saveName)) {
                   $img_url[] = $prefixUploadDir.$saveName;
               } else {
                   throw new FileException('上传图片失败');
               }
           }
       } else {
           //单文件上传
           if ($files['error'] != 0) {
               throw new FileException('上传过程中出错');
           }
           $filename = $files['name'];
           $extension = pathinfo($filename, PATHINFO_EXTENSION);
           $uploadDir = $this->uploadDir.strtolower($extension).DIRECTORY_SEPARATOR;
           $prefixUploadDir = $this->prefixUploadDir.strtolower($extension).DIRECTORY_SEPARATOR;
           if (!in_array(strtolower($extension), $this->imageTypes, true)) {
               throw new FileException('图片类型不支持上传');
           }
           if (!is_uploaded_file($files['tmp_name'])) {
               throw new FileException('非法操作');
           }
           $basename = pathinfo($filename, PATHINFO_BASENAME);
           $msec = self::get_millisecond();
           $saveName = md5($basename).$msec.'.'.$extension;
           if (move_uploaded_file($files['tmp_name'], $uploadDir.$saveName)) {
               $img_url [] = $prefixUploadDir.$saveName;
           }
       }
       if (is_array($img_url) && !empty($img_url)) {
           $img_url = implode(',', $img_url);
       }
       $img_name = $files['name'];
       if (!is_array($img_name)) {
           $img_name = (array)$img_name;
       }
       $img = array (
           'img_name' => $img_name,
           'img_url' => $img_url
       );
       return $img;
   }
   //避免图片重名,获取当前毫秒数
    public  static function get_millisecond()
    {
        list($usec, $sec) = explode(" ", microtime());
        $msec = round($usec*1000);
        return $msec;
    }
}
