<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

use Illuminate\Contracts\Support\Arrayable;

class ArrayUntil extends BaseUntil
{
    public static function toArray($object, $properties = [], $recursive = true, $includeNull = true)
    {
        if (is_array($object)) {
            //数组
            if ($recursive) {
                foreach ($object as $key => $value) {
                    if (is_array($value) || is_object($value)) {
                        $object[$key] = static::toArray($value, $properties, true, $includeNull);
                    }
                }
            }
            return $object;
        } elseif (is_object($object)) {
            //对象
            if (!empty($properties)) {
                $className = get_class($object); //获取类全名
                if (!empty($properties[$className])) { //获取对应类属性
                    $result = [];
                    foreach ($properties[$className] as $key => $name) {
                        //自身属性
                        if (is_int($key)) {
                            if ($includeNull || $object->$name !== null) {
                                $result[$name] = $object->$name;
                            }
                        } else {
                            //别名属性
                            //$value = static::getValue($object, $name); //获取属性值，这里是yii2的一个特有方法，用来获取数组元素或者对象属性的值
                            $value = $object->$name;
                            if ($includeNull || $value !== null) {
                                $result[$key] = $value;
                            }
                        }
                    }
                    return $recursive ? static::toArray($result, $properties, true, $includeNull) : $result;
                }
            }
            if ($object instanceof Arrayable) {
                $result = $object->toArray([], [], $recursive);
            } else {
                $result = [];
                foreach ($object as $key => $value) {
                    if ($includeNull || $value !== null) {
                        $result[$key] = $value;
                    }
                }
            }
    
            return $recursive ? static::toArray($result, $properties, true, $includeNull) : $result;
        } else {
            //其它类型
            return [$object];
        }
    }
}
