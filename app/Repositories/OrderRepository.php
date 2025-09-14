<?php

namespace App\Repositories;

use App\Enums\OrderEnum;
use App\Models\Order;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OrderRepository extends BaseRepository
{
    public function getFieldsSearchable()
    {
        return [
            'destiny',
            'departure_date',
            'return_date',
            'status',
        ];
    }

    public function model()
    {
        return Order::class;
    }

    public function listPaginate(?int $userId = null)
    {
        $query = QueryBuilder::for(Order::class)
            ->allowedFilters([
                'destiny',
                AllowedFilter::scope('departure_date'),
                AllowedFilter::scope('return_date'),
                'status'
            ]);

        if (! empty($userId)) {
            $query->where('user_id', $userId);
        }

        return $query->paginate();
    }

    public function findOrder(int $orderId, ?int $userId): ?Order
    {
        $query =  $this->model->where('order_id', $orderId);
        if (! empty($userId)) {
            $query->where('user_id', $userId);
        }

        return $query->first();
    }

    public function checkOrderIdExists(int $orderId): bool
    {
        return $this->model
            ->where('order_id', $orderId)
            ->exists();
    }

    public function checkOrderIsApproved(int $orderId): bool
    {
        return $this->model
            ->where('order_id', $orderId)
            ->where('status', OrderEnum::STATUS_APPROVED)
            ->exists();
    }
}
