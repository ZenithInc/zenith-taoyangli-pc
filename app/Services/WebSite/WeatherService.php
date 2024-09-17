<?php
/**
 * Created by PhpStorm.
 * User: 12258
 * Date: 2021/7/19
 * Time: 17:15
 */

namespace App\Services\WebSite;

use App\Services\BaseService;
use Hyperf\Di\Annotation\Inject;
use Psr\SimpleCache\CacheInterface;

/**
 * @property \App\Repositories\CityWeatherCodeRepository $cityWeatherCodeRepository
 */
class WeatherService extends BaseService
{
    /**
     * @Inject()
     * @var CacheInterface
     */
    protected CacheInterface $cache;

    /**
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getWeather()
    {
        //通过城市名称获取城市天气编码
        $cityWeatherCodeWhere = [
            ['id', '=', 1008],
        ];
        $cityWeatherCodeRow = $this->cityWeatherCodeRepository->findOneWhere($cityWeatherCodeWhere, ['*']);
        if (empty($cityWeatherCodeWhere)) {
            return $this->baseFailed('未查询到该城市信息');
        }

        $day = date('Y-m-d');
        //        $hour = intval(date('H'));
        //        if($hour < 12){
        //            $key = 'weather_'.$day.' 12';
        //        }else{
        //            $key = 'weather_'.$day.' 24';
        //        }
        //缓存保存12个小时
        $key = 'weather_'.$day;
        $weatherRow = $this->cache->get($key);
        if (empty($weatherRow)) {
            $returnInfo = $this->getWeatherByMoji($cityWeatherCodeRow);
            if ( !empty($returnInfo['status'])) {
                return $this->baseFailed('未查询到该城市天气情况');
            }
            $weatherRow = $returnInfo['data'];
            $weatherRow['date'] = date('Y-m-d H:i:s');
            $this->cache->set($key,$weatherRow,3600);
        }
        return $this->baseSucceed('获取成功', $weatherRow);
    }

    public function getWeatherByMoji($cityWeatherCodeRow)
    {
        $moji_cityid = $cityWeatherCodeRow['moji_cityid'];
        if (empty($moji_cityid)) {
            return array('status' => 3, 'msg' => '未查询到该城市天气');
        }
        $mojiApiS = array('aqi', 'forecast24hours', 'aqiforecast5days', 'forecast15days', 'condition', 'index', 'alert');
        $mojiWeatherInfo = array();
        //循环获取墨迹常用api接口，并整合数据
        foreach ($mojiApiS as $v) {
            $returninfo = $this->mojiWeather($moji_cityid, $v);
            if ( !empty($returninfo) && !empty($returninfo['status'])) {
                return $returninfo;
            }
            $mojiWeatherInfo[$v] = $returninfo;

        }
        if (is_array($mojiWeatherInfo['forecast24hours'])) {
            $hours24Arr = array_column($mojiWeatherInfo['forecast24hours']['hourly'], 'temp');
            $templow = min($hours24Arr);
            $temphigh = max($hours24Arr);
        }
        $mojiWeatherInfo['condition']['condition']['templow'] = $templow;
        $mojiWeatherInfo['condition']['condition']['temphigh'] = $temphigh;
        /***************/
        $apiName = 'moji';
        $weatherInfo = $mojiWeatherInfo;

        //格式化天气第方接口数据，方便不同接口的天气信息入库
        $commonWeater = $this->formatWeaterApiData($weatherInfo, $apiName);
        if (empty($commonWeater['data']['updatetime'])) {
            return array('status' => 3, 'msg' => '格式化天气错误');
        }
        return array('status' => 0, 'msg' => '获取天气信息成功', 'data' => $commonWeater['data']);
    }

    private function formatWeaterApiData($weatherInfo, $apiName)
    {
        $apiNames = array('hzws', 'moji');//支持的阿里第三方接口
        if ( !in_array($apiName, $apiNames)) {
            return array('status' => 1, 'msg' => '不支持的第三方天气接口');
        }
        $apiName = $apiName.'ToWeaterData';
        $apiWeaterData = $this->$apiName();
        $apiWeaterBase = $apiWeaterData['base'];
        $formatWeater = array();//格式化后的天气数据
        $formatWeater['city'] = $this->kToValue($apiWeaterBase['city'], $weatherInfo);
        $formatWeater['cityid'] = $this->kToValue($apiWeaterBase['cityid'], $weatherInfo);
        $formatWeater['citycode'] = $this->kToValue($apiWeaterBase['citycode'], $weatherInfo);
        $formatWeater['date'] = $this->kToValue($apiWeaterBase['date'], $weatherInfo);
        $formatWeater['week'] = $this->kToValue($apiWeaterBase['week'], $weatherInfo);
        $formatWeater['img'] = $this->kToValue($apiWeaterBase['img'], $weatherInfo);
        if ($formatWeater['img'] < 10) {
            if ($formatWeater['img'] == '') {
                $formatWeater['img'] = '00';
            } else {
                $formatWeater['img'] = '0'.$formatWeater['img'];
            }
        }
        $formatWeater['weather'] = $this->kToValue($apiWeaterBase['weather'], $weatherInfo);
        $formatWeater['temp'] = $this->kToValue($apiWeaterBase['temp'], $weatherInfo);
        $formatWeater['temphigh'] = $this->kToValue($apiWeaterBase['temphigh'], $weatherInfo);
        $formatWeater['templow'] = $this->kToValue($apiWeaterBase['templow'], $weatherInfo);
        $formatWeater['humidity'] = $this->kToValue($apiWeaterBase['humidity'], $weatherInfo);
        $formatWeater['pressure'] = $this->kToValue($apiWeaterBase['pressure'], $weatherInfo);
        $formatWeater['windspeed'] = $this->kToValue($apiWeaterBase['windspeed'], $weatherInfo);
        $formatWeater['winddirect'] = $this->kToValue($apiWeaterBase['winddirect'], $weatherInfo);
        $formatWeater['windpower'] = $this->kToValue($apiWeaterBase['windpower'], $weatherInfo);//风力等级
        if (mb_stripos($formatWeater['windpower'], '级') === false) {
            $formatWeater['windpower'] .= '级';
        }
        $formatWeater['updatetime'] = $this->kToValue($apiWeaterBase['updatetime'], $weatherInfo);

        //生活指数
        $apiIndex = array();//第三方api对应生活指数数组数据
        $apiIndexKey = $apiWeaterBase['index'];//第三方api对应生活指数键
        //            $apiIndex = $weatherInfo[$apiIndexKey];//第三方api对应生活指数值
        $apiIndex = $this->kToValue($apiIndexKey, $weatherInfo);//第三方api对应生活指数值
        if (is_array($apiIndex)) {
            foreach ($apiIndex as $k => $v) {
                $formatWeater['index'][$k]['iname'] = $v[$apiWeaterData['index']['iname']];
                $formatWeater['index'][$k]['ivalue'] = $v[$apiWeaterData['index']['ivalue']];
                $formatWeater['index'][$k]['detail'] = $v[$apiWeaterData['index']['detail']];
            }
        }
        //aqi值
        $apiAqi = array();//第三方api对应空气质量数组数据
        $apiAqiKey = $apiWeaterBase['aqi'];//第三方api对应空气质量键
        $apiAqi = $weatherInfo[$apiAqiKey];//第三方api对应空气质量值
        $formatWeater['aqi']['pm2_5'] = $this->kToValue($apiWeaterData['aqi']['pm2_5'], $apiAqi);
        $formatWeater['aqi']['aqi'] = $this->kToValue($apiWeaterData['aqi']['aqi'], $apiAqi);
        $formatWeater['aqi']['quality'] = $this->kToValue($apiWeaterData['aqi']['quality'], $apiAqi);
        $formatWeater['aqi']['timepoint'] = $this->kToValue($apiWeaterData['aqi']['timepoint'], $apiAqi);
        if (empty($formatWeater['aqi']['quality']) && is_numeric($formatWeater['aqi']['aqi'])) {//空气质量数值转换成等级
            $formatWeater['aqi']['quality'] = $this->aqiToQuality($formatWeater['aqi']['aqi']);
        }
        //daiy 未来几天天气情况
        $apiDaily = array();//第三方api对应未来几天天气情况数组数据
        $apiDailyKey = $apiWeaterBase['daily'];//第三方api对应未来几天天气情况键
        //            $apiDaily = $weatherInfo[$apiDailyKey];//第三方api对应未来几天天气情况值
        $apiDaily = $this->kToValue($apiDailyKey, $weatherInfo);//第三方api对应未来几天天气情况值
        foreach ($apiDaily as $k => $v) {
            $formatWeater['daily'][$k]['date'] = $this->kToValue($apiWeaterData['daily']['date'], $v);
            $formatWeater['daily'][$k]['week'] = $this->kToValue($apiWeaterData['daily']['week'], $v);
            $formatWeater['daily'][$k]['sunrise'] = $this->kToValue($apiWeaterData['daily']['sunrise'], $v);
            $formatWeater['daily'][$k]['sunset'] = $this->kToValue($apiWeaterData['daily']['sunset'], $v);
            $formatWeater['daily'][$k]['day']['weather'] = $this->kToValue($apiWeaterData['daily']['day_']['weather'], $v);
            $formatWeater['daily'][$k]['day']['templow'] = $this->kToValue($apiWeaterData['daily']['day_']['templow'], $v);
            $formatWeater['daily'][$k]['day']['img'] = $this->kToValue($apiWeaterData['daily']['day_']['img'], $v);
            if ($formatWeater['daily'][$k]['day']['img'] < 10) {
                if ($formatWeater['daily'][$k]['day']['img'] == '') {
                    $formatWeater['daily'][$k]['day']['img'] = '00';
                } else {
                    $formatWeater['daily'][$k]['day']['img'] = '0'.$formatWeater['daily'][$k]['day']['img'];
                }
            }
            $formatWeater['daily'][$k]['day']['winddirect'] = $this->kToValue($apiWeaterData['daily']['day_']['winddirect'], $v);
            $formatWeater['daily'][$k]['day']['windpower'] = $this->kToValue($apiWeaterData['daily']['day_']['windpower'], $v);
            $formatWeater['daily'][$k]['night']['weather'] = $this->kToValue($apiWeaterData['daily']['night_']['weather'], $v);
            $formatWeater['daily'][$k]['night']['templow'] = $this->kToValue($apiWeaterData['daily']['night_']['templow'], $v);
            $formatWeater['daily'][$k]['night']['img'] = $this->kToValue($apiWeaterData['daily']['night_']['img'], $v);
            if ($formatWeater['daily'][$k]['night']['img'] < 10) {
                if ($formatWeater['daily'][$k]['night']['img'] == '') {
                    $formatWeater['daily'][$k]['night']['img'] = '00';
                } else {
                    $formatWeater['daily'][$k]['night']['img'] = '0'.$formatWeater['daily'][$k]['night']['img'];
                }
            }
            $formatWeater['daily'][$k]['night']['winddirect'] = $this->kToValue($apiWeaterData['daily']['night_']['winddirect'], $v);
            $formatWeater['daily'][$k]['night']['windpower'] = $this->kToValue($apiWeaterData['daily']['night_']['windpower'], $v);
            if (mb_strpos($formatWeater['daily'][$k]['night']['windpower'], '级') === false) {
                $formatWeater['daily'][$k]['night']['windpower'] .= '级';
            }
        }
        $sunrise = $formatWeater['daily'][0]['sunrise'];
        $sunset = $formatWeater['daily'][0]['sunset'];
        $formatWeater['sunrise'] = is_numeric($sunrise) ? date('H:i:s', $sunrise) : date('H:i:s', strtotime($sunrise));
        $formatWeater['sunset'] = is_numeric($sunset) ? date('H:i:s', $sunset) : date('H:i:s', strtotime($sunset));
        //hourly 当天按小时天气情况
        $apiHourly = array();//第三方api对应未来几天天气情况数组数据
        $apiHourlyKey = $apiWeaterBase['hourly'];//第三方api对应未来几天天气情况键
        //            $apiHourly = $weatherInfo[$apiHourlyKey];//第三方api对应未来几天天气情况值
        $apiHourly = $this->kToValue($apiHourlyKey, $weatherInfo);//第三方api对应未来几天天气情况值
        foreach ($apiHourly as $k => $v) {
            $formatWeater['hourly'][$k]['time'] = $this->kToValue($apiWeaterData['hourly']['time'], $v);
            $formatWeater['hourly'][$k]['weather'] = $this->kToValue($apiWeaterData['hourly']['weather'], $v);
            $formatWeater['hourly'][$k]['temp'] = $this->kToValue($apiWeaterData['hourly']['temp'], $v);
            $formatWeater['hourly'][$k]['img'] = $this->kToValue($apiWeaterData['hourly']['img'], $v);
            if ($formatWeater['hourly'][$k]['img'] < 10) {
                if ($formatWeater['hourly'][$k]['img'] == '') {
                    $formatWeater['hourly'][$k]['img'] = '00';
                } else {
                    $formatWeater['hourly'][$k]['img'] = '0'.$formatWeater['hourly'][$k]['img'];
                }
            }
        }
        $alert = $apiWeaterBase['alert'];
        $apiAlert = $this->kToValue($alert, $weatherInfo);
        if ($apiAlert) {
            foreach ($apiAlert as $k => $v) {
                $formatWeater['alert'][$k]['content'] = $this->kToValue($apiWeaterData['alert']['content'], $v);
                $formatWeater['alert'][$k]['infoid'] = $this->kToValue($apiWeaterData['alert']['infoid'], $v);
                $formatWeater['alert'][$k]['land_defense_id'] = $this->kToValue($apiWeaterData['alert']['land_defense_id'], $v);
                $formatWeater['alert'][$k]['level'] = $this->kToValue($apiWeaterData['alert']['level'], $v);
                $formatWeater['alert'][$k]['name'] = $this->kToValue($apiWeaterData['alert']['name'], $v);
                $formatWeater['alert'][$k]['port_defense_id'] = $this->kToValue($apiWeaterData['alert']['port_defense_id'], $v);
                $formatWeater['alert'][$k]['pub_time'] = $this->kToValue($apiWeaterData['alert']['pub_time'], $v);
                $formatWeater['alert'][$k]['title'] = $this->kToValue($apiWeaterData['alert']['title'], $v);
                $formatWeater['alert'][$k]['type'] = $this->kToValue($apiWeaterData['alert']['type'], $v);
            }
        } else {
            $formatWeater['alert'] = [];
        }

        return array('status' => 0, 'msg' => '格式化成功', 'data' => $formatWeater);
    }

    private function hzwsToWeaterData()
    {
        return array(
            'base'   =>
                array(
                    'city'       => 'city',//城市 杭州
                    'cityid'     => 'cityid',//城市id
                    'citycode'   => 'citycode',//城市天气编码 101210101
                    'date'       => 'date', //2018-08-14
                    'week'       => 'week',  //星期二
                    'weather'    => 'weather',//基本天气，如多云
                    'temp'       => 'temp',//当前气温 35
                    'temphigh'   => 'temphigh',//最高气温  35
                    'templow'    => 'templow',//最低气温 26
                    'img'        => 'img',//天气图标id  1
                    'humidity'   => 'humidity',//湿度  64
                    'pressure'   => 'pressure',//气压  1001
                    'windspeed'  => 'windspeed',//风速 3.8
                    'winddirect' => 'winddirect',//风向 东北风
                    'windpower'  => 'windpower',//风力级数 2级
                    'updatetime' => 'updatetime',//数据更新时间  2018-08-14 14:00:00


                    'index'  => 'index',//生活指数，数组
                    'aqi'    => 'aqi',//AQI指数，数组
                    'daily'  => 'daily',//未来几天按天的天气情况 ，数组
                    'hourly' => 'hourly',//按小时，数组
                ),
            'index'  => array(//对应index的生活指数
                              "iname"  => "iname",//指数名称 空调指数
                              "ivalue" => "ivalue",//指数值  部分时间开启
                              "detail" => "detail",  //指数详情 您将感到些燥热，建议您在适当的时候开启制冷空调来降低温度，以免中暑。
            ),
            'aqi'    => array(//AQI指数 对应基本信息里的aqi
                              "pm2_5"     => "pm2_5", //pm2.5 16
                              "aqi"       => 'aqi',//
                              "quality"   => "quality", //空气指数类别
                              "timepoint" => "timepoint",//AQI更新时间
            ),
            'daily'  => array(//未来几天按天展示
                              "date"    => "date", //日期 2018-08-14
                              "week"    => "week",//星期 星期二
                              "sunrise" => "sunrise",//日出时间 05:24
                              "sunset"  => "sunset",//日落时间 18:43
                              'night'   => 'night',//下面的晚上////////
                              'night_'  => array(//晚上
                                                 "weather"    => "weather",//多云
                                                 "templow"    => "templow",//35
                                                 "img"        => "img",//1
                                                 "winddirect" => "winddirect",//东风
                                                 "windpower"  => "windpower",//3-5级
                              ),
                              'day'     => 'day',//下面的白天///////
                              'day_'    => array(//白天
                                                 "weather"    => "weather",//多云
                                                 "temphigh"   => "temphigh",//35
                                                 "img"        => "img",//1
                                                 "winddirect" => "winddirect",//东风
                                                 "windpower"  => "windpower",//3-5级
                              ),
            ),
            'hourly' => array(
                "time"    => "time", //14:00
                "weather" => "weather",//多云
                "temp"    => "temp",//35
                "img"     => "img",//1
            ),

        );
    }

    /*
    * 墨迹天气接口返回的数据对应的标准的天气数据格式
    * 键是标准的命名方标式，值是第三方的
    */
    private function mojiToWeaterData()
    {
        return array(
            'base'   =>
                array(
                    'city'       => array('condition', 'city', 'name'),//城市 杭州
                    'cityid'     => array('condition', 'city', 'cityId'),//城市id
                    'citycode'   => '',//城市天气编码 101210101
                    'date'       => array('condition', 'condition', 'updatetime'), //2018-08-14 更新日期，不能为空，目前一天一更新以此为依据
                    'week'       => '',  //星期二
                    'weather'    => array('condition', 'condition', 'condition'),//基本天气，如多云
                    'temp'       => array('condition', 'condition', 'temp'),//当前气温 35
                    'temphigh'   => array('condition', 'condition', 'temphigh'),//最高气温  35
                    'templow'    => array('condition', 'condition', 'templow'),//最低气温 26
                    'img'        => array('condition', 'condition', 'icon'),//天气图标id  1
                    'humidity'   => array('condition', 'condition', 'humidity'),//湿度  64
                    'pressure'   => array('condition', 'condition', 'pressure'),//气压  1001
                    'windspeed'  => array('condition', 'condition', 'windSpeed'),//风速 3.8
                    'winddirect' => array('condition', 'condition', 'windDir'),//风向 东北风
                    'windpower'  => array('condition', 'condition', 'windLevel'),//风力级数 2级
                    'updatetime' => array('condition', 'condition', 'updatetime'),//数据更新时间  2018-08-14 14:00:00


                    //                'index'=>'index',//生活指数，数组
                    'index'      => array('index', 'liveIndex', date('Y-m-d', time())),//生活指数，数组
                    'alert'      => array('alert', 'alert'),
                    'aqi'        => 'aqi',//AQI指数，数组
                    'daily'      => array('forecast15days', 'forecast'),//未来几天按天的天气情况 ，数组
                    'hourly'     => array('forecast24hours', 'hourly'),//按小时，数组
                ),
            'index'  => array(//对应index的生活指数
                              "iname"  => "name",//指数名称 空调指数
                              "ivalue" => "status",//指数值  部分时间开启
                              "detail" => "desc",  //指数详情 您将感到些燥热，建议您在适当的时候开启制冷空调来降低温度，以免中暑。
            ),
            'alert'  => array(
                'content'         => 'content',//焦作市气象台2021年07月21日14时20分发布暴雨红色预警信号：预计未来3小时焦作中东部地区将出现100毫米以上的降水,可能造成较大影响，请注意防范。焦作市防汛抗旱指挥部办公室提醒：注意做好防灾减灾和安全生产工作。（预警信息来源：国家预警信息发布中心）
                'infoid'          => 'infoid',//81
                'land_defense_id' => 'land_defense_id',//17,9,12
                'level'           => 'level',//红色
                'name'            => 'name',//暴雨
                'port_defense_id' => 'port_defense_id',//17,9,12
                'pub_time'        => 'pub_time',//2021-07-21 14:20:50
                'title'           => 'title',//河南省焦作市气象台发布暴雨红色预警
                'type'            => 'type',//暴雨红色
            ),
            'aqi'    => array(//AQI指数 对应基本信息里的aqi
                              "pm2_5"     => array('aqi', 'pm25'), //pm2.5 16
                              "aqi"       => array('aqi', 'value'),//
                              "quality"   => '', //空气指数类别
                              "timepoint" => array('aqi', 'pubtime'),//AQI更新时间
            ),
            'daily'  => array(//未来几天按天展示
                              "date"    => "predictDate", //日期 2018-08-14
                              "week"    => "",//星期 星期二
                              "sunrise" => "sunrise",//日出时间 05:24
                              "sunset"  => "sunset",//日落时间 18:43
                              'night'   => 'night',//下面的晚上////////
                              'night_'  => array(//晚上
                                                 "weather"    => array('conditionNight'),//多云
                                                 "templow"    => array('tempNight'),//35
                                                 "img"        => array('conditionIdNight'),//1
                                                 "winddirect" => array('windDirNight'),//东风
                                                 "windpower"  => array('windLevelNight'),//3-5级
                              ),
                              'day'     => 'day',//下面的白天///////
                              'day_'    => array(//白天
                                                 "weather"    => array('conditionDay'),//多云
                                                 "templow"    => array('tempDay'),//35
                                                 "img"        => array('conditionIdDay'),//1
                                                 "winddirect" => array('windDirDay'),//东风
                                                 "windpower"  => array('windSpeedDay'),//3-5级
                              ),
            ),
            'hourly' => array(
                "time"    => array('hour'), //14:00
                "weather" => array('condition'),//多云
                "temp"    => array('temp'),//35
                "img"     => array('iconDay'),//1
            ),

        );
    }

    public function mojiWeather($cityid, $PartApiName)
    {
        $alyunapi = config('weather.moji');
        $method = "POST";
        $appcode = $alyunapi['appcode'];
        $headers = array();
        array_push($headers, "Authorization:APPCODE ".$appcode);
        array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");
        $host = "http://aliv18.data.moji.com";//墨迹天气（专业版气象服务（cityid）-墨迹天气）
        $path = "/whapi/json/alicityweather/".$PartApiName;
        $querys = "cityId=".$cityid;
        $url = $host.$path;
        $bodys = $querys;
        $returnCurl = curlHttpOld($url, $bodys, $method, $headers);
        if ($returnCurl['code'] != '200' && empty($returnCurl['response'])) {
            return array('status' => 2, 'msg' => '墨迹天气接口:'.$PartApiName.'访问失败；code:'.$returnCurl['code'].$returnCurl['error']);
        }
        $row = json_decode($returnCurl['response'], true);
        if ($row['code'] != 0 || empty($row['data'])) {
            $msg = json_encode($row, JSON_UNESCAPED_UNICODE);
            return array('status' => 3, 'msg' => '天气获取失败:'.$msg);
        }
        $row = $row['data'];
        return $row;
    }

    /*
        * 空气质量数值转换等级
        */
    private function aqiToQuality($aqi)
    {
        if ($aqi > 0 && $aqi <= 50) {
            return '优';
        }
        if ($aqi <= 100) {
            return '良';
        }
        if ($aqi <= 150) {
            return '轻度污染';
        }
        if ($aqi <= 200) {
            return '中度污染';
        }
        if ($aqi <= 300) {
            return '重度污染';
        }
        if ($aqi <= 500) {
            return '严重污染';
        }
        if ($aqi <= 100000) {
            return '爆表';
        }
        return '';
    }

    function kToValue($k, $arr)
    {
        if (is_string($k)) {
            if (empty($arr[$k])) {
                return '';
            }
            return $arr[$k];
        }
        foreach ($k as $v) {
            if (empty($arr[$v])) {
                return '';
            }
            $return = $arr[$v];
            unset($arr);
            $arr = $return;
        }
        return $arr;
    }

    public function insertCode()
    {
        //        $cityData = json_decode($cityInfo,true);
        //        foreach ($cityData as $k=>$v){
        //            $data = [];
        //            if(!isset($v['name'])){
        //                continue;
        //            }
        //            $data['weather_code'] = $v['Fweathercn']??0;
        //            $data['name'] = $v['name'];
        //            $data['moji_cityid'] = $v['Fid'];
        //            $data['province_name'] = $v['Fprovince_cn']??'';
        //            $data['dateline'] = time();
        //            $this->cityWeatherCode->create($data);
        //        }
    }


}
