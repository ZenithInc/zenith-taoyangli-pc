<?php

namespace App\JsonRpc;

use Hyperf\RpcClient\AbstractServiceClient;

class ParkApiRpcService extends AbstractServiceClient
{
    /**
     * 定义对应服务提供者的服务名称
     */
    protected $serviceName = 'ParkApiRpcService';

    /**
     * 定义对应服务提供者的服务协议
     */
    protected $protocol = 'jsonrpc';

    /**
     * @param $param
     *
     * @return mixed
     */
    public function getPaymentInfo($param)
    {
        return $this->__request(__FUNCTION__, compact('param'));
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    public function parkInfo($param)
    {
        return $this->__request(__FUNCTION__, compact('param'));
    }

    /**
     * @param $param
     *
     * @return mixed
     */
    public function freeSpace($param)
    {
        return $this->__request(__FUNCTION__, compact('param'));
    }
}