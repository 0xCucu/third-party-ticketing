<?php

use Muskid\Providers\KuGouProvider;
use PHPUnit\Framework\TestCase;
class ApiTest extends TestCase
{

    public function testBaseUrl()
    {
        $provider = new KuGouProvider();
        $provider->setAppId('1002');
        $provider->setSecretKey('j!dn7f9#w2a^5hm');
        $provider->handle([
            'sourceid'=>1,
            'item_name'=>'ad',
            'cover'=>'/1.png',
            'provice'=>'北京',
            'city'=>'北京',
            'ven_name' => '体育馆',
            'venue_address' => '体育馆路1234',
            'type' => 2,
            'show_time' => time(),
            'end_time' => time(),
            'performer' => '周杰伦,刘德华,五月天',
            'status' => 4,
            'prices' => [
                [
                    'status'=>1,
                    'price'=>100,
                    'desc' => '1'
                ],
                [
                    'status'=>1,
                    'price'=>160,
                    'desc' => '2'
                ]

            ],
            'is_choose_seat' => 0,
            'ticket_url' => 'asd',
            'detail_url' => 'http:sad'
        ]);
//        $this->assertSame('http://auth.url', $provider->buildSignature);

    }
}