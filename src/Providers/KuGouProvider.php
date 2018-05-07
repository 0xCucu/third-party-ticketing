<?php

namespace Muskid\Providers;

use App\Models\Performances;
use Muskid\Providers\ProviderInterface;

class KuGouProvider extends AbstractProvider implements ProviderInterface
{
    protected $baseUrl = 'http://test-tools.mobile.kugou.com';

    protected $appId = '1002';

    protected $secretkey = 'j!dn7f9#w2a^5hm';

    /**
     * Notes:   处理
     * Author:  Cucumber
     * Date:    2018/5/8
     * Time:    1:02
     * @param $requestData
     * @return string
     */

    public function handle($requestData)
    {

        $appid = $this->appId;
        $t = time();
        $requestData['_t'] = $t;
        $sign = $this->buildSignature($requestData);
        $requestData['appid'] = $appid;
        $requestData['token'] = $sign;
        dd($requestData);
        $response = $this->getHttpClient()->post($this->baseUrl . '/api/v1/perform/open_add', [
            "form_params" => $requestData
        ]);
        return $response->getBody()->getContents();
    }


    public function setAppId($appid)
    {
        $this->appId = $appid;
    }

    public function setSecretKey($secretkey)
    {
        $this->secretkey = $secretkey;
    }

    /**
     * Notes:   创建签名
     * Author:  Cucumber
     * Date:    2018/5/8
     * Time:    1:09
     * @param $data
     * @return string
     */

    protected function buildSignature($data)
    {
        $appid = $this->appId;
        $secretkey = $this->secretkey;
        ksort($data);
        $string_parma = '';
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $string_parma .= $key . $value;
        }
        return md5($appid.md5($string_parma).$secretkey);
    }


}