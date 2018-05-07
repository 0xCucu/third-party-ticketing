<?php

namespace Muskid\DataBuilder;

abstract class BasicDataBuilder
{

    public function handle($args)
    {
        $res = $this->build(...$args);
        if (count($res) == 0) {
            return [];
        };
        if (count($res) == 1) {
            return $this->builder($res->first());
        }
        $resArray = [];
        foreach ($res as $item) {
            $resArray[] = $this->builder($item);
        }
        return $resArray;
    }
}