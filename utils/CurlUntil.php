<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

class CurlUntil extends BaseUntil
{
	private $ch;
    const ConnectTimeOut = 10; //连接建立超时时间
	const ExecuteTimeOut = 30; //执行超时时间

	/**
	 * @function	construct function
	 **/
    public function __construct()
    {   
        $this->ch = curl_init();
    }

	
	/**
	 * @function	destruct function
	 **/
    public function __destruct()
    {   
        curl_close($this->ch);
    } 	

	/**
	 * @function	get the data
	 * @param		$url:url
	 * @param		$cookie:cookie
	 * @param		$timeOut:timeout
	 * @return		bool|array
	 **/
	public function getWithCookie($url, $cookie)
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, true) ;
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, self::ConnectTimeOut) ;
        curl_setopt($this->ch, CURLOPT_TIMEOUT, self::ExecuteTimeOut) ;
        curl_setopt($this->ch, CURLOPT_COOKIE, $cookie);
        $result = curl_exec($this->ch);
        if (curl_errno($this->ch) !== 0) {
            LogUntil::error(__CLASS__." curl error: ".curl_error($this->ch));
            return false;
        }
        $result = json_decode($result, true);
        return $result;
    }

	/**
	 * @function	get the data
	 * @param		$url:url
	 * @param		$queryString
	 * @return		bool|array
	 **/
	public function get($url, $queryString=array())
    {
	    if (!empty($queryString)) {
            $url .= "?";
            $url .= http_build_query($queryString);
        }
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, true) ;
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, self::ConnectTimeOut) ;
        curl_setopt($this->ch, CURLOPT_TIMEOUT, self::ExecuteTimeOut) ;
        $result = curl_exec($this->ch);
        if (curl_errno($this->ch) !== 0) {
            LogUntil::error(__CLASS__." curl error: ".curl_error($this->ch));
            return false;
        }
        $result = json_decode($result, true);
		return $result;
    }

	/**
	 * @function	post the data
	 * @param		$url:url
	 * @param		$postData:the data to be posted
	 * @return		bool|array
	 **/
	public  function post($url, $postData)
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt($this->ch, CURLOPT_POST, true) ;
        curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, true) ;
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, self::ConnectTimeOut) ;
        curl_setopt($this->ch, CURLOPT_TIMEOUT, self::ExecuteTimeOut) ;
        $result = curl_exec($this->ch) ;
        if (curl_errno($this->ch) !== 0) {
            LogUntil::error(__CLASS__." curl error: ".curl_error($this->ch));
            return false;
        }
        $result = json_decode($result, true);
		return $result;
    }

}
?>
