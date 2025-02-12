<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReplyResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'telegram_message_id'   => $this->resource['result']['message_id'] ?? null,
            'chat_id'      => $this->resource['result']['chat']['id'] ?? null,
            'first_name'   => $this->resource['result']['chat']['first_name'] ?? null,
            'text'         => $this->resource['result']['text'] ?? null,
            'datetime'     => isset($this->resource['result']['date']) 
                                ? now()->setTimestamp($this->resource['result']['date'])->toDateTimeString() 
                                : null,
        ];
    }
}
