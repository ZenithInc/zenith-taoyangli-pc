<?php

namespace App\Repositories;


use App\Model\GoodsOrderIdentity;
use Hyperf\Cache\Annotation\Cacheable;
class GoodsOrderIdentityRepository extends BaseRepository
{

    /**
     * @var GoodsOrderIdentity;
     */
    public $model;

    public function getInfo($id, $cache = false)
    {
        return $this->model->getOneById($id,$cache);
    }

    public function createDo($data)
    {
        return $this->model->createDo($data);
    }
    public function insertData($data)
    {
        return $this->model->insertData($data);
    }
    public function updateDo($data,$fieldValue,$field='id')
    {
        return $this->model->updateDo($data,$fieldValue,$field);
    }
    public function findOneWhere($where, $columns = ['*'], $order = '')
    {
        return $this->model->findOneWhere($where, $columns, $order);
    }
    public function findWhere(array $where,array $columns = ['*']){
        return $this->model->findWhere($where,$columns);
    }
    public function getList(array $where, array $columns = ['*'], array $options = [])
    {
        return $this->model->getListOptions( $where,$columns,$options);
    }

    public function updateByCondition($data,$where)
    {
        return $this->model->updateByCondition($data,$where);
    }
    public function countNum($where)
    {
        return $this->model->countNum($where);
    }
}
