<?php

namespace App\Services\WebSite;

use App\Services\BaseService;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

/**
 * @property \App\Repositories\WebsiteNoticeRepository $websiteNoticeRepository
 */
class NoticeService extends BaseService
{
    /**
     * 公告详情
     *
     * @param $param
     *
     * @return array
     */
    public function detail($param)
    {
        $id = $param['id'] ?? 0;
        $row = $this->websiteNoticeRepository->getInfo($id);
        if (empty($row) || $row['is_deleted'] == 1) {
            return $this->baseFailed('不存在或已被删除');
        }
        $row['start'] = !empty($row['start']) ? date('Y-m-d H:i:s', $row['start']) : '';
        $row['end'] = !empty($row['end']) ? date('Y-m-d H:i:s', $row['end']) : '';
        return $this->baseSucceed('获取成功', $row);
    }

    /**
     * 公告列表
     *
     * @param $param
     *
     * @return array
     */
    public function getList($param)
    {
        $title = $param['title'] ?? '';
        $page = $param['page'] ?? '';
        $limit = $param['limit'] ?? '';
        $time = time();
        $where = [
            ['is_deleted', '=', 0],
            ['is_enabled', '=', 1],
            ['start', '<=', $time],
            ['end', '>=', $time],
        ];
        if ($title) {
            $where[] = ['title', 'locate', $title];
        }
        $noticeAll = $this->websiteNoticeRepository->getList($where, $page, $limit, 'iforder desc,sort asc,sort_uptime desc,id desc', ['id', 'title', 'title_en', 'title_jap', 'title_korea', 'link', 'dateline', 'sort', 'sort_uptime', 'content', Db::raw('if (sort>0,1,0) AS iforder')]);
        if ( !empty($noticeAll['list'])) {
            foreach ($noticeAll['list'] as &$value) {
                $value['dateline'] = date('Y-m-d H:i:s', $value['dateline']);
                $value['start'] = !empty($value['start']) ? date('Y-m-d H:i:s', $value['start']) : '';
                $value['end'] = !empty($value['end']) ? date('Y-m-d H:i:s', $value['end']) : '';
            }
        }
        return $this->baseSucceed('获取成功', $noticeAll);
    }
}
