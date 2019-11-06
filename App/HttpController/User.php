<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-10-23
 * Time: 19:25
 */

namespace App\HttpController;


use App\Model\UserModel;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Http\AbstractInterface\Controller;

class User extends Controller
{
    public function index() {

    }

    public function add() {
        try {
            $user = new UserModel();
            $data = ['name'=>'54xiake', 'sex'=>1];
            $result = $user->add($data);
            $this->response()->write(json_encode($result));
        } catch (\Exception $e) {
            Logger::getInstance()->error($e->getMessage());
        }

    }

    public function get() {
        try {
            $id = (int)$this->request()->getQueryParams('id');
            $user = new UserModel();
            $result = $user->getInfo($id);
            $this->response()->write(json_encode($result));
        } catch (\Exception $e) {
            Logger::getInstance()->error($e->getMessage());
            Logger::getInstance()->error($e->getTraceAsString());
        }

    }
}