<?php

namespace App\Repositories;

use App\Models\CancelOrdersApproved;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CancelOrderApprovedRepository extends BaseRepository
{
    public function getFieldsSearchable()
    {
        return [
            'status',
        ];
    }

    public function model()
    {
        return CancelOrdersApproved::class;
    }

    public function listPaginate(?int $userId = null)
    {
        $query = QueryBuilder::for(CancelOrdersApproved::class)
            ->select('cancel_orders_approved.*')
            ->allowedFilters(['status']);

        if (! empty($userId)) {
            $query->leftJoin('orders', 'orders.order_id', '=', 'cancel_orders_approved.order_id')
                ->where('user_id', $userId);
        }

        return $query->paginate();
    }

    public function findCancelOrder(int $id, ?int $userId): ?CancelOrdersApproved
    {
        $query =  $this->model
            ->select('cancel_orders_approved.*')
            ->where('cancel_orders_approved.id', $id);

        if (! empty($userId)) {
            $query->leftJoin('orders', 'orders.order_id', '=', 'cancel_orders_approved.order_id')
                ->where('user_id', $userId);
        }

        return $query->first();
    }

    public function checkOrderIdExists(int $orderId): bool
    {
        return $this->model
            ->where('order_id', $orderId)
            ->exists();
    }
}
