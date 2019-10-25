<?php
namespace App\Model;

use App\Common\DB;
use EasySwoole\Mysqli\Mysqli;

class BaseModel
{
    protected $db;
    protected $table;
    function __construct()
    {
        $this->db = DB::getInstance()->dbCon();
    }

    function getDbConnection():Mysqli
    {
        return $this->db;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }
}