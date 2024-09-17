<?php

namespace App\Controller\WebSite;

use App\Controller\BaseController;
use App\Services\WebSite\ScenicService;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\InvalidArgumentException;

class ScenicController extends BaseController
{
    /**
     * @Inject()
     * @var ScenicService
     */
    public $scenicService;

    /**
     * 编辑
     *
     * @return ResponseInterface
     * @throws InvalidArgumentException
     */
    public function todayVerifyNum()
    {
        $result = $this->scenicService->todayVerifyNum();
        if (empty($result['status']) || $result['status'] !== 'success') {
            return $this->failed($result['message'] ?? '', $result['data'] ?? 600);
        } else {
            return $this->success($result['data'] ?? [], $result['message'] ?? '');
        }
    }
}
