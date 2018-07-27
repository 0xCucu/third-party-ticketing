<?php

namespace Muskid;

use Illuminate\Support\Facades\Queue;
use Muskid\Job\CreateTicketJob;
use Muskid\Providers\ProviderInterface;

class Ticket
{
    /**
     * @var
     */
    protected static $_instances;

    /**
     * 是否需要同步
     * @var bool
     */
    public $async = false;
    /**
     * 基础服务提供者
     * @var
     */
    protected $provider;

    /**
     * @return mixed
     */
    public static function getInstances()
    {
        if (self::$_instances && self::$_instances instanceof self) {
            $instance = self::$_instances;
        } else {
            $instance = self::$_instances = new self();
        }
        return $instance;
    }

    /**
     * Notes:   async create ticket
     * Author:  Cucumber
     * Date:    2018/5/7
     * Time:    2:31
     * @return mixed
     */
    public static function async()
    {
        $instance = self::getInstances();
        $instance->async = true;
        return $instance;
    }

    /**
     * Notes:   调用魔法函数选择指定服务提供者
     * Author:  Cucumber
     * Date:    2018/5/8
     * Time:    1:04
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $instance = self::getInstances();
        return $instance->handle($method, $args);
    }

    /**
     * Notes:   调用魔法函数选择指定服务提供者
     * Author:  Cucumber
     * Date:    2018/5/8
     * Time:    1:04
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->handle($method, $args);
    }

    /**
     * Notes:   自定义服务提供者
     * Author:  Cucumber
     * Date:    2018/5/8
     * Time:    1:05
     * @param $provider
     * @return mixed
     */
    public static function withProvider($provider)
    {
        $instance = self::getInstances();
        $providerInstance = $instance->resloveClass($provider);
        $instance->setProvider($providerInstance);
        return $instance;
    }

    /**
     * Notes:   处理调用服务提供者
     * Author:  Cucumber
     * Date:    2018/5/8
     * Time:    1:05
     * @param $method
     * @param $args
     * @return mixed
     */

    protected function handle($method, $args)
    {
        $providerInstance = $this->getProvidersInstance($method);
        if (!$this->getProvider()) {
            $this->setProvider($providerInstance);
        }
        return $this->createTicket($args);
    }

    /**
     * Notes:   设置服务提供者
     * Author:  Cucumber
     * Date:    2018/5/8
     * Time:    1:05
     * @param ProviderInterface $providerInstance
     */
    protected function setProvider(ProviderInterface $providerInstance)
    {
        $this->provider = $providerInstance;
    }

    /**
     * Notes:   获取服务提供者
     * Author:  Cucumber
     * Date:    2018/5/8
     * Time:    1:06
     * @return mixed
     */
    protected function getProvider()
    {
        return $this->provider;
    }

    /**
     * Notes:   创建票品
     * Author:  Cucumber
     * Date:    2018/5/8
     * Time:    1:06
     * @param $args
     * @return mixed
     */
    protected function createTicket($args)
    {
        $data = $this->getDataByDataBuilder($args);
        if (!$data) {
            return false;
        }
        $providerInstance = $this->getProvider();
        return $this->create($providerInstance, $data);
    }

    /**
     * Notes:   调用指定服务提供者创建票品
     * Author:  Cucumber
     * Date:    2018/5/8
     * Time:    1:06
     * @param $providerInstance
     * @param $data
     * @return mixed
     */
    public function create($providerInstance, $data)
    {
        if (!$this->async) {
            return call_user_func_array([$providerInstance, 'handle'], [$data]);
        }
        return Queue::pushOn('createTicket', new CreateTicketJob($providerInstance, $data));
    }

    /**
     * Notes:   更具数据构造者构造数据
     * Author:  Cucumber
     * Date:    2018/5/8
     * Time:    1:07
     * @param $args
     * @return mixed
     */

    protected function getDataByDataBuilder($args)
    {
        $dataBuilderInstance = $this->makeDataBuilder($args);
        $args = array_except($args, 0);
        return $dataBuilderInstance->handle($args);
    }

    /**
     * Notes:   创建数据构造者
     * Author:  Cucumber
     * Date:    2018/5/8
     * Time:    1:08
     * @param $args
     * @return bool|\Illuminate\Foundation\Application|mixed
     * @throws \Exception
     */
    protected function makeDataBuilder($args)
    {
        if (count($args) == 0) {
            throw new \Exception('Data Builder is Necessary !');
            return false;
        }

        $dataBuilder = $args[0];
        if (!class_exists($args[0])) {
            throw new \Exception("No Data Builder [$dataBuilder] Found !");
            return false;
        }
        return app($dataBuilder);

    }

    /**
     * Notes:   获取服务提供者实例
     * Author:  Cucumber
     * Date:    2018/5/8
     * Time:    1:08
     * @param $method
     * @return bool
     */
    protected function getProvidersInstance($method)
    {
        $provider = '\\Muskid\\Providers\\' . $method . 'Provider';
        return $this->resloveClass($provider);
    }

    /**
     * Notes:   更具传入类名获取实例
     * Author:  Cucumber
     * Date:    2018/5/8
     * Time:    1:08
     * @param $className
     * @return bool
     * @throws \Exception
     */
    protected function resloveClass($className)
    {
        if (!class_exists($className)) {
            throw new \Exception("no provider named [$className]");
            return false;
        }
        return new $className;
    }
}
