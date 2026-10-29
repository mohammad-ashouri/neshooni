<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['listing_id', 'date', 'views', 'clicks'])]
class ListingDailyStat extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['date' => 'date'];
    }
}
