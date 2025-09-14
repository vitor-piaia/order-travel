<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CancelOrdersApproved extends Model
{
    protected $table = 'cancel_orders_approved';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'order_id',
        'message',
        'status',
        'admin_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'admin_id',
        'created_at',
        'updated_at',
    ];

    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'order_id', 'order_id');
    }

    public function admin(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'admin_id');
    }
}
