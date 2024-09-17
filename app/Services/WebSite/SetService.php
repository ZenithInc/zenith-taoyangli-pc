<?php

namespace App\Services\WebSite;
use App\Services\BaseService;
use Hyperf\Di\Annotation\Inject;

/**
 * @property \App\Repositories\WebsiteSetRepository $websiteSetRepository
 */
class SetService extends BaseService
{
    /**
     * 详情
     * @param $param
     * @return array
     */
    public function detail()
    {
        $list = $this->websiteSetRepository->getList([
            ['is_deleted','=',0]
        ]);
        return $this->baseSucceed('获取成功',$list);
    }
}
