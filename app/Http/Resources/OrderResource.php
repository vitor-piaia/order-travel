<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class OrderResource extends JsonResource
{
    public $statusCode;

    public function __construct($resource, $statusCode = Response::HTTP_OK)
    {
        parent::__construct($resource);
        $this->statusCode = $statusCode;
    }
    public function toArray(Request $request): array
    {
        return [
            'orderId' => $this->order_id,
            'name' => $this->name,
            'destiny' => $this->destiny,
            'departureDate' => $this->departure_date,
            'returnDate' => $this->return_date,
            'status' => $this->status
        ];
    }
}
