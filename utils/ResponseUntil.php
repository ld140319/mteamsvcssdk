<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

/**
 * 给前台返回数据
 * Class ResponseUntil
 * @package CMS\MTeamServicesSDK\Until
 */
class ResponseUntil extends BaseUntil
{
   const OperationSuccessful = 10004; //操作成功

   const OperationFailed = 10003; //操作失败

    /**
     * 返回数据
     * @param $data
     * @param $msg
     * @param $status
     * @return string
     */
   public static function ouputJson($data, $msg, $status)
   {
       $res = array(
           'data' => $data,
           'message' => $msg,
           'status' => $status
       );
       header('Content-Type: application/json;charset=utf8');
       return json_encode($res, JSON_UNESCAPED_SLASHES);
       /*header('Content-Type: application/json;charset=utf8');
       echo(json_encode($res, JSON_UNESCAPED_UNICODE));
       exit;*/
   }

}
