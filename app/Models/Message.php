<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'telegram_chat_id',
        'telegram_message_id',
        'user_id',
        'text',
        'datetime',
        'is_from_guest',
        'reply_to_message_id',
        'data',
    ];

    protected $casts = [
        'data' => 'json',
    ];

    /**
     * Get the user that owns the message.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
