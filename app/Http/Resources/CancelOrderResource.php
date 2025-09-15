<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class CancelOrderResource extends JsonResource
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
            'id' => $this->id,
            'message' => $this->message,
            'status' => $this->status,
            'order' => new OrderResource($this->order),
        ];
    }
}
