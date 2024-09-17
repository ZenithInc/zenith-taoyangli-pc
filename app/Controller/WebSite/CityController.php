<?php
/**
 * 城市
 */

namespace App\Controller\WebSite;

namespace App\Controller\WebSite;
use App\Controller\BaseController;
use App\Services\WebSite\WeatherService;
use Hyperf\Di\Annotation\Inject;

class CityController extends BaseController
{
    /**
     * @Inject()
     * @var WeatherService
     */
    public $weatherService;

    /**
     * 天气
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getWeather()
    {
        $result = $this->weatherService->getWeather();
        if (empty($result['status']) || $result['status'] !== 'success') {
            return $this->failed($result['message'], $result['data']);
        } else {
            return $this->success($result['data'], $result['message']);
        }
    }
}

