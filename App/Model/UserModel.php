<?php
/**
 * Created by PhpStorm.
 * User: yugang
 * Date: 2019-10-23
 * Time: 19:19
 */

namespace App\Model;

class UserModel extends BaseModel
{
    protected $table='user';

    public function add($data) {
        $this->db->queryBuilder()->insert($this->table, $data);
        return $this->db->execBuilder();
    }

    public function getInfo($id) {
        $this->db->queryBuilder()->where('id', $id)->getOne($this->table);
        $result = $this->db->execBuilder();
        return $result;
    }
}