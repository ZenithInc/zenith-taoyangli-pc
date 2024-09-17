<?php
/**
 * Created by PhpStorm.
 * User: 12258
 * Date: 2022/10/24
 * Time: 15:52
 */

namespace App\Controller\WebSite;
use App\Controller\BaseController;
use App\Services\WebSite\SearchService;
use Hyperf\Di\Annotation\Inject;

class SearchController extends BaseController
{
    /**
     * @Inject()
     * @var SearchService
     */
    public $searchService;

    /**
     * 搜索
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getList()
    {
        $requestData = $this->request->all();
        $result = $this->searchService->getList($requestData);
        if ($result['status'] !== 'success') {
            return $this->failed($result['message'],$result['data']);
        }else{
            return $this->success($result['data'],$result['message']);
        }
    }
}
