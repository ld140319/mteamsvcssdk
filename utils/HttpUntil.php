<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

use CMS\MTeamServicesSDK\Until\LogUntil;

class HttpUntil extends BaseUntil
{

    /**
     * 枚举所有curl支持的错误码
     * @var array
     */
    protected static $errorTypes = array(
        'OK',
        'CURLE_UNSUPPORTED_PROTOCOL',
        'CURLE_FAILED_INIT',
        'CURLE_URL_MALFORMAT',
        'CURLE_NOT_BUILT_IN',
        'CURLE_COULDNT_RESOLVE_PROXY',
        'CURLE_COULDNT_RESOLVE_HOST',
        'CURLE_COULDNT_CONNECT',
        'CURLE_FTP_WEIRD_SERVER_REPLY',
        'CURLE_REMOTE_ACCESS_DENIED',
        'CURLE_FTP_ACCEPT_FAILED',
        'CURLE_FTP_WEIRD_PASS_REPLY',
        'CURLE_FTP_ACCEPT_TIMEOUT',
        'CURLE_FTP_WEIRD_PASV_REPLY',
        'CURLE_FTP_WEIRD_227_FORMAT',
        'CURLE_FTP_CANT_GET_HOST',
        'CURLE_FTP_COULDNT_SET_TYPE',
        'CURLE_PARTIAL_FILE',
        'CURLE_FTP_COULDNT_RETR_FILE',
        'CURLE_QUOTE_ERROR',
        'CURLE_HTTP_RETURNED_ERROR',
        'CURLE_WRITE_ERROR',
        'CURLE_UPLOAD_FAILED',
        'CURLE_READ_ERROR',
        'CURLE_OUT_OF_MEMORY',
        'CURLE_OPERATION_TIMEDOUT',
        'CURLE_FTP_PORT_FAILED',
        'CURLE_FTP_COULDNT_USE_REST',
        'CURLE_RANGE_ERROR',
        'CURLE_HTTP_POST_ERROR',
        'CURLE_SSL_CONNECT_ERROR',
        'CURLE_BAD_DOWNLOAD_RESUME',
        'CURLE_FILE_COULDNT_READ_FILE',
        'CURLE_LDAP_CANNOT_BIND',
        'CURLE_LDAP_SEARCH_FAILED',
        'CURLE_FUNCTION_NOT_FOUND',
        'CURLE_ABORTED_BY_CALLBACK',
        'CURLE_BAD_FUNCTION_ARGUMENT',
        'CURLE_INTERFACE_FAILED',
        'CURLE_TOO_MANY_REDIRECTS',
        'CURLE_UNKNOWN_OPTION',
        'CURLE_TELNET_OPTION_SYNTAX',
        'CURLE_PEER_FAILED_VERIFICATION',
        'CURLE_GOT_NOTHING',
        'CURLE_SSL_ENGINE_NOTFOUND',
        'CURLE_SSL_ENGINE_SETFAILED',
        'CURLE_SEND_ERROR',
        'CURLE_RECV_ERROR',
        'CURLE_SSL_CERTPROBLEM',
        'CURLE_SSL_CIPHER',
        'CURLE_SSL_CACERT',
        'CURLE_BAD_CONTENT_ENCODING',
        'CURLE_LDAP_INVALID_URL',
        'CURLE_FILESIZE_EXCEEDED',
        'CURLE_USE_SSL_FAILED',
        'CURLE_SEND_FAIL_REWIND',
        'CURLE_SSL_ENGINE_INITFAILED',
        'CURLE_LOGIN_DENIED',
        'CURLE_TFTP_NOTFOUND',
        'CURLE_TFTP_PERM',
        'CURLE_REMOTE_DISK_FULL',
        'CURLE_TFTP_ILLEGAL',
        'CURLE_TFTP_UNKNOWNID',
        'CURLE_REMOTE_FILE_EXISTS',
        'CURLE_TFTP_NOSUCHUSER',
        'CURLE_CONV_FAILED',
        'CURLE_CONV_REQD',
        'CURLE_SSL_CACERT_BADFILE',
        'CURLE_REMOTE_FILE_NOT_FOUND',
        'CURLE_SSH',
        'CURLE_SSL_SHUTDOWN_FAILED',
        'CURLE_AGAIN',
        'CURLE_SSL_CRL_BADFILE',
        'CURLE_SSL_ISSUER_ERROR',
        'CURLE_FTP_PRET_FAILED',
        'CURLE_RTSP_CSEQ_ERROR',
        'CURLE_RTSP_SESSION_ERROR',
        'CURLE_FTP_BAD_FILE_LIST',
        'CURLE_CHUNK_FAILED',
        'CURLE_NO_CONNECTION_AVAILABLE',  
    );

    protected static $header = array();

    /**
     * curl 错误码
     * @var int
    */
    protected static $lastError = 0;

    /**
     * @return int
     */
    public static function getLastError()
    {
        return self::$lastError;
    }

    /**
     * 根据错误码返回可读信息
     * @return string
     */
    public static function getLastErrorMsg()
    {
        $ret = self::$errorTypes[self::$lastError];
        return $ret;
    }
    
    /**
     * 下载URL指定内容，支持HTTP、HTTPS、FTP等协议，支持返回内容或者写入指定文件。
     * @param string $url 下载地址
     * @param int $timeout 超时时间
     * @param string $saveTo 内容写入文件地址，null为直接返回内容
     * @param array $options 其它cURL选项
     * @return bool|string TRUE，FALSE或者内容
     */
    public static function get($url, $timeout = 10, $saveTo = null, $options = array(), &$header = null)
    {
        $arrUrl = parse_url($url);
        $profileName = (isset($arrUrl['host']) ? strval($arrUrl['host']) : '') . (isset($arrUrl['path']) ? strval($arrUrl['path']) : '');
        LogUntil::info("HttpUntil get --- start url info: ".$profileName);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (isset($header)) {
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'self::headerCallBack'); 
        }
        // forward logid
        if (isset($_SERVER['LOG_ID']) && is_string($_SERVER['LOG_ID']) && !empty($_SERVER['LOG_ID'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Ngx-LogId: ' . strval($_SERVER['LOG_ID'])));
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 500);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt_array($ch, $options);
        if (!is_null($saveTo) && ($fp = fopen($saveTo, 'w+'))) {
            curl_setopt($ch, CURLOPT_FILE, $fp);
        } else {
            $fp = null;
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        }
        $return = curl_exec($ch);
        if ($fp) {
            fclose($fp);
        }
        LogUntil::info("HttpUntil get --- end url info: ".$profileName);
        if (curl_errno($ch)) {
            self::$lastError = curl_errno($ch);
            LogUntil::error('HttpUntil get --- get request failed. url-'.$url.' errno-'.curl_errno($ch).' error-'.curl_error($ch));
            return false;
        }
        if (isset($header)) {
            $header = self::$header;
        }
        curl_close($ch);
        return $return;
    }
    
    /**
     * 下载URL指定内容，支持HTTP、HTTPS、FTP等协议，支持返回内容或者写入指定文件。
     * @param string $url 下载地址
     * @param int $timeout 超时时间毫秒
     * @param string $saveTo 内容写入文件地址，null为直接返回内容
     * @param array $options 其它cURL选项
     * @return bool|string TRUE，FALSE或者内容
     */
    public static function getMs($url, $timeout = 500, $saveTo = null, $options = array(), &$header = null)
    {
        $arrUrl = parse_url($url);
        $profileName = (isset($arrUrl['host']) ? strval($arrUrl['host']) : '') . (isset($arrUrl['path']) ? strval($arrUrl['path']) : '');
        LogUntil::info("HttpUntil getMs --- start url info: ".$profileName);
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (isset($header)) {
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'self::headerCallBack');
        }
        // forward logid
        if (isset($_SERVER['LOG_ID']) && is_string($_SERVER['LOG_ID']) && !empty($_SERVER['LOG_ID'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Ngx-LogId: ' . strval($_SERVER['LOG_ID'])));
        }
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 500);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt_array($ch, $options);
        if (!is_null($saveTo) && ($fp = fopen($saveTo, 'w+'))) {
            curl_setopt($ch, CURLOPT_FILE, $fp);
        } else {
            $fp = null;
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        }
        $return = curl_exec($ch);
        if ($fp) {
            fclose($fp);
        }
        LogUntil::info("HttpUntil getMs --- end url info: ".$profileName);
        if (curl_errno($ch)) {
            self::$lastError = curl_errno($ch);
            LogUntil::error('HttpUntil getMs --- get request failed. url-'.$url.' errno-'.curl_errno($ch).' error-'.curl_error($ch));
            return false;
        }
        if (isset($header)) {
            $header = self::$header;
        }
        curl_close($ch);
        return $return;
    }
    
    /**
     * 以 POST 方式执行请求.
     * 
     * @param string $url       请求目标地址
     * @param array  $params    请求参数,格式如: array('id'=>10,'name'=>'yuanwei')
     * @param int    $timeout   请求超时时间
     * @param array  $options   其它cURL选项
     * @static
     * @access public
     * @return mixed 正确返回:结果内容
     */
    public static function post($url, $params = array(), $timeout = 10, $options = array(), &$header = null)
    {
        $arrUrl = parse_url($url);
        $profileName = (isset($arrUrl['host']) ? strval($arrUrl['host']) : '') . (isset($arrUrl['path']) ? strval($arrUrl['path']) : '');
        LogUntil::info("HttpUntil post --- start url info: ".$profileName);

        $ch = curl_init();
        // forward logid
        if (isset($_SERVER['LOG_ID']) && is_string($_SERVER['LOG_ID']) && !empty($_SERVER['LOG_ID'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Ngx-LogId: ' . strval($_SERVER['LOG_ID'])));
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        if (isset($header)) {
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'self::headerCallBack');
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 500);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt_array($ch, $options);
        $return = curl_exec($ch);
        LogUntil::info("HttpUntil post --- end url info: ".$profileName);
        if (curl_errno($ch)) {
            self::$lastError = curl_errno($ch);
            LogUntil::error('HttpUntil post ---  post request failed. url-' . $url . ' params-' . json_encode($params) . ' errno-' . curl_errno($ch) . ' error-' . curl_error($ch));
            return false;
        }
        if (isset($header)) {
            $header = self::$header;
        }
        curl_close($ch);
        return $return;
    }
    
    /**
     * 以 POST 方式执行请求.
     * 
     * @param string $url       请求目标地址
     * @param array  $params    请求参数,格式如: array('id'=>10,'name'=>'yuanwei')
     * @param int    $timeout   请求超时时间毫秒
     * @param array  $options   其它cURL选项
     * @static
     * @access public
     * @return mixed:false 正确返回:结果内容
     */
    public static function postMs($url, $params = array(), $timeout = 500, $options = array(), &$header = null)
    {
        $arrUrl = parse_url($url);
        $profileName = (isset($arrUrl['host']) ? strval($arrUrl['host']) : '') . (isset($arrUrl['path']) ? strval($arrUrl['path']) : '');
        LogUntil::info("HttpUntil postMs --- start url info: ".$profileName);

        $ch = curl_init();
         // forward logid
        if (isset($_SERVER['LOG_ID']) && is_string($_SERVER['LOG_ID']) && !empty($_SERVER['LOG_ID'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Ngx-LogId: ' . strval($_SERVER['LOG_ID'])));
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        if (isset($header)) {
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'self::headerCallBack');
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 500);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt_array($ch, $options);
        $return = curl_exec($ch);
        LogUntil::info("HttpUntil postMs --- end url info: ".$profileName);
        if (curl_errno($ch)) {
            self::$lastError = curl_errno($ch);
            LogUntil::error('HttpUntil postMs --- post request failed. url-' . $url . ' params-' . json_encode($params) . ' errno-' . curl_errno($ch) . ' error-' . curl_error($ch));
            return false;
        }
        if (isset($header)) {
            $header = self::$header;
        }
        curl_close($ch);
        return $return;
    }
    
    protected static function headerCallBack($ch, $str) 
    {
        @list($key, $value) = explode(':', $str);
        $key = trim($key);
        $value = trim($value);
        if (!$value && $key) {
            self::$header[] = $key;
        } elseif ($value) {
            self::$header[$key] = $value;
        }
        return strlen($str);
        /*
         * 浏览器会发送 Accept 指令，告诉服务器可接受的内容类型，但这不能阻止服务器返回不接受的内容，即哪怕请求的资源不在 Accept 列表中，仍然会把文件返回给客户端
         * 想着，有没有办法，在得到服务器 response 的时候先分析 header，如果不是可接受的类型，就断掉连接，不必下载后续内容？
            选项 CURLOPT_HEADERFUNCTION
            这个选项要给一个回调函数作为参数，回调函数相当于一个触发器，CUrl 会把每一条 response 的 header 指令传给这个函数，由函数来决定如何执行后面的步骤。
            回调函数要定义 2 个参数，第 1 个是 CUrl 的资源标识符，第 2 个是单独的一条 header 字符串。

            如果想让 CUrl 继续下载后续内容，那么回调函数应该返回 header 字符串的长度：

            return strlen($strOneHeader);

            如果不想继续下载，直接返回数字 0，那么 CUrl 就会发送 TCP 的终止连接的指令，不会继续下载后续内容了。
        */
    }
}
