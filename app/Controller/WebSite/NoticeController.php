<?php
/**
 * Created by PhpStorm.
 * User: 12258
 * Date: 2022/10/24
 * Time: 15:52
 */

namespace App\Controller\WebSite;
use App\Controller\BaseController;
use App\Services\WebSite\NoticeService;
use Hyperf\Di\Annotation\Inject;

class NoticeController extends BaseController
{
    /**
     * @Inject()
     * @var NoticeService
     */
    public $noticeService;

    /**
     * 公告列表
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getList()
    {
        $requestData = $this->request->all();
        $result = $this->noticeService->getList($requestData);
        if ($result['status'] !== 'success') {
            return $this->failed($result['message'],$result['data']);
        }else{
            return $this->success($result['data'],$result['message']);
        }
    }
    /**
     * 公告详情
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function detail()
    {
        $requestData = $this->request->all();
        $result = $this->noticeService->detail($requestData);
        if ($result['status'] !== 'success') {
            return $this->failed($result['message'],$result['data']);
        }else{
            return $this->success($result['data'],$result['message']);
        }
    }

}
