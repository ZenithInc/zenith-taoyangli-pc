<?php
/**
 * Created by PhpStorm.
 * User: 12258
 * Date: 2022/10/24
 * Time: 15:52
 */

namespace App\Controller\WebSite;
use App\Controller\BaseController;
use App\Services\WebSite\SpotsService;
use Hyperf\Di\Annotation\Inject;

class SpotsController extends BaseController
{
    /**
     * @Inject()
     * @var SpotsService
     */
    public $spotsService;

    /**
     * 编辑
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function edit()
    {
        $requestData = $this->request->all();
        $result = $this->spotsService->edit($requestData,$this->userValidate());
        if ($result['status'] !== 'success') {
            return $this->failed($result['message'],$result['data']);
        }else{
            return $this->success($result['data'],$result['message']);
        }
    }
    /**
     * 列表
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getList()
    {
        $requestData = $this->request->all();
        $result = $this->spotsService->getList($requestData);
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
        $requestData = $this->request->all();
        $result = $this->spotsService->detail($requestData);
        if ($result['status'] !== 'success') {
            return $this->failed($result['message'],$result['data']);
        }else{
            return $this->success($result['data'],$result['message']);
        }
    }
    /**
     * 启用禁用
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function enabled()
    {
        $requestData = $this->request->all();
        $result = $this->spotsService->enabled($requestData, $this->userValidate());
        if ($result['status'] !== 'success') {
            return $this->failed($result['message'],$result['data']);
        }else{
            return $this->success($result['data'],$result['message']);
        }
    }
    /**
     * 删除
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function del()
    {
        $requestData = $this->request->all();
        $result = $this->spotsService->del($requestData, $this->userValidate());
        if ($result['status'] !== 'success') {
            return $this->failed($result['message'],$result['data']);
        }else{
            return $this->success($result['data'],$result['message']);
        }
    }
    /**
     * 修改排序
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sort()
    {
        $requestData = $this->request->all();
        $result = $this->spotsService->sort($requestData, $this->userValidate());
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
        $result = $this->spotsService->languageSet($this->userValidate(), $requestData);
        if ($result['status'] !== 'success') {
            return $this->failed($result['message'],$result['data']);
        }else{
            return $this->success($result['data'],$result['message']);
        }
    }
}
