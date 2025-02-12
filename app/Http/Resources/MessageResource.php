<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'telegram_chat_id'    => $this->telegram_chat_id,
            'telegram_message_id' => $this->telegram_message_id,
            'telegram_first_name' => $this->telegram_first_name,
            'datetime'            => $this->datetime,
            'text'                => $this->text,
            'is_from_guest'       => (bool) $this->is_from_guest,
            'reply_to_message_id' => $this->reply_to_message_id,
            'created_at'          => $this->created_at,
        ];
    }
}
