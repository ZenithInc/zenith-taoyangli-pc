<?php
/**
 * Created by PhpStorm.
 * User: 12258
 * Date: 2022/10/24
 * Time: 15:52
 */

namespace App\Controller\WebSite;
use App\Controller\BaseController;
use App\Services\WebSite\SetService;
use Hyperf\Di\Annotation\Inject;

class SetController extends BaseController
{
    /**
     * @Inject()
     * @var SetService
     */
    public $setService;

    /**
     * 编辑
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function edit()
    {
        $requestData = $this->request->all();
        $result = $this->setService->edit($requestData);
        if ($result['status'] !== 'success') {
            return $this->failed($result['message'],$result['data']);
        }else{
            return $this->success($result['data'],$result['message']);
        }
    }
    /**
     * 详情
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function detail()
    {
        $result = $this->setService->detail();
        if ($result['status'] !== 'success') {
            return $this->failed($result['message'],$result['data']);
        }else{
            return $this->success($result['data'],$result['message']);
        }
    }

    /**
     * 多语言设置
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function languageSet()
    {
        $requestData = $this->request->all();
        $result = $this->setService->languageSet($requestData);
        if ($result['status'] !== 'success') {
            return $this->failed($result['message'],$result['data']);
        }else{
            return $this->success($result['data'],$result['message']);
        }
    }
}
