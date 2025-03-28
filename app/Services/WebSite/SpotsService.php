<?php

namespace App\Services\WebSite;
use App\Services\BaseService;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

/**
 * @property \App\Repositories\WebsiteSpotsRepository $websiteSpotsRepository
 */
class SpotsService extends BaseService
{
    /**
     * 景点详情
     * @param $param
     * @return array
     */
    public function detail($param)
    {
        $id = $param['id'] ?? 0;
        $row = $this->websiteSpotsRepository->getInfo($id);
        if(empty($row) || $row['is_deleted'] == 1){
            return $this->baseFailed('不存在或已被删除');
        }
        $row['push_time'] = !empty($row['push_time']) ? date('Y-m-d',$row['push_time']) : '';
        return $this->baseSucceed('获取成功',$row);
    }
    /**
     * 景点列表
     * @param $param
     * @return array
     */
    public function getList($param)
    {
        $title = $param['title'] ?? '';
        $page = $param['page'] ?? '';
        $limit = $param['limit'] ?? '';
        $recommend = $param['recommend'] ?? 0;
        $where = [
            ['is_deleted', '=', 0],
            ['is_enabled', '=', 1],
        ];
        if($recommend){
            $where[] = ['id','!=',$recommend];
        }
        if ($title) {
            $where[] = ['title', 'locate', $title];
        }
        $noticeAll = $this->websiteSpotsRepository->getList($where, $page, $limit, 'iforder desc,sort asc,sort_uptime desc,push_time desc,id desc', ['id','title','title_en','title_jap','title_korea','content','content_en','content_jap','content_korea','pic','push_time','dateline',Db::raw('if (sort>0,1,0) AS iforder')]);
        if (!empty($noticeAll['list'])) {
            foreach ($noticeAll['list'] as &$value) {
                $value['push_time'] = date('Y-m-d', $value['push_time']);
            }
        }
        return $this->baseSucceed('获取成功', $noticeAll);
    }
}
