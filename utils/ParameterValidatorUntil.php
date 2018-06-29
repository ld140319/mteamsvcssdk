<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

use CMS\MTeamServicesSDK\Exception\ValidateException;
use CMS\MTeamServicesSDK\Exception\WarnException;

/**
 * 进行统一的参数验证
 * Class ParameterValidatorUntil
 * @package CMS\MTeamServicesSDK\Until
 */
class ParameterValidatorUntil extends BaseUntil
{
    /**
     * 检查是否为空，为空并且设置默认值，则返回默认值
     *
     * @param array/string  $mixed/mixed $data  数组只支持一维数组
     * @param string        $porp
     * @param int           $default NAN
     * @return int
     * @throws ValidateException
     * @example ''、null、false、未定义的变量都会被认为是空
     */
    public static function checkEmpty($mixed, $porp, $default=NAN)
    {
        if (!is_array($mixed)) {
            throw new ValidateException('参数mixed不正确');
        }
        if (isset($mixed[$porp])) {
            $value=$mixed[$porp];
        }
        if (isset($value)) {
            //数组或者字符串,数组为空，字符串长度为0
            if ((is_array($value) && count($value) == 0) || (is_string($value) && strlen($value) == 0))  {
                //验证默认参数是否为数字,返回true代表表示 不是数字
                if (is_array($default) && count($default) != 0) {
                    $value = $default;
                } elseif (is_string($default) && strlen($default)>0 && $default != NAN) {
                    $value = $default;
                }
            }
        } else if (!empty($default)) {
            $value = $default;
        } else {
            $value = null;
        }
        //处理空白字符
        if (isset($value) && is_string($value)) {
            $value = trim($value);
        }
        if ($value == null  || (is_string($value) && strlen($value) == 0) || (is_array($value) && count($value) == 0)) {
            throw new ValidateException("$porp can't be empty!");
        }
        return $value;
    }

    /**
     * 对整形的过滤处理
     *
     * @param array  $mixed/mixed $data
     * @param string $porp
     * @param int    $min default null 最小值为 -PHP_INT_MAX -1
     * @param int    $max default null 最大值  PHP_INT_MAX
     * @param int    $default NAN
     *
     * @return int
     * @throws ValidateException
     */
    public static function validateInteger($mixed, $porp = 'key', $min = null, $max = PHP_INT_MAX, $default = NAN)
    {
        $value = self::checkEmpty($mixed, $porp, $default);
        if (@is_nan($value) || !preg_match('/^-?\d+(\.\d)?\d*$/', $value)) {
            throw new ValidateException("$porp must be an integer.");
        }
        //非整数
        if (!preg_match('/^\s*[+-]?\d+\s*$/', $value)) {
            throw new ValidateException("$porp must be an integer.");
        }
        $value = intval($value);
        if (!is_int($value)) {
            throw new ValidateException("$porp must be an integer.");
        }
        if ($min != null) {
            $min = intval($min);
        }
        if ($max != null) {
            $max = intval($max);
        }
        if ($min == null) {
            $min = -PHP_INT_MAX - 1;
        }
        if (is_int($min) && $value < $min) {
            throw new ValidateException("$porp is too samll (minimum is $min)");
        }
        if (is_int($max) && $value > $max) {
            throw new ValidateException("$porp is too big (maximum is $max)");
        }
        return $value;
    }

    /**
     * 浮点数据验证
     *
     * @param array  $mixed/mixed $data
     *
     * @param String $porp
     * @param float  $min
     * @param float  $max
     * @param float  $default NAN
     *
     * @return float
     * @throws ValidateException
     */
    //进行浮点处理
    public static function validateFloat($mixed, $porp, $min = null, $max = null, $default = NAN)
    {
        $value = self::checkEmpty($mixed, $porp, $default);
        if (@is_nan($value) || !preg_match('/^-?\d+(\.\d)?\d*$/', $value)) {
            throw new ValidateException("$porp must be an float.");
        }
        $value = floatval($value);
        if ($min != null) {
            $min = floatval($min);
        }
        if ($max != null) {
            $max = floatval($max);
        }
        if ($min!==null && (!is_float($min) || @is_nan($min))) {
            throw new ValidateException("min must be an float");
        }
        if ($max!==null && (!is_float($max) || @is_nan($max))) {
            throw new ValidateException("max must be an float");
        }
        if ($min!==null && $value < $min) {
            throw new ValidateException("$porp is too small (minimum is $min)");
        }
        if ($max!==null && $value > $max) {
            throw new ValidateException("$porp is too big (maximum is $max)");
        }
        return $value;
    }

    /**
     * 数字验证
     *
     * @param array  $mixed/mixed $data
     * @param String $porp
     * @param number  $min
     * @param number  $max
     * @param number  $default NAN
     *
     * @return number
     * @throws ValidateException
     */
    public static function validateNumber($mixed, $porp, $min = null, $max = null, $default = NAN)
    {
        $value = self::checkEmpty($mixed, $porp, $default);
        if (@is_nan($value)) {
            throw new ValidateException("$porp must be an number.");
        }
        if (!preg_match('/^-?\d+(\.\d)?\d*$/', $value)) {
            throw new ValidateException("$porp must be an number.");
        }
        $value += 0;
        if ($min != null) {
            $min = floatval($min);
        }
        if ($max != null) {
            $max = floatval($max);
        }
        if ($min!==null && $value < $min) {
            throw new ValidateException("$porp is too small (minimum is $min)");
        }
        if ($max!==null && $value > $max) {
            throw new ValidateException("$porp is too big (maximum is $max)");
        }
        return $value;
    }

    /**
     * 字符串验证
     * @param array  $mixed/mixed $data
     * @param String $porp
     * @param int    $min
     * @param int    $max
     * @param string $default NAN
     * @return string
     * @throws ValidateException
     */
    public static function validateString($mixed, $porp, $min = null, $max = null, $default = NAN)
    {
        $value = self::checkEmpty($mixed, $porp, $default);
        if (!is_string($value) || empty($value) || @is_nan($value)) {
            throw new ValidateException("$porp must be a string.");
        }
        $length = mb_strlen($value); // 这里不能用strlen，字符串长度跟编码有关
        if ($length == 0) {
            throw new ValidateException("$porp can't be empty!");
        }
        if ($min!==null && $length < $min) {
            throw new ValidateException("$porp is too short (minimum is $min characters)");
        }

        if ($max!==null && $length > $max) {
            throw new ValidateException("$porp is too long (maximum is $max characters)");
        }
        return $value;
    }

    /**
     * 将传入的字符串按照指定的方式切割为数组
     *
     * @param array  $mixed/mixed $data
     * @param string $porp
     * @param float  $min
     * @param float  $max
     * @param string $default NAN
     * @return array
     * @throws ValidateException
     */
    public static function validateArray($mixed, $porp, $split = ',', $min = null, $max = null, $default = NAN)
    {
        $value = self::checkEmpty($mixed, $porp, $default);
        if (empty($value)|| @is_nan($value) ) {
            return array();
        }
        if (!is_array($value)) {
            $value = explode($split, $value);
        }
        $length=count($value);
        if ($min!==null && $length < $min) {
            throw new ValidateException("$porp is too short (minimum is $min elements).");
        }
        if ($max!==null && $length > $max) {
            throw new ValidateException("$porp is too long (maximum is $max elements).");
        }
        return $value;
    }

    public static function checkParameterIntegrity($rules, $data)
    {
        if (empty($rules)) {
            throw new WarnException("数据校验规则不能够为空");
        }
        if (empty($data)) {
            throw new WarnException("数据不能够为空");
        }
        foreach ($rules as $attribute => $rule) {
            if (!isset($data[$attribute]) || !$data[$attribute]) {
                throw new WarnException("$attribute is required");
            }
            if (is_callable($rule)) {
                if (($checkRes = $rule($data[$attribute])) !== true) {
                    throw new WarnException($checkRes);
                }
            }
        }
    }
}
