<?php

namespace App\Services;

use App\Enums\OrderEnum;
use App\Exceptions\Order\CancelOrderExistException;
use App\Exceptions\Order\OrderNotApprovedException;
use App\Repositories\CancelOrderApprovedRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CancelOrderApprovedService
{
    public function __construct(protected CancelOrderApprovedRepository $cancelOrderApprovedRepository, protected OrderService $orderService){}

    public function find(int $id)
    {
        return $this->cancelOrderApprovedRepository->find($id);
    }

    public function show(int $id)
    {
        $userId = null;
        if (! Auth::user()->hasRole('admin')) {
            $userId = auth()->id();
        }

        $order = $this->cancelOrderApprovedRepository->findCancelOrder($id, $userId);

        if (! $order->order_id) {
            throw new Exception();
        }

        return $order;
    }

    public function list()
    {
        $userId = null;
        if (! Auth::user()->hasRole('admin')) {
            $userId = auth()->id();
        }

        return $this->cancelOrderApprovedRepository->listPaginate($userId);
    }

    public function store(array $post)
    {
        $checkOrderIsApproved = $this->orderService->checkOrderIsApproved($post['order_id']);
        if (! $checkOrderIsApproved) {
            throw new OrderNotApprovedException();
        }

        $orderExist = $this->cancelOrderApprovedRepository->checkOrderIdExists($post['order_id']);
        if ($orderExist) {
            throw new CancelOrderExistException();
        }

        $data = array_merge($post, [
            'status' => OrderEnum::STATUS_REQUESTED,
            'user_id' => auth()->id()
        ]);

        $order = $this->cancelOrderApprovedRepository->create($data);

        if (! $order->order_id) {
            throw new Exception();
        }

        return $order;
    }

    public function update(array $data)
    {
        $update = $this->cancelOrderApprovedRepository->updateMultiple(['status' => $data['status']], ['order_id' => $data['order_id']]);

        if (! $update) {
            throw new Exception();
        }

        return true;
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
