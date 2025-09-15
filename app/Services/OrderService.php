<?php

namespace App\Services;

use App\Enums\OrderEnum;
use App\Exceptions\Order\OrderExistException;
use App\Mail\Orders\Approved;
use App\Mail\Orders\Reproved;
use App\Repositories\OrderRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class OrderService
{
    public function __construct(protected OrderRepository $orderRepository) {}

    public function show(int $orderId): ?Model
    {
        $userId = null;
        if (! Auth::user()->hasRole('admin')) {
            $userId = Auth::id();
        }

        $order = $this->orderRepository->findOrder($orderId, $userId);

        if (! $order->order_id) {
            throw new Exception;
        }

        return $order;
    }

    public function list(int $page = 1, string $orderBy = 'asc'): LengthAwarePaginator
    {
        $userId = null;
        if (! Auth::user()->hasRole('admin')) {
            $userId = Auth::id();
        }

        return $this->orderRepository->listPaginate($page, $orderBy, $userId);
    }

    public function store(array $post): Model
    {
        $orderExist = $this->orderRepository->checkOrderIdExists($post['order_id']);
        if ($orderExist) {
            throw new OrderExistException;
        }

        $data = array_merge($post, [
            'status' => OrderEnum::STATUS_REQUESTED,
            'user_id' => Auth::id(),
        ]);

        $order = $this->orderRepository->create($data);

        if (! $order->order_id) {
            throw new Exception;
        }

        return $order;
    }

    public function update(array $data): bool
    {
        $orderId = $data['order_id'];
        unset($data['order_id']);
        $update = $this->orderRepository->update($data, $orderId);

        if (! $update) {
            throw new Exception;
        }

        return true;
    }

    public function updateStatus(array $data): bool
    {
        $update = $this->orderRepository->updateMultiple(['status' => $data['status']], ['order_id' => $data['order_id']]);

        if (! $update) {
            throw new Exception;
        }

        if (config('mail.active')) {
            $order = $this->orderRepository->find($data['order_id']);
            $email = $order->user->email;
            $order->status == OrderEnum::STATUS_APPROVED ? Mail::to($email)->send(new Approved($order)) : Mail::to($email)->send(new Reproved($order));
        }

        return true;
    }

    public function checkOrderIsApproved(int $orderId): bool
    {
        return $this->orderRepository->checkOrderIsApproved($orderId);
    }
}
