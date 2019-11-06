<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-10-29
 * Time: 18:05
 */

namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\Controller;
use MQ\Model\TopicMessage;
use MQ\MQClient;

class Mq extends Controller
{

    private $client;
    private $producer;
    public function getClient()
    {
        $this->client = new MQClient(
            "http://127.0.0.1:9876", null, null
        );
        // 所属的 Topic
        $topic = "TopicTest";
        // Topic所属实例ID，默认实例为空NULL
        $instanceId = null;
        $this->producer = $this->client->getProducer($instanceId, $topic);
    }

    function index()
    {
        $this->getClient();
        try
        {
            for ($i=1; $i<=4; $i++)
            {
                $publishMessage = new TopicMessage(
                    "abc"// 消息内容
                );
                // 设置属性
                $publishMessage->putProperty("a", $i);
                // 设置消息KEY
                $publishMessage->setMessageKey("MessageKey");
                $publishMessage->setMessageTag("tagA");
                if ($i % 2 == 0) {
                    // 定时消息, 定时时间为10s后
                    $publishMessage->setStartDeliverTime(time() * 1000 + 10 * 1000);
                }
                var_dump($publishMessage);
                $result = $this->producer->publishMessage($publishMessage);
                var_dump($result);
                print "Send mq message success. msgId is:" . $result->getMessageId() . ", bodyMD5 is:" . $result->getMessageBodyMD5() . "\n";
            }
        } catch (\Exception $e) {
            print_r($e->getMessage() . "\n");
        }
    }
}