<?php

namespace Muskid\DataBuilder;

use Illuminate\Database\Eloquent\Model;

interface DataBuilderInterface
{
    public function builder(Model $model);
}