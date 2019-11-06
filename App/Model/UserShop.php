<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-10-31
 * Time: 16:24
 */

namespace App\Model;


use EasySwoole\ORM\AbstractModel;

class UserShop extends AbstractModel
{
    protected $tableName = 'user_shop';

    protected $connectionName = 'default';

    protected function getStatusAttr($value, $data)
    {
        $status = [-1=>'删除',0=>'禁用',1=>'正常',2=>'待审核'];
        return $status[$value];
    }
}