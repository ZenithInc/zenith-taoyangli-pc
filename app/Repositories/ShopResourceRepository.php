<?php

namespace App\Repositories;


use App\Model\ShopResource;
use Hyperf\Cache\Annotation\Cacheable;
class ShopResourceRepository extends BaseRepository
{

    /**
     * @var ShopResource;
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
    public function updateDo($data,$fieldValue,$field)
    {
        return $this->model->updateDo($data,$fieldValue,$field);
    }
    public function findOneWhere($where, $columns = ['*'], $order = '')
    {
        return $this->model->findOneWhere($where, $columns, $order);
    }
    public function findWhere($where, $columns = ['*'])
    {
        return $this->model->findWhere($where, $columns);
    }
    public function getList($where=[],$page='',$limit='',$order='',$columns=['*'],$groupBy='')
    {
        return $this->model->getList($where,$page,$limit,$order,$columns,$groupBy);
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
