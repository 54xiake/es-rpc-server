<?php
namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\DDL\Blueprint\Table;
use EasySwoole\DDL\DDLBuilder;
use EasySwoole\DDL\Enum\Character;
use EasySwoole\DDL\Enum\Engine;

class Ddl extends Controller
{

    function index()
    {
        // TODO: Implement index() method.
    }

    function user() {
        $sql = DDLBuilder::table('user', function (Table $table) {

            $table->setTableComment('用户表')//设置表名称/
            ->setTableEngine(Engine::INNODB)//设置表引擎
            ->setTableCharset(Character::UTF8MB4_GENERAL_CI);//设置表字符集
            $table->colInt('id', 10)->setColumnComment('用户ID')->setIsAutoIncrement()->setIsPrimaryKey();
            $table->colVarChar('name')->setColumnLimit(64)->setIsNotNull()->setColumnComment('用户名');
            $table->colTinyInt('sex')->setIsUnsigned()->setIsNotNull()->setDefaultValue(1)->setColumnComment('性别：1男，2女');
            $table->colTinyInt('age')->setIsUnsigned()->setColumnComment('年龄')->setIsNotNull();
            $table->colInt('created_at', 10)->setIsNotNull()->setColumnComment('创建时间');
            $table->colInt('updated_at', 10)->setIsNotNull()->setColumnComment('更新时间');
            $table->indexUnique('username_index', 'name');//设置索引
        });
        echo $sql;
    }

    function shop() {
        $sql = DDLBuilder::table('user_shop', function (Table $table) {
            $table->setTableComment('门店表')//设置表名称/
            ->setTableEngine(Engine::INNODB)//设置表引擎
            ->setTableCharset(Character::UTF8MB4_GENERAL_CI);//设置表字符集
            $table->colInt('id', 10)->setColumnComment('门店ID')->setIsAutoIncrement()->setIsPrimaryKey();
            $table->colVarChar('shop_name')->setColumnLimit(64)->setIsNotNull()->setColumnComment('门店名称');
            $table->colInt('user_id', 10)->setColumnComment('用户ID');
        });
        echo $sql;
    }
}