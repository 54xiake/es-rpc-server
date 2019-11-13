<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-11-01
 * Time: 09:33
 */

namespace App\Common;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class Dingding
{
    public $webhook;
    public $secret;

    public function __construct()
    {
        $this->webhook = 'send?access_token=1d1c5075852ce761e67e1d5cfcc34550804c7abdda1dbffe8353015fdea860f9';
        $this->secret = 'SECea214362ab033a5f7f850557a18e25111ae848caaf45084f2fb0817740104a58';
    }

    public function getNoticeUri() {
        list($msec, $sec) = explode(' ', microtime());
        $timestamp = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $data = $timestamp . "\n" . $this->secret;
        $sign = urlencode(base64_encode(hash_hmac('sha256', $data, $this->secret, TRUE)));
        $noticeUrl = $this->webhook.'&timestamp='.$timestamp.'&sign='.$sign;
        return $noticeUrl;
    }

    public function sendMessage($data, $type='text') {
        $noticeUri = $this->getNoticeUri();
        $client = new Client(['base_uri' => 'https://oapi.dingtalk.com/robot/']);
        $request = new Request('POST', $noticeUri);
        switch ($type) {
            case 'markdown':
                $message = [
                    'msgtype' => 'markdown',
                    'markdown' => [
                        'title' => $data['title'],
                        'text' => $data['content']
                    ]
                ];
                break;
            default:
                $message = [
                    'msgtype' => 'text',
                    'text' => [
                        'content' => '【通知】'.$data['content']
                    ]
                ];
        }
        $response = $client->send($request, ['json'=>$message]);
//        $response = $client->send($request, [
//            'msgtype' => 'text',
//            'text' => ['content' => 'test']
//        ]);

        var_dump($response->getBody()->getContents());
        return $response->getBody()->getContents();
    }
}