<?php
/**
 * pass中台
 */
namespace App\Handlers\Integration;

use App\Handlers\Base;

class PassOwner extends Base
{
    public $config;
    public function __construct()
    {
       $this->config = config('integration.passowner');
    }

    /**
     * 获取token
     * @return array
     */
    public function getToken()
    {
        $data = [
            'applicationid' => $this->config['applicationId'],
            'appkey' => $this->config['appKey'],
            'appsecret' => $this->config['appSecret'],
            'username' => $this->config['username'],
        ];
        $header = [
            'Content-Type' => 'application/json; charset=UTF-8',
        ];
        $res = $this->curlHttp($this->config['url'].'/api/appsheets/sheetapi/getUToken','POST','pass中台获取token',json_encode($data,JSON_UNESCAPED_UNICODE),$header);
        if($res['status'] !== 'success'){
            return $this->baseFailed(!empty($res['message']) ? $res['message'] : '接口请求失败');
        }
        $returnData = $res['data'] ?? [];
        if(empty($returnData['status']) || $returnData['status'] !== 'success'){
            return $this->baseFailed('token获取失败');
        }
        return $this->baseSucceed('token获取成功',$returnData['result']);
    }

    /**
     * 更新记录
     * @param $param
     * @return array
     */
    public function editFormRowById($param)
    {
        $data = [
            'applicationid' => $this->config['applicationId'],
            'formheaduuid' => $param['formheaduuid'],
            'rowid' => $param['external_log_id'],
            'fields' => $param['fields'],
        ];
        $tokenRes = $this->getToken();
        if(empty($tokenRes) || $tokenRes['status'] !== 'success'){
            return $tokenRes;
        }
        $token = $tokenRes['data']['token'];
        $header = [
            'Authorization' => 'Bearer '.$token
        ];
        $res = $this->curlHttp($this->config['url'].'/api/appsheets/sheetapi/editFormRowById','POST','pass中台更新记录详情',json_encode($data,JSON_UNESCAPED_UNICODE),$header);
        if($res['status'] !== 'success'){
            return $this->baseFailed(!empty($res['message']) ? $res['message'] : '接口请求失败');
        }
        $returnData = $res['data'] ?? [];
        if(empty($returnData['status']) || $returnData['status'] !== 'success'){
            return $this->baseFailed('操作失败');
        }
        return $this->baseSucceed('操作成功',$returnData);
    }

    /**
     * 新增记录
     * @param $param
     * @return array
     */
    public function addFormRow($param)
    {
        $data = [
            'applicationid' => $this->config['applicationId'],
            'formheaduuid' => $param['formheaduuid'],
            'fields' => $param['fields'],
        ];
        $tokenRes = $this->getToken();
        if(empty($tokenRes) || $tokenRes['status'] !== 'success'){
            return $tokenRes;
        }
        $token = $tokenRes['data']['token'];
        $header = [
            'Authorization' => 'Bearer '.$token
        ];
        $res = $this->curlHttp($this->config['url'].'/api/appsheets/sheetapi/addFormRow','POST','pass中台新增记录详情',json_encode($data,JSON_UNESCAPED_UNICODE),$header);
        if($res['status'] !== 'success'){
            return $this->baseFailed(!empty($res['message']) ? $res['message'] : '接口请求失败');
        }
        $returnData = $res['data'] ?? [];
        if(empty($returnData['status']) || $returnData['status'] !== 'success'){
            return $this->baseFailed('操作失败');
        }
        return $this->baseSucceed('操作成功',$returnData);
    }
    /**
     * 获取列表
     * @param $param
     * @return array
     */
    public function getList($param)
    {
        $data = [
            'applicationid' => $this->config['applicationId'],
            'formheaduuid' => $param['formheaduuid'],
            'needpage' => $param['needpage'],
            'page' => $param['page'],
            'pagesize' => $param['limit'],
            'filters' => $param['filters'],
        ];
        $tokenRes = $this->getToken();
        if(empty($tokenRes) || $tokenRes['status'] !== 'success'){
            return $tokenRes;
        }
        $token = $tokenRes['data']['token'];
        $header = [
            'Authorization' => 'Bearer '.$token
        ];
        $res = $this->curlHttp($this->config['url'].'/api/appsheets/sheetapi/getFormList','POST','pass中台获取记录列表',json_encode($data,JSON_UNESCAPED_UNICODE),$header);
        if($res['status'] !== 'success'){
            return $this->baseFailed(!empty($res['message']) ? $res['message'] : '接口请求失败');
        }
        $returnData = $res['data'] ?? [];
        if(empty($returnData['status']) || $returnData['status'] !== 'success'){
            return $this->baseFailed('操作失败');
        }
        return $this->baseSucceed('操作成功',$returnData);
    }
    /**
     * 获取详情
     * @param $param
     * @return array
     */
    public function getRowbyId($param)
    {
        $data = [
            'applicationid' => $this->config['applicationId'],
            'formheaduuid' => $param['formheaduuid'],
            'rowid' => $param['rowid'],
        ];
        $tokenRes = $this->getToken();
        if(empty($tokenRes) || $tokenRes['status'] !== 'success'){
            return $tokenRes;
        }
        $token = $tokenRes['data']['token'];
        $header = [
            'Authorization' => 'Bearer '.$token
        ];
        $res = $this->curlHttp($this->config['url'].'/api/appsheets/sheetapi/getRowbyId','POST','pass中台获取记录详情',json_encode($data,JSON_UNESCAPED_UNICODE),$header);
        if($res['status'] !== 'success'){
            return $this->baseFailed(!empty($res['message']) ? $res['message'] : '接口请求失败');
        }
        $returnData = $res['data'] ?? [];
        if(empty($returnData['status']) || $returnData['status'] !== 'success'){
            return $this->baseFailed('操作失败');
        }
        return $this->baseSucceed('操作成功',$returnData);
    }
    public function getDashboard()
    {
        $tokenRes = $this->getToken();
        if(empty($tokenRes) || $tokenRes['status'] !== 'success'){
            return $tokenRes;
        }
        $token = $tokenRes['data']['token'];
        $header = [
            'Authorization' => 'Bearer '.$token
        ];
        $res = $this->curlHttp($this->config['url'].'/api/platform/dashboard/getApplicationDashboard','GET','获取仪表盘',['app_id'=>'12620936824811ee8c0984a93e086e68'],$header);
        if($res['status'] !== 'success'){
            return $this->baseFailed(!empty($res['message']) ? $res['message'] : '接口请求失败');
        }
        $returnData = $res['data'] ?? [];
        if(empty($returnData['status']) || $returnData['status'] !== 'success'){
            return $this->baseFailed('操作失败');
        }
        return $this->baseSucceed('操作成功',$returnData);
    }

    public function getDashboardDetail()
    {
        $tokenRes = $this->getToken();
        if(empty($tokenRes) || $tokenRes['status'] !== 'success'){
            return $tokenRes;
        }
        $token = $tokenRes['data']['token'];
        $header = [
            'Authorization' => 'Bearer '.$token
        ];
        $res = $this->curlHttp($this->config['url'].'/api/platform/dashboard/getApplicationDashboardDetail','GET','获取仪表盘详情',['id'=>'71a8581607284a79b4475bb1ac369948'],$header);
        if($res['status'] !== 'success'){
            return $this->baseFailed(!empty($res['message']) ? $res['message'] : '接口请求失败');
        }
        $returnData = $res['data'] ?? [];
        if(empty($returnData['status']) || $returnData['status'] !== 'success'){
            return $this->baseFailed('操作失败');
        }
        return $this->baseSucceed('操作成功',$returnData);
    }

    public function getDashboardDetailData()
    {
        $tokenRes = $this->getToken();
        if(empty($tokenRes) || $tokenRes['status'] !== 'success'){
            return $tokenRes;
        }
        $token = $tokenRes['data']['token'];
        $header = [
            'Authorization' => 'Bearer '.$token
        ];
        $data = [
            'id' => '9345',
            'filter_search' => ''
        ];
        $res = $this->curlHttp($this->config['url'].'/api/platform/dashboard/queryDashboardDetailShowData','POST','获取仪表盘详情',json_encode($data,JSON_UNESCAPED_UNICODE),$header);
        if($res['status'] !== 'success'){
            return $this->baseFailed(!empty($res['message']) ? $res['message'] : '接口请求失败');
        }
        $returnData = $res['data'] ?? [];
        if(empty($returnData['status']) || $returnData['status'] !== 'success'){
            return $this->baseFailed('操作失败');
        }
        return $this->baseSucceed('操作成功',$returnData);
    }
}