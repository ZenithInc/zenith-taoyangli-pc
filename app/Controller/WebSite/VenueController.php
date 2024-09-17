<?php

namespace App\Controller\WebSite;

use App\Controller\BaseController;
use App\Services\WebSite\VenueService;
use App\Repositories\ParkingRepository;
use App\Repositories\ScenicSpotRepository;
use App\Repositories\ShopResourceRepository;
use App\Repositories\VenueCategoryRepository;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use App\JsonRpc\ParkApiRpcService;

class VenueController extends BaseController
{
    /**
     * @Inject()
     * @var VenueService
     */
    public $venueService;

    /**
     * @Inject
     * @var ParkingRepository
     */
    public $parkingRepository;

    /**
     * @Inject
     * @var ScenicSpotRepository
     */
    public $scenicSpotRepository;

    /**
     * @Inject
     * @var ShopResourceRepository
     */
    public $shopResourceRepository;

    /**
     * @Inject
     * @var VenueCategoryRepository
     */
    public $venueCategoryRepository;

    /**
     * @Inject()
     * @var ParkApiRpcService
     */
    public $parkApiRpcService;

    /**
     * 类型列表
     *
     * @return ResponseInterface
     */
    public function categoryList()
    {
        $requestData = $this->request->all();
        $requestData['is_admin'] = 1;
        $result = $this->venueService->categoryList($requestData);
        if (empty($result['status']) || $result['status'] !== 'success') {
            return $this->failed($result['message'] ?? '', $result['data'] ?? 600);
        } else {
            return $this->success($result['data'] ?? [], $result['message'] ?? '');
        }
    }

    /**
     * 列表
     *
     * @return ResponseInterface
     */
    public function getList()
    {
        $requestData = $this->request->all();

        $categoryId = $requestData['category_id'] ?? 0;
        if (empty($categoryId)) {
            return $this->failed('类型编号不能为空', 600);
        }
        $list = ['total' => 0, 'list' => []];
        $page = $requestData['page'] ?? 1;
        $limit = $requestData['limit'] ?? 10;
        $name = $requestData['name'] ?? '';
        $where = [
            ['is_deleted', '=', 0],
            ['is_enabled', '=', 1],
        ];
        if ($name) {
            $where[] = ['name', 'locate', $name];
        }
        $categoryList = $this->venueCategoryRepository->getList([
            ['is_admin', '=', 1],
            ['is_enabled', '=', 1],
        ], '', '', 'is_admin DESC,id asc', ['id', 'name', 'pic', 'pic_noclick', 'pic_click']);
        $adminCategoryIds = empty($categoryList['list']) ? [] : array_column($categoryList['list'], 'id');
        if ($adminCategoryIds && in_array($categoryId, $adminCategoryIds)) {
            if ($categoryId == 1001) {
                // 景点
                $options = [
                    'page'  => $page,
                    'limit' => $limit,
                    'order' => '`iforder` DESC,order_by ASC,id DESC',
                ];
                $fields = ['id', 'name', 'address', 'lat', 'lng', Db::raw('if (`order_by`>0,1,0) AS iforder')];
                $list = $this->scenicSpotRepository->getList($where, $fields, $options);
            } elseif ($categoryId == 1002) {
                // 美食
                $where[] = ['type', '=', 4];
                $order = '`iforder` DESC,sort ASC,id DESC';
                $fields = ['id', 'name', 'address', 'lat', 'lng', Db::raw('if (`sort`>0,1,0) AS iforder')];
                $list = $this->shopResourceRepository->getList($where, $page, $limit, $order, $fields);
            } elseif ($categoryId == 1003) {
                // 酒店
                $where[] = ['type', '=', 2];
                $order = '`iforder` DESC,sort ASC,id DESC';
                $fields = ['id', 'name', 'address', 'lat', 'lng', Db::raw('if (`sort`>0,1,0) AS iforder')];
                $list = $this->shopResourceRepository->getList($where, $page, $limit, $order, $fields);
            } elseif ($categoryId == 1004) {
                // 停车场
                $order = 'id DESC';
                $fields = ['id', 'name', 'address', 'lat', 'lng'];
                $list = $this->parkingRepository->getList($where, $page, $limit, $order, $fields);
            }
            if ( !empty($list['list'])) {
                $categoryAll = !empty($categoryList['list']) ? array_column($categoryList['list'], null, 'id') : [];
                foreach ($list['list'] as &$value) {
                    $value['category_id'] = $categoryId;
                    $value['category_name'] = $categoryAll[$categoryId]['name'] ?? '';
                    $value['category_pic'] = $categoryAll[$categoryId]['pic'] ?? '';
                    $value['category_pic_click'] = $categoryAll[$categoryId]['pic_click'] ?? '';
                    $value['category_pic_noclick'] = $categoryAll[$categoryId]['pic_noclick'] ?? '';
                    unset($value['iforder']);
                }
                unset($value);
            }
        } else {
            $result = $this->venueService->getList($requestData);
            if (empty($result['status']) || $result['status'] !== 'success') {
                return $this->failed($result['message'] ?? '', $result['data'] ?? 600);
            }
            $list = $result['data'] ?? [];
        }

        return $this->success($list, '成功');
    }

    /**
     * 详情
     *
     * @return ResponseInterface
     */
    public function detail()
    {
        $requestData = $this->request->all();
        $id = $requestData['id'] ?? 0;
        $categoryId = $requestData['category_id'] ?? 0;
        if (empty($categoryId)) {
            return $this->failed('类型编号不能为空', 600);
        }
        $totalNum = $freeSpaceNum = $free_charging_num = 0;

        $category = $this->venueCategoryRepository->getInfo($categoryId);
        if ( !empty($category['is_admin'])) {
            if ($categoryId == 1001) {
                // 景点
                $table = 'scenicSpotRepository';
            } elseif ($categoryId == 1002) {
                // 美食
                $table = 'shopResourceRepository';
            } elseif ($categoryId == 1003) {
                // 酒店
                $table = 'shopResourceRepository';
            } elseif ($categoryId == 1004) {
                // 停车场
                $table = 'parkingRepository';
                $return = $this->parkApiRpcService->freeSpace([
                    'source' => 1,
                    'parkId' => $id,
                ]);
                if ( !empty($return['status']) && $return['status'] == 'success') {
                    $totalNum = intval($return['data']['freeSpaceNum'] ?? 0);
                    $freeSpaceNum = intval($return['data']['freeSpaceNum'] ?? 0);
                }
            } else {
                $table = 'venueRepository';
            }
            $rowTmp = $this->{$table}->getInfo($id);
            if (empty($rowTmp['id']) || $rowTmp['is_deleted'] == 1) {
                return $this->failed('不存在或已被删除');
            }
            $rowTmp['category_name'] = $category['name'] ?? '';
            $rowTmp['category_pic'] = $category['pic'] ?? '';
            $rowTmp['category_pic_click'] = $category['pic_click'] ?? '';
            $rowTmp['category_pic_noclick'] = $category['pic_noclick'] ?? '';
        } else {
            $result = $this->venueService->detail($requestData);
            if (empty($result['status']) || $result['status'] !== 'success') {
                return $this->failed($result['message'] ?? '', $result['data'] ?? 600);
            }
            $rowTmp = $result['data'] ?? [];
        }
        $row = [
            'id'                   => $rowTmp['id'],
            'name'                 => $rowTmp['name'] ?? '',
            'category_id'          => $categoryId,
            'category_name'        => $rowTmp['category_name'],
            'category_pic'         => $rowTmp['category_pic'] ?? '',
            'category_pic_click'   => $rowTmp['category_pic_click'] ?? '',
            'category_pic_noclick' => $rowTmp['category_pic_noclick'] ?? '',
            'address'              => $rowTmp['address'] ?? '',
            'lng'                  => $rowTmp['lng'] ?? '',
            'lat'                  => $rowTmp['lat'] ?? '',
            'desc'                 => $rowTmp['desc'] ?? '',
            'phone'                => $rowTmp['phone'] ?? '',
            'voice_url'            => $rowTmp['voice_url'] ?? '',
            'slideshow_pics'       => empty($rowTmp['slideshow_pics']) ? ($rowTmp['pic'] ?? '') : $rowTmp['slideshow_pics'],
            'parking_info'         => $rowTmp['parking_info'] ?? '',
            'business_hours'       => $rowTmp['business_hours'] ?? '',
            'extend_field'         => [
                'total_space_num'   => $totalNum,
                'free_space_num'    => $freeSpaceNum,
                'free_charging_num' => $free_charging_num,
            ],
        ];
        return $this->success($row, '成功');
    }
}
