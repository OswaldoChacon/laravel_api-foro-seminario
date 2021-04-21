<?php

namespace App\Traits;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

trait TraitCreatedAtAttribute
{

    public function getCreatedAtAttribute($value)
    {
        return Carbon::createFromTimestamp(strtotime($value))
            ->timezone(Config::get('app.timezone'))
            ->toDateTimeString();
    }
}
