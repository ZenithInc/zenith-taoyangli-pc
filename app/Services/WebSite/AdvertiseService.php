<?php

namespace App\Services\WebSite;

use App\Services\BaseService;
use Hyperf\DbConnection\Db;

/**
 * @property \App\Repositories\AdvertiseRepository $advertiseRepository
 * @property \App\Repositories\AdvertisePositionRepository $advertisePositionRepository
 */
class AdvertiseService extends BaseService
{
    /**
     * 广告详情
     *
     * @param $param
     *
     * @return array
     */
    public function detail($param)
    {
        $id = $param['id'] ?? 0;
        $row = $this->advertiseRepository->getInfo($id);
        $time = time();
        if (empty($row) || $row['is_deleted'] == 1 || $row['is_enabled'] != 1 || $row['start'] > $time || $row['end'] < $time) {
            return $this->baseFailed('广告信息不存在');
        }
        return $this->baseSucceed('获取成功', $row);
    }

    /**
     * 广告列表
     *
     * @param $param
     *
     * @return array
     */
    public function getList($param)
    {
        $positionTag = $param['position_tag'] ?? '';
        $page = $param['page'] ?? '';
        $limit = $param['limit'] ?? '';
        $time = time();
        $position = $this->advertisePositionRepository->findOneWhere([
            ['tag','=',$positionTag],
            ['is_deleted','=',0]
        ],['*'],'id desc');
        $where = [
            ['is_deleted','=',0],
            ['is_enabled','=',1],
            ['position_id','=',$position['id']??0],
            ['start','<=',$time],
            ['end','>=',$time],
        ];
        $advertiseAll = $this->advertiseRepository->getList($where,$page,$limit,'iforder desc,sort asc,sort_uptime desc,id desc',['id','pic','link','sort','sort_uptime',Db::raw('if (sort>0,1,0) AS iforder')]);
        return $this->baseSucceed('获取成功',$advertiseAll);
    }
}
