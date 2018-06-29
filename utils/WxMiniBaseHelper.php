<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;


/**
 * 微信小程序helper,用于封装异常处理以及获取openid
 * Class WxBaseHelper
 * @package CMS\MTeamServicesSDK\Until
 */
class WxMiniBaseHelper
{
    //错误码
    const INVAlID_CODE = 20001;
    const NOT_ESISTS_CODE = 20002;
    const NOT_ESISTS_USER = 20003;
    const GET_TOKEN_SUCCESS = 20004;
    const NOT_ESISTS_TOKEN = 20005;
    const INVAlID_TOKEN = 20006;
    const INVAlID_USERINFO = 20007;
    const INVAlID_OPENID = 20008;
    const INVAlID_USERID = 20009;
    const NO_BINDING_USER = 20010;
    const INVALID_USER_STATUS = 20011;
    const OPENID_ALREADY_BOUND = 20012;
    const INVALID_RESP_CODE = 20013;
    const OPENID_BOUND_FAILED = 20014;


    //错误码提示信息
    const INFORM_MESSAGES = array (
        self::INVAlID_CODE => '无效的code',
        self::NOT_ESISTS_CODE => 'code不存在',
        self::NOT_ESISTS_USER => '用户不存在',
        self::GET_TOKEN_SUCCESS => '获取token成功',
        self::NOT_ESISTS_TOKEN => 'token不存在',
        self::INVAlID_TOKEN => 'token不合法',
        self::INVAlID_USERINFO => '登录信息已经过期',
        self::INVAlID_OPENID => '无效的openid',
        self::INVAlID_USERID => 'User Id不合法',
        self::NO_BINDING_USER => '未进行账户绑定,请先绑定',
        self::INVALID_USER_STATUS => '账户被禁用',
        self::OPENID_ALREADY_BOUND => '该账号已经被绑定',
        self::INVALID_RESP_CODE => '无效的code',
        self::OPENID_BOUND_FAILED => 'openid绑定失败'
    );


    //微信配置参数
    const WX_CONFIG = array(
        'appid' => 'wxb20ff3520cc6ae8b',
        'secret' => '2ddd2a81d6ca1ac4daa291640425f2f3'
    );

    //加密的盐
    const CacheSecret = 'secret';


    public static function getRespMessage($code)
    {
        if (isset(self::INFORM_MESSAGES[$code])) {
            return self::INFORM_MESSAGES[$code];
        }
        return self::INFORM_MESSAGES[self::INVALID_RESP_CODE];
    }

    //'https://api.weixin.qq.com/sns/jscode2session?appid=APPID&secret=SECRET&js_code=JSCODE&grant_type=authorization_code'

    /**
     * 获取openid
     * @param $code
     * @return mixed
     */
    public static function getWxOpenId($code) {
        $url = 'https://api.weixin.qq.com/sns/jscode2session?';
        $params = array(
            'appid' => self::WX_CONFIG['appid'],
            'secret' => self::WX_CONFIG['secret'],
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        );
        $url = $url.http_build_query($params);
        $_curl = curl_init();
        $_header = array(
            'Accept-Language: zh-CN',
            'Connection: Keep-Alive',
            'Cache-Control: no-cache'
        );
        curl_setopt($_curl, CURLOPT_URL, $url);
        curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($_curl, CURLOPT_HTTPHEADER, $_header);
        curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($_curl, CURLOPT_TIMEOUT, 60); // 处理超时时间
        curl_setopt($_curl, CURLOPT_CONNECTTIMEOUT, 30); // 建立连接超时时间
        $result['result'] = curl_exec($_curl);
        $result['code'] = curl_getinfo($_curl, CURLINFO_HTTP_CODE);
        $result['info'] = curl_getinfo($_curl);
        if ($result['result'] === false) {
            $result['result'] = curl_error($_curl);
            $result['code'] = -curl_errno($_curl);
            LogUntil::error("WxBaseHelper getWxOpenId --- curl error:".json_encode($result));
        }
        curl_close($_curl);
        $info = json_decode($result['result'], true);
        return $info;
    }
}
