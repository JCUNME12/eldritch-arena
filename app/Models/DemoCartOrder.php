<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoCartOrder extends Model
{
    protected $guarded = ['id'];

    public function payments()
    {
        return $this->hasMany(DemoPayment::class, 'cart_order_id');
    }
}
