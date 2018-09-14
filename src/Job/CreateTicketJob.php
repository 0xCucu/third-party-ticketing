<?php

namespace Muskid\Job;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Muskid\Ticket;

class CreateTicketJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $provider;

    protected $data;
    public $tries = 5;

    public function __construct(Ticket $provider, $info)
    {
        $this->provider = $provider;
        $this->data = $info;
    }

    public function handle()
    {
        if ($this->attempts() > 4) {
            \Log::error('推送酷狗票品信息: 演出【' . $this->data['args'][1] . '】 场次【' . $this->data['args'][2] . '】 res: 尝试多次失败');
            return true;
        }
        $data = $this->data;
        try {
            $res = $this->provider->handle($this->data['method'], $this->data['args']);  
            \Log::info('推送酷狗票品信息: 演出【' . $this->data['args'][1] . '】 场次【' . $this->data['args'][2] . '】 res:' . $res);
           
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
        sleep(1);//防止系统繁忙
        return true;

    }
}
