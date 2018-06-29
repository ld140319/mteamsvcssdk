<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

use CMS\MTeamServicesSDK\Until\LogUntil;
use CMS\MTeamServicesSDK\Until\CurlUntil;
use App\Models\Location;
use App\Models\LoginUser;
use App\Models\User;
use think\Db;

class MapUntil extends BaseUntil
{
    private $curlHelper;

    private $config;

    private static $excludeConfig = array(); //需要排除的配置

    const EARTH_RADIUS = 6371; //地球平均半径

    public function __construct($config = [
        [
            "key"=>"787c9c5088eaa69f25f1d96cb644798d",
            "secret"=>"6e196db65429ace4dd60a80cbee9b8ee"
        ]
    ])
    {
        $this->config = $config;
        $this->curlHelper = new CurlUntil();
    }

    public  function getConfig()
    {
        $config = array_rand($this->config);
        while (($index = array_search($config, self::$excludeConfig, true)) !== false) {
            $config = array_rand($this->config);
        }
        return $this->config[$index];
    }

    //通过地址获取经纬度
    public  function getCoordinatByAddress($address, $city="成都")
    {
        if (empty($address)) {
            return false;
        }
        $requestUrl = "http://restapi.amap.com/v3/geocode/geo";
        $config = $this->getConfig();
        $requestParams = array(
            "key" => $config["key"],
            "output" => "JSON",
            "address" => $address,
            "city" => $city
        );
        $res = $this->curlHelper->get($requestUrl, $requestParams);
        if ($res === false) {
            return false;
        }
        if ($res['status'] == 0 ) {
            if ($res['infocode'] == "10003") {
                self::$excludeConfig[] = $config;
            }
            LogUntil::info($res['info']."---".json_encode($config));
            return false;
        }
        if (isset($res["geocodes"][0])) {
            return $res["geocodes"][0]["location"];
        }
        LogUntil::error("经纬度转换失败 --- ".$address);
        return false;
    }

    //通过经纬度获取地址
    public  function getAddressByCoordinat($coordinat)
    {
        if (empty($coordinat)) {
            return false;
        }
        $requestUrl = "http://restapi.amap.com/v3/geocode/regeo";
        $config = $this->getConfig();
        $requestParams = array(
            "key" => $config["key"],
            "output" => "JSON",
            "radius" => 3000,
            "extensions" => "base",
            "location" => $coordinat
        );
        $res = $this->curlHelper->get($requestUrl, $requestParams);
        if ($res === false) {
            return false;
        }
        if ($res['status'] == 0 ) {
            if ($res['infocode'] == "10003") {
                self::$excludeConfig[] = $config;
            }
            LogUntil::info($res['info']."---".json_encode($config));
            return false;
        }
        return $res["regeocode"];
    }

    /* 计算驾车距离
     * @param $originCoordinat
     * @param $destinationCoordinat
     * @return int
     * 单位:米
     */
    public  function computeInstance($originCoordinat, $destinationCoordinat)
    {
        if (empty($originCoordinat) || empty($destinationCoordinat)) {
            return false;
        }
        $requestUrl = "http://restapi.amap.com/v3/direction/driving";
        $config = $this->getConfig();
        $requestParams = array(
            "key" => $config["key"],
            "output" => "JSON",
            "origin" => $originCoordinat,
            "destination" => $destinationCoordinat
        );
        $res = $this->curlHelper->get($requestUrl, $requestParams);
        if ($res === false) {
            return false;
        }
        if ($res['status'] == 0 ) {
            if ($res['infocode'] == "10003") {
                self::$excludeConfig[] = $config;
            }
            LogUntil::info($res['info']."---".json_encode($config));
            return false;
        }
        return $res['route']['paths'][0]['distance'];
    }

    //行政区域查询
    public function getAdministrativeRegions($keywords="", $subdistrict=1)
    {
        $requestUrl = "http://restapi.amap.com/v3/config/district";
        $config = $this->getConfig();
        $requestParams = array(
            "key" => $config["key"],
            "output" => "JSON",
            "subdistrict" => $subdistrict,
            'page' => 1,
            'offset' => 200
        );
        if (!empty($keywords)) {
            $requestParams["keywords"] = $keywords;
        }
        $res = $this->curlHelper->get($requestUrl, $requestParams);
        if ($res === false) {
            return false;
        }
        if ($res['status'] == 0 ) {
            if ($res['infocode'] == "10003") {
                self::$excludeConfig[] = $config;
            }
            LogUntil::info($res['info']."---".json_encode($config));
            return false;
        }
        return $res['districts'];
    }

    /**
     * 其它地图坐标转换
     * @param $coordinat
     * @return bool
     */
    public function  Transform($coordinatArr)
    {
        if (empty($coordinatArr)) {
            return false;
        }
        $requestUrl = "http://restapi.amap.com/v3/assistant/coordinate/convert";
        $config = $this->getConfig();
        $requestParams = array(
            "key" => $config["key"],
            "output" => "JSON",
            "coordsys" => "baidu",
            "locations" => implode('|', $coordinatArr)
        );
        $res = $this->curlHelper->get($requestUrl, $requestParams);
        if ($res === false) {
            return false;
        }
        if ($res['status'] == 0 ) {
            if ($res['infocode'] == "10003") {
                self::$excludeConfig[] = $config;
            }
            LogUntil::info($res['info']."---".json_encode($config));
            return false;
        }
        return $res["locations"];
    }

    /**
     * @param $longitude
     * 经度
     * @param $latitude
     * 维度
     * @param $range
     * 范围,单位:千米
     */
    public function Search($longitude, $latitude, $range)  {

        if (empty($longitude) || empty($latitude) || empty($range)) {
            return false;
        }

        $dlng = 2 * asin(sin($range/(2*self::EARTH_RADIUS))/cos($latitude*M_PI/180));
        $dlng = $dlng * 180 / M_PI;

        $dlat = $range / self::EARTH_RADIUS;
        $dlat = $dlat * 180 / M_PI;

        //外接四边形的点
        $left_top = array($longitude + $dlat, $latitude - $dlng);
        $right_top = array($longitude + $dlat, $latitude + $dlng);
        $left_bottom = array($longitude - $dlat, $latitude - $dlng);
        $right_bottom = array($longitude - $dlat, $latitude + $dlng);

        $dlat = abs($dlat);
        $dlng =abs($dlng);

        $minlat =$latitude - $dlat;
        $maxlat  = $latitude + $dlat;
        $minlng  = $longitude - $dlng;
        $maxlng  = $longitude + $dlng;

        return compact($minlat, $maxlat, $minlng, $maxlng);
    }
}

?>
