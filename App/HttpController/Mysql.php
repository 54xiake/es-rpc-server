<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-11-06
 * Time: 17:50
 */

namespace App\HttpController;


use App\Common\DB;
use App\Common\Log\Logger;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Mysqli\QueryBuilder;

class Mysql extends Controller
{

    function index()
    {

        $mysqlConfig = \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL');
        $config = new \EasySwoole\Mysqli\Config($mysqlConfig);
        $client = new \EasySwoole\Mysqli\Client($config);

        go(function () use($client) {
            //构建sql
            $client->queryBuilder()->get('user_shop');
            //执行sql
            print_r($client->execBuilder());

            $client->queryBuilder()->where('shop_name','333')->getOne('user_shop');
            print_r($client->execBuilder());
        });

        $builder = new QueryBuilder();
        $builder->where('shop_name','333')->get('user_shop');

        //获取最后的查询参数
        print_r($builder->getLastQueryOptions());

        //获取子查询
        print_r($builder->getSubQuery());


        //获取上次条件构造的预处理sql语句
        echo $builder->getLastPrepareQuery();

        //获取上次条件构造的预处理sql语句所以需要的绑定参数
        print_r($builder->getLastBindParams());

        //获取上次条件构造的sql语句
        echo $builder->getLastQuery();

    }

    function add() {
        try {
            $mysqlConfig = \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL');
            $config = new \EasySwoole\Mysqli\Config($mysqlConfig);
            $client = new \EasySwoole\Mysqli\Client($config);
            $client->queryBuilder()->insert('user_shop', ['shop_name' => '测试店', 'user_id' => 1]);
            $result = $client->execBuilder();

            $this->writeJson(1, $result);
        } catch (\Exception $e) {
            print_r($e->getMessage());
        }
    }

    function replace() {
        $mysqlConfig = \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL');
        $config = new \EasySwoole\Mysqli\Config($mysqlConfig);
        $client = new \EasySwoole\Mysqli\Client($config);
        $client->queryBuilder()->replace('user_shop', ['shop_name' => '测试店', 'user_id' => 1]);
        $result = $client->execBuilder();

        $this->writeJson(1, $result);
    }

    function update() {
        $mysqlConfig = \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL');
        $config = new \EasySwoole\Mysqli\Config($mysqlConfig);
        $client = new \EasySwoole\Mysqli\Client($config);
//        $client->queryBuilder()->where('user_id', 1)->update('user_shop', ['shop_name' => '门店名称']);

//        $client->queryBuilder()->update('user_shop', ['shop_name' => '限制个数'], 5);

        $client->queryBuilder()
            ->where('user_id', -11)
            ->update('user_shop', [
                'user_id'    => QueryBuilder::inc(12),
//                'user_id' => QueryBuilder::dec(3),
            ]);


        $result = $client->execBuilder();
        $this->writeJson(1, $result);
    }

    function del() {
        $mysqlConfig = \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL');
        $config = new \EasySwoole\Mysqli\Config($mysqlConfig);
        $client = new \EasySwoole\Mysqli\Client($config);
//        $client->queryBuilder()->delete('user_shop', 3);
        $client->queryBuilder()->where('user_id', 1)->delete('user_shop');

        $result = $client->execBuilder();
        $this->writeJson(1, $result);

    }

    function query() {
        $mysqlConfig = \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL');
        $config = new \EasySwoole\Mysqli\Config($mysqlConfig);
        $client = new \EasySwoole\Mysqli\Client($config);
        $client->queryBuilder()->fields(['id','shop_name as name'])
            ->where('user_id', [1], 'in')
            ->orWhere('shop_name', '测试店', '=')
            ->orderBy('id', 'desc')
            ->limit(2, 3)
            ->get('user_shop');
        $result = $client->execBuilder();
        echo $client->queryBuilder()->getLastQuery().PHP_EOL;

        $this->writeJson(1, $result);

    }

    function group() {
        $mysqlConfig = \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL');
        $config = new \EasySwoole\Mysqli\Config($mysqlConfig);
        $client = new \EasySwoole\Mysqli\Client($config);
        $client->queryBuilder()->fields(['user_id','count(1) as num'])
            ->groupBy('user_id')
            ->having('num', 3,'>')
            ->orHaving('num', 5,'<')
            ->get('user_shop');
        $result = $client->execBuilder();
        echo $client->queryBuilder()->getLastQuery().PHP_EOL;

        $this->writeJson(1, $result);
    }

    function join() {
        $mysqlConfig = \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL');
        $config = new \EasySwoole\Mysqli\Config($mysqlConfig);
        $client = new \EasySwoole\Mysqli\Client($config);
        $client->queryBuilder()->lockInShareMode(true);
        $client->queryBuilder()->setPrefix('')->join('user as u','u.id = us.user_id','LEFT')
            ->where('u.id', 1)
            ->get('user_shop as us');
        $result = $client->execBuilder();


        echo $client->queryBuilder()->getLastQuery().PHP_EOL;

        $this->writeJson(1, $result);
    }

    function count() {
        $mysqlConfig = \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL');
        $config = new \EasySwoole\Mysqli\Config($mysqlConfig);
        $client = new \EasySwoole\Mysqli\Client($config);
        $client->queryBuilder()->withTotalCount()->get("user_shop",null,"*");

        $client->execBuilder();
            //select FOUND_ROWS();
        $result = $client->rawQuery('select FOUND_ROWS()');
        echo $client->queryBuilder()->getLastQuery().PHP_EOL;
        $this->writeJson(1, $result);
    }

    //事务
    function transaction() {
//        $mysqlConfig = \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL');
//        $config = new \EasySwoole\Mysqli\Config($mysqlConfig);
//        $client = new \EasySwoole\Mysqli\Client($config);
//        $client->queryBuilder()->startTransaction();
//        DB::getInstance()->dbCon()->queryBuilder()->startTransaction();
        DB::getInstance()->dbCon()->rawQuery('start transaction');
        try {
            DB::getInstance()->dbCon()->queryBuilder()->insert('user_shop', ['shop_name' => 'transaction555', 'user_id' => 2]);
            DB::getInstance()->dbCon()->execBuilder();

            DB::getInstance()->dbCon()->queryBuilder()->insert('user_shop', ['shop_name' => 'transaction444', 'user_id' => 2]);
            DB::getInstance()->dbCon()->execBuilder();

            echo DB::getInstance()->dbCon()->queryBuilder()->getLastQuery().PHP_EOL;

            throw new \Exception('事务回滚');

        } catch (\Exception $e) {
//            DB::getInstance()->dbCon()->queryBuilder()->rollback();
            DB::getInstance()->dbCon()->rawQuery("rollback");
            Logger::getInstance()->log($e->getMessage());
            return $this->writeJson(0, '失败');
        }
//        DB::getInstance()->dbCon()->queryBuilder()->commit();
        DB::getInstance()->dbCon()->rawQuery("commit");
        echo DB::getInstance()->dbCon()->queryBuilder()->getLastQuery().PHP_EOL;

//        return $this->writeJson(1, $result);


    }

    public function onException(\Throwable $throwable): void
    {
        parent::onException($throwable); // TODO: Change the autogenerated stub
    }
}