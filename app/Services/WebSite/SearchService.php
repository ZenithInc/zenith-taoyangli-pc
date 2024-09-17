<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Services\WebSite;

use App\Services\BaseService;
use Hyperf\DbConnection\Db;

/**
 * @property \App\Repositories\WebsiteArticleRepository $websiteArticleRepository
 * @property \App\Repositories\WebsiteNoticeRepository $websiteNoticeRepository
 * @property \App\Repositories\WebsiteInformationRepository $websiteInformationRepository
 * @property \App\Repositories\WebsiteSetRepository $websiteSetRepository
 * @property \App\Repositories\WebsiteSpotsRepository $websiteSpotsRepository
 */
class SearchService extends BaseService
{
    /**
     * 搜索
     * @return array
     */
    public function getList($param)
    {
        $page = $param['page'] ?? 1;
        $limit = $param['limit'] ?? 10;
        $keyword = $param['keyword'] ?? '';
        $type = $param['type'] ?? ''; // 1公告 2资讯 3景区服务 4文章 5景点
        if (! empty($type)) {
            if ($type == 1) {
                $list = $this->websiteNoticeRepository->getList([
                    ['is_deleted', '=', 0],
                    ['is_enabled', '=', 1],
                    ['or', [
                        ['title', 'locate', $keyword],
                        ['title_en', 'locate', $keyword],
                        ['title_jap', 'locate', $keyword],
                        ['title_korea', 'locate', $keyword],
                    ]],
                ], $page, $limit, 'id desc', ['id', 'title', 'link', 'title_en', 'title_jap', 'title_korea']);
            } elseif ($type == 2) {
                $list = $this->websiteInformationRepository->getList([
                    ['is_deleted', '=', 0],
                    ['is_enabled', '=', 1],
                    ['or', [
                        ['title', 'locate', $keyword],
                        ['title_en', 'locate', $keyword],
                        ['title_jap', 'locate', $keyword],
                        ['title_korea', 'locate', $keyword],
                    ]],
                ], $page, $limit, 'id desc', ['id', 'title', 'link', 'title_en', 'title_jap', 'title_korea']);
            } elseif ($type == 3) {
                $list = $this->websiteSetRepository->getList([
                    ['is_deleted', '=', 0],
                    ['or', [
                        ['name', '=', 'ticket'],
                        ['name', '=', 'problem'],
                        ['name', '=', 'tips'],
                        ['name', '=', 'vips'],]
                    ],
                    ['or', [
                        ['value', 'locate', $keyword],
                        ['value_en', 'locate', $keyword],
                        ['value_jap', 'locate', $keyword],
                        ['value_korea', 'locate', $keyword],
                    ]],
                ], $page, $limit, 'id desc', ['id', 'name', 'value', 'value_en', 'value_jap', 'value_korea']);
                if (! empty($list['list'])) {
                    foreach ($list['list'] as &$value) {
                        $value['title'] = strip_tags($value['value']);
                        $value['title_en'] = strip_tags($value['value_en']);
                        $value['title_jap'] = strip_tags($value['value_jap']);
                        $value['title_korea'] = strip_tags($value['value_korea']);
                    }
                }
            } elseif ($type == 4) {
                $list = $this->websiteArticleRepository->getList([
                    ['is_deleted', '=', 0],
                    ['or', [
                        ['title', 'locate', $keyword],
                        ['title_en', 'locate', $keyword],
                        ['title_jap', 'locate', $keyword],
                        ['title_korea', 'locate', $keyword],
                    ]],
                ], $page, $limit, 'id desc', ['id', Db::raw('type as c_type'),'link', 'title', 'title_en', 'title_jap', 'title_korea']);
            } elseif ($type == 5) {
                $list = $this->websiteSpotsRepository->getList([
                    ['is_deleted', '=', 0],
                    ['or', [
                        ['title', 'locate', $keyword],
                        ['title_en', 'locate', $keyword],
                        ['title_jap', 'locate', $keyword],
                        ['title_korea', 'locate', $keyword],
                    ]],
                ], $page, $limit, 'id desc', ['id', 'title', 'title_en', 'title_jap', 'title_korea']);
            }
        } else {
            // type为空查询所有
            $informationAll = $this->websiteInformationRepository->getList([
                ['is_deleted', '=', 0],
                ['is_enabled', '=', 1],
                ['or', [
                    ['title', 'locate', $keyword],
                    ['title_en', 'locate', $keyword],
                    ['title_jap', 'locate', $keyword],
                    ['title_korea', 'locate', $keyword],
                ]],
            ], '', '', 'id desc', ['id','link', 'title', 'title_en', 'title_jap', 'title_korea',Db::raw('2 as type')]);
            $noticeAll = $this->websiteNoticeRepository->getList([
                ['is_deleted', '=', 0],
                ['is_enabled', '=', 1],
                ['or', [
                    ['title', 'locate', $keyword],
                    ['title_en', 'locate', $keyword],
                    ['title_jap', 'locate', $keyword],
                    ['title_korea', 'locate', $keyword],
                ]],
            ], '', '', 'id desc', ['id','link', 'title', 'title_en', 'title_jap', 'title_korea',Db::raw('1 as type')]);
            $setAll = $this->websiteSetRepository->getList([
                ['is_deleted', '=', 0],
                ['or', [
                    ['name', '=', 'ticket'],
                    ['name', '=', 'problem'],
                    ['name', '=', 'tips'],
                    ['name', '=', 'vips'],]
                ],
                ['or', [
                    ['value', 'locate', $keyword],
                    ['value_en', 'locate', $keyword],
                    ['value_jap', 'locate', $keyword],
                    ['value_korea', 'locate', $keyword],
                ]],
            ], '', '', 'id desc', ['id', 'name', Db::raw('value as title'),Db::raw('value_en as title_en'), Db::raw('value_jap as title_jap'), Db::raw('value_korea as title_korea'), Db::raw('3 as type')]);
            if(!empty($setAll['list'])){
                foreach ($setAll['list'] as &$value) {
                    $value['title'] = strip_tags($value['title']);
                    $value['title_en'] = strip_tags($value['title_en']);
                    $value['title_jap'] = strip_tags($value['title_jap']);
                    $value['title_korea'] = strip_tags($value['title_korea']);
                }
            }
            $aritcleAll = $this->websiteArticleRepository->getList([
                ['is_deleted', '=', 0],
                ['or', [
                    ['title', 'locate', $keyword],
                    ['title_en', 'locate', $keyword],
                    ['title_jap', 'locate', $keyword],
                    ['title_korea', 'locate', $keyword],
                ]],
            ], '', '', 'id desc', ['id', Db::raw('type as c_type'),'link', 'title', 'title_en', 'title_jap', 'title_korea',Db::raw('4 as type')]);
            $spotsAll = $this->websiteSpotsRepository->getList([
                ['is_deleted', '=', 0],
                ['or', [
                    ['title', 'locate', $keyword],
                    ['title_en', 'locate', $keyword],
                    ['title_jap', 'locate', $keyword],
                    ['title_korea', 'locate', $keyword],
                ]],
            ], '', '', 'id desc', ['id', 'title', 'title_en', 'title_jap', 'title_korea',Db::raw('5 as type')]);
            $page = $page <= 0 ? 1 : $page;
            $pageSize = ceil(($page - 1) * $limit);
            $pageSize = intval($pageSize);
            $allList = array_merge($informationAll['list'], $noticeAll['list'], $aritcleAll['list'], $setAll['list'], $spotsAll['list']);
            $list['list'] = array_slice($allList, $pageSize, intval($limit));
            $list['countpage'] = ceil(count($allList) / $limit);
        }
        return $this->baseSucceed('获取成功', $list);
    }
}
