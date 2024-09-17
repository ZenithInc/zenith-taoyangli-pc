<?php

namespace App\Services\WebSite;

use App\Services\BaseService;
use Hyperf\Di\Annotation\Inject;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use App\Handlers\Integration\PassOwner;

class ScenicService extends BaseService
{
   
    /**
     * @Inject()
     * @var CacheInterface
     */
    protected $cache;

    /**
     * 今日入园
     *
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function todayVerifyNum()
    {
        $timeStart = strtotime(date('Y-m-d'));
        $key = 'scenic_statistics_'.$timeStart;
        $cacheData = $this->cache->get($key);
        if(empty($cacheData)){
            $passObj = new PassOwner();
            $res = $passObj->getDashboardDetailData();
            if(empty($res) || $res['status'] !== 'success'){
                return $this->baseSucceed('获取成功',[
                    'verify_num' => 0,
                    'hear_num' => 0,
                    'saturation_num' => 0,
                ]);
            }else{
                $data = $res['data']['result']['list'];
                $verifyNum = !empty($data[0]['button_num']) ? $data[0]['button_num'] : 0;
                $hearNum = !empty($data[3]['button_num']) ? $data[3]['button_num'] : 0;
                $saturationNum = !empty($data[4]['button_num']) ? $data[4]['button_num'] : 0;
                $cacheData = [
                    'verify_num' => $verifyNum,
                    'hear_num' => $hearNum,
                    'saturation_num' => $saturationNum,
                ];
                $this->cache->set($key,$cacheData,180);
            }
        }
        return $this->baseSucceed('获取成功',$cacheData);
    }
}
