<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

/**
 * 微信小程序helper,用于支付
 * Class WxPayUntil
 * @package CMS\MTeamServicesSDK\Until
 */
class WxMiniPayUntil extends BaseUntil
{
    private  $config = array(
        'appid'		 => 'xxxxxxxxxxxxxx',  //小程序ID
        'pay_mchid'	 => 'xxxxxxxx',        //商户ID
        'pay_apikey' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', //微信支付密钥  key设置路径：微信商户平台(pay.weixin.qq.com)-->账户设置-->API安全-->密钥设置
    );

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * 将一个数组转换为 XML 结构的字符串
     * @param array $arr 要转换的数组
     * @param int $level 节点层级, 1 为 Root.
     * @return string XML 结构的字符串
     * WxPayUntil::array2xml(['name'=>'lzm', 'age' => ['age1' => 10, 'age2' => 29], ['TagName'=>'test_alias', 'height' => 165]]
     * <xml><name><![CDATA[lzm]]></name><age><age1>10</age1><age2>29</age2></age><test><height>165</height></test></xml>
     */
    public static function array2xml($arr, $level = 1)
    {
        $s = ($level == 1 ? "<xml>" : '');
        foreach($arr as $tag_name => $value) {
            if (is_numeric($tag_name)) {
                if (!isset($value['TagName'])) {
                    throw new \Exception("XML Node缺少TagName参数");
                }
                $tag_name = $value['TagName'];
                unset($value['TagName']);
            }
            //<![CDATA[ ]]表示不被当做xml解析
            if(!is_array($value)) {
                $s .= "<{$tag_name}>".(!is_numeric($value) ? '<![CDATA[' : '').$value.(!is_numeric($value) ? ']]>' : '')."</{$tag_name}>";
            } else {
                $s .= "<{$tag_name}>" . self::array2xml($value, $level + 1)."</{$tag_name}>";
            }
        }
        $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
        return $level == 1 ? $s."</xml>" : $s;
    }

    /**
     * 将xml转为array
     * @param  string 	$xml xml字符串
     * @return array    转换得到的数组
     */
    public static function xml2array($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }

    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return string
     */
    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    /**
     * 生成签名
     * @return string
     */
    public function makeSign($data)
    {
        //获取微信支付秘钥
        $key = $this->config['pay_apikey'];
        // 去空
        $data = array_filter($data); //参数的值为空不参与签名
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string_a = http_build_query($data);
        $string_a = urldecode($string_a);
        //签名步骤二：在string后加入KEY
        $string_sign_temp = $string_a."&key=".$key;
        //签名步骤三：MD5加密
        $sign = md5($string_sign_temp);
        // 签名步骤四：所有字符转为大写
        $result = strtoupper($sign);
        return $result;
    }

    /**
     * 错误返回提示
     * @param string $errMsg 错误信息
     * @param string $status 错误码
     * @return  string
     */
    public static function return_err($errMsg='error', $status=0)
    {
        exit(json_encode(array('status'=>$status, 'result'=>'fail', 'errmsg'=>$errMsg)));
    }


    /**
     * 正确返回
     * @param 	array $data 要返回的数组
     * @return  string
     */
    public static function return_data($data=array())
    {
        exit(json_encode(array('status'=>1, 'result'=>'success', 'data'=>$data)));
    }
}
