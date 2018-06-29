<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

class MessageUntil extends BaseUntil
{
    //错误码
    const INVAlID_CODE = 20001;
    const NOT_ESISTS_CODE = 20002;
    const NOT_ESISTS_USER = 20003;
    const GET_TOKEN_SUCCESS = 20004;
    const NOT_ESISTS_TOKEN = 20005;
    const INVAlID_TOKEN = 20006;
    const INVAlID_USERINFO = 20007;
    const INVAlID_USERID = 20009;
    const NO_BINDING_USER = 20010;
    const REQUIRED_USER_TYPE = 20011;


    //错误码提示信息
    const INFORM_MESSAGES = array (
        20001 => '无效的code',
        20002 => 'code不存在',
        20003 => '用户不存在',
        20004 => '获取token成功',
        20005 => 'token不存在',
        20006 => 'token不合法',
        20007 => '登录信息已经过期',
        20009 => 'User Id不合法',
        20010 => '未进行账户绑定,请先绑定',
        20011 => '缺少身份类型参数',
    );

}