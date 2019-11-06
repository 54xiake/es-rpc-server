<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-10-31
 * Time: 16:23
 */

namespace App\HttpController;


use App\Model\UserModel;
use App\Model\UserShop;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\DbManager;

class Orm extends Controller
{

    function index()
    {
        //自定义查询
        $queryBuild = new QueryBuilder();
        $queryBuild->raw("show tables");

        $data = DbManager::getInstance()->query($queryBuild, $raw = true, $connectionName = 'default');
        var_dump($data);
        $this->writeJson(1, $data->getResult());
    }

    function shop() {
        // 开启事务
        $strat = DbManager::getInstance()->startTransaction();

        try {
            $shop = UserShop::create()->get(1);

            $shop->shop_name = '123123132';

            // 更新操作
            $res = $shop->update();
//            throw new \Exception('aaaa');

        } catch (\Exception $e) {
            // 不管更新成功还是失败，直接回滚
            $rollback = DbManager::getInstance()->rollback();
        }

        // 返回false 因为连接已经回滚。事务关闭。
        $commit = DbManager::getInstance()->commit();
        $this->writeJson(1, $res);
    }

    function find() {
//        $res = UserShop::create()->get(1);

//        $res = UserShop::create()->get(1);
//        $res->destroy();//删除获取的单条记录

        //单条数据
//        $res = UserShop::create()->get(['shop_name' => 'aaa']);
//        $res = UserShop::create()->where(['shop_name' => 'aaa'])->get();
//        $res = UserShop::create()->findOne(['shop_name' => 'aaa']);

        //多条数据
//        $res = UserShop::create()->all();
//        $res = UserShop::create()->all(['shop_name' => 'aaa']);
//        $res = UserShop::create()->where(['shop_name' => 'aaa'])->all();
//        $res = UserShop::create()->findAll(['shop_name' => 'aaa']);
//        $res = UserShop::create()->where(['shop_name' => 'aaa'])->findAll();

        //复杂查询
//        $res = UserShop::create()->get(function (QueryBuilder $builder) {
//            $builder->where('shop_name', 'aaa');
//        });

//        $res = UserShop::create()->all(function (QueryBuilder $builder) {
//            $builder->where('shop_name', 'aaa');
//            $builder->orderBy('id', 'desc');
//            $builder->limit(10);
//        });

//        $res = UserShop::create()->field('shop_name')->get(1);

        //分页
        $page = 1;          // 当前页码
        $limit = 10;        // 每页多少条数据

        $model = UserShop::create()->limit($limit * ($page - 1), $limit * $page - 1)->withTotalCount();

        // 列表数据
        $list = $model->all(null, true);
        var_dump($list);

        $result = $model->lastQueryResult();
        var_dump($result);

        // 总条数
        $res['total'] = $result->getTotalCount();
        $res['data'] = $result->getResult();


        $this->writeJson(1, $res);
    }

    function add() {
//        $model = new UserShop();
//        // 不同设置值的方式
////        $model->setAttr('id', 7);
//        $model->shop_name = 'name';

        $model = new UserShop([
            'shop_name' => 'siam'
        ]);

        $res = $model->save();



        $this->writeJson(1, $res);
    }

    function del() {
//        $res = UserShop::create()->destroy(1); //通过直接指定主键(如果存在)
//        $res = UserShop::create()->destroy('2,4,5');//指定多个参数每个参数为不同主键
//        $res = UserShop::create()->destroy([3, 7]);//数组指定多个主键

//        $res = UserShop::create()->destroy(['shop_name' => 'name']);//数组指定 where 条件结果来删除
//        $res = UserShop::create()->destroy(function (QueryBuilder $builder) {
//            $builder->where('id', 1);
//        });


        //删除全表数据
        $res = UserShop::create()->destroy(null,true);

        $this->writeJson(1, $res);
    }

    function update() {
//        $res = UserShop::create()->update([
//            'shop_name' => 'new'
//        ], ['id' => 23]);

        $model = UserShop::create()->get(23);

        // 获取后传入更新数组
//        $res = $model->update([
//            'shop_name' => 123,
//        ]);

//        //获取后指定字段赋值
//        $model->shop_name = 323;
        $model['shop_name'] = 333;
//
//        // 调用保存  返回bool 成功失败
        $res = $model->update();

        $this->writeJson(1, $res);
    }

    function group() {
//        $res = UserShop::create()->field('count(1) as count, `shop_name`')->group('shop_name')->all(null);
        $res = UserShop::create()->field('count(1) as count, `shop_name`')->group('shop_name')->select();;
        $this->writeJson(1, $res);

    }

    function join() {
        $res = UserShop::create()->alias('us')->join('user as u','u.id = us.user_id')->get();
        $this->writeJson(1, $res);

    }

    function count() {
        $res = UserShop::create()->count();
        $this->writeJson(1, $res);

    }

    function status() {
        $res = UserShop::create()->get(23);
        var_dump($res->status);
        $this->writeJson(1, $res);
    }
}