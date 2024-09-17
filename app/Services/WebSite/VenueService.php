<?php

namespace App\Services\WebSite;

use App\Services\BaseService;

/**
 * @property \App\Repositories\VenueRepository $venueRepository
 * @property \App\Repositories\VenueCategoryRepository $venueCategoryRepository
 */
class VenueService extends BaseService
{
    /**
     * 列表
     *
     * @param $param
     *
     * @return array
     */
    public function categoryList($param)
    {
        $name = $param['name'] ?? '';
        $page = $param['page'] ?? 1;
        $limit = $param['limit'] ?? 10;
        $is_admin = $param['is_admin'] ?? 0;
        $where = [
            ['is_enabled', '=', 1],
        ];
        if ($is_admin) {
            $where[] = [
                'OR', [
                    ['is_admin', '=', 1],
                    ['is_deleted', '=', 0],
                ],
            ];
        } else {
            $where[] = ['is_deleted', '=', 0];
        }
        if ($name) {
            $where[] = ['name', 'locate', $name];
        }
        $list = $this->venueCategoryRepository->getList($where, $page, $limit, 'is_admin DESC,id ASC', ['id', 'name', 'pic', 'pic_noclick', 'pic_click']);
        return $this->baseSucceed('获取成功', $list);
    }

    /**
     * 详情
     *
     * @param $param
     *
     * @return array
     */
    public function detail($param)
    {
        $id = intval($param['id'] ?? 0);
        $row = $this->venueRepository->getInfo($id);
        if (empty($row['id']) || $row['is_deleted'] != 0) {
            return $this->baseFailed('不存在或已被删除', 600);
        }
        $category = $this->venueCategoryRepository->getInfo($row['category_id'] ?? 0);
        $row['category_name'] = $category['name'] ?? '';
        $row['category_pic'] = $category['pic'] ?? '';
        $row['category_pic_click'] = $category['pic_click'] ?? '';
        $row['category_pic_noclick'] = $category['pic_noclick'] ?? '';
        return $this->baseSucceed('获取成功', $row);
    }

    /**
     * 列表
     *
     * @param $param
     *
     * @return array
     */
    public function getList($param)
    {
        $name = $param['name'] ?? '';
        $page = $param['page'] ?? 1;
        $limit = $param['limit'] ?? 10;
        $categoryId = $param['category_id'] ?? 0;
        $where = [
            ['is_enabled', '=', 1],
            ['is_deleted', '=', 0],
        ];
        if ($categoryId) {
            $where[] = ['category_id', '=', $categoryId];
        }
        if ($name) {
            $where[] = ['name', 'locate', $name];
        }
        $list = $this->venueRepository->getList($where, $page, $limit, '`id` DESC', ['id', 'name', 'dateline', 'address', 'lng', 'lat', 'category_id']);
        if ( !empty($list['list'])) {
            $categoryIds = array_column($list['list'], 'category_id');
            $categoryList = $this->venueCategoryRepository->getList([
                ['id', 'in', $categoryIds],
            ], '', '', '', ['id', 'name', 'pic', 'pic_noclick', 'pic_click']);
            $categoryAll = array_column($categoryList['list'] ?? [], null, 'id');
            foreach ($list['list'] as &$value) {
                $value['category_name'] = $categoryAll[$value['category_id']]['name'] ?? '';
                $value['category_pic'] = $categoryAll[$value['category_id']]['pic'] ?? '';
                $value['category_pic_click'] = $categoryAll[$value['category_id']]['pic_click'] ?? '';
                $value['category_pic_noclick'] = $categoryAll[$value['category_id']]['pic_noclick'] ?? '';
            }
        }
        return $this->baseSucceed('获取成功', $list);
    }
}
