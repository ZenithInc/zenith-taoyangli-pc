<?php

namespace App\Services\WebSite;

use App\Services\BaseService;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

/**
 * @property \App\Repositories\WebsiteInformationRepository $websiteInformationRepository
 */
class InformationService extends BaseService
{
    /**
     * 详情
     *
     * @param $param
     *
     * @return array
     */
    public function detail($param)
    {
        $id = $param['id'] ?? 0;
        $row = $this->websiteInformationRepository->getInfo($id);
        if (empty($row) || $row['is_deleted'] == 1) {
            return $this->baseFailed('不存在或已被删除');
        }
        $row['push_time'] = !empty($row['push_time']) ? date('Y-m-d', $row['push_time']) : '';
        return $this->baseSucceed('获取成功', $row);
    }

    /**
     * 资讯列表
     *
     * @param $param
     *
     * @return array
     */
    public function getList($param)
    {
        $title = $param['title'] ?? '';
        $type = $param['type'] ?? 0;
        $page = $param['page'] ?? '';
        $limit = $param['limit'] ?? '';
        $where = [
            ['is_deleted', '=', 0],
            ['is_enabled', '=', 1],
        ];
        if ($type) {
            $where[] = ['type', '=', $type];
        }
        if ($title) {
            $where[] = ['title', 'locate', $title];
        }
        $informationAll = $this->websiteInformationRepository->getList($where, $page, $limit, 'iforder desc,sort asc,sort_uptime desc,push_time desc,id desc', ['id', 'title', 'title_en', 'title_jap', 'title_korea', 'pic', 'type', 'push_time', 'link', 'sort', 'is_enabled', 'dateline', 'uptime', 'content', Db::raw('if (sort>0,1,0) AS iforder')]);
        if ( !empty($informationAll['list'])) {
            foreach ($informationAll['list'] as &$value) {
                $value['push_time'] = date('Y-m-d', $value['push_time']);
                $value['uptime'] = date('Y-m-d H:i:s', $value['uptime']);
                $value['dateline'] = date('Y-m-d H:i:s', $value['dateline']);
                $value['content'] = strip_tags($value['content']);
            }
        }
        return $this->baseSucceed('获取成功', $informationAll);
    }
}
