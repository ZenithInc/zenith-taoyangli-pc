<?php

namespace App\Controller\WebSite;

use App\Controller\BaseController;
use App\Services\WebSite\AdvertiseService;
use Hyperf\Di\Annotation\Inject;

class AdvertiseController extends BaseController
{
    /**
     * @Inject()
     * @var AdvertiseService
     */
    public $advertiseService;

    /**
     * 广告列表
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getList()
    {
        $requestData = $this->request->all();
        $result = $this->advertiseService->getList($requestData);
        if ($result['status'] !== 'success') {
            return $this->failed($result['message'], $result['data']);
        } else {
            return $this->success($result['data'], $result['message']);
        }
    }

    /**
     * 广告详情
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function detail()
    {
        $requestData = $this->request->all();
        $result = $this->advertiseService->detail($requestData);
        if ($result['status'] !== 'success') {
            return $this->failed($result['message'], $result['data']);
        } else {
            return $this->success($result['data'], $result['message']);
        }
    }
}
