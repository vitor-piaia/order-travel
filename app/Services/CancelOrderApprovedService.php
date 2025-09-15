<?php

namespace App\Services;

use App\Enums\OrderEnum;
use App\Exceptions\CancelOrder\CancelOrderExistException;
use App\Exceptions\Order\OrderNotApprovedException;
use App\Repositories\CancelOrderApprovedRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CancelOrderApprovedService
{
    public function __construct(protected CancelOrderApprovedRepository $cancelOrderApprovedRepository, protected OrderService $orderService){}

    public function show(int $id)
    {
        $userId = null;
        if (! Auth::user()->hasRole('admin')) {
            $userId = Auth::id();
        }

        $cancelOrder = $this->cancelOrderApprovedRepository->findCancelOrder($id, $userId);

        if (! $cancelOrder->id) {
            throw new Exception();
        }

        return $cancelOrder;
    }

    public function list(int $page = 1, string $orderBy = 'asc')
    {
        $userId = null;
        if (! Auth::user()->hasRole('admin')) {
            $userId = Auth::id();
        }

        return $this->cancelOrderApprovedRepository->listPaginate($page, $orderBy, $userId);
    }

    public function store(array $post)
    {
        $checkOrderIsApproved = $this->orderService->checkOrderIsApproved($post['order_id']);
        if (! $checkOrderIsApproved) {
            throw new OrderNotApprovedException();
        }

        $cancelOrderExist = $this->cancelOrderApprovedRepository->checkOrderIdExists($post['order_id']);
        if ($cancelOrderExist) {
            throw new CancelOrderExistException();
        }

        $data = array_merge($post, ['status' => OrderEnum::STATUS_REQUESTED]);

        $cancelOrder = $this->cancelOrderApprovedRepository->create($data);

        if (! $cancelOrder->id) {
            throw new Exception();
        }

        return $cancelOrder;
    }

    public function updateStatus(array $data)
    {
        DB::beginTransaction();
        $update = $this->cancelOrderApprovedRepository->update(['status' => $data['status']], $data['id']);

        if (! $update) {
            DB::rollBack();
            throw new Exception();
        }

        if ($data['status'] == OrderEnum::STATUS_APPROVED) {
            $cancelOrder = $this->cancelOrderApprovedRepository->find($data['id']);
            app()->make(OrderService::class)->updateStatus([
                'status' => OrderEnum::STATUS_CANCELED,
                'order_id' => $cancelOrder->order_id
            ]);
        }


        DB::commit();
        return true;
    }
}
