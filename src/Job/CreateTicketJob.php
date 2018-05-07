<?php

namespace Muskid\Job;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Muskid\Providers\ProviderInterface;

class CreateTicketJob implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $provider;

    protected $data;

    public function __construct(ProviderInterface $provider, $data)
    {
        $this->provider = $provider;
        $this->data = $data;
    }

    public function handle()
    {
        $data = $this->data;
        $this->provider->handle($data);
    }
}