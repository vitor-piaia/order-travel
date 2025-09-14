<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $table = 'orders';

    protected $primaryKey = 'order_id';

    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'name',
        'destiny',
        'departure_date',
        'return_date',
        'status',
        'user_id',
        'admin_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'user_id',
        'admin_id',
        'created_at',
        'updated_at',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function admin(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'admin_id');
    }

    public function scopeDepartureDate(Builder $query, $date): Builder
    {
        return $query->where('departure_date', '>=', Carbon::createFromFormat('d/m/Y', $date)->toDateString());
    }

    public function scopeReturnDate(Builder $query, $date): Builder
    {
        return $query->where('return_date', '<=', Carbon::createFromFormat('d/m/Y', $date)->toDateString());
    }

    public function setDepartureDateAttribute($value): void
    {
        $this->attributes['departure_date'] = Carbon::createFromFormat('d/m/Y', $value)->toDateString();
    }

    public function setReturnDateAttribute($value): void
    {
        $this->attributes['return_date'] = Carbon::createFromFormat('d/m/Y', $value)->toDateString();
    }

    public function getDepartureDateAttribute($value): string
    {
        return Carbon::parse($value)->format('d/m/Y');
    }

    public function getReturnDateAttribute($value): string
    {
        return Carbon::parse($value)->format('d/m/Y');
    }
}
