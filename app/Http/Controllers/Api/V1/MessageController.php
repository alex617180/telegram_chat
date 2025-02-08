<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Services\TelegramService;

class MessageController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Получение списка сообщений.
     *
     */
    public function index()
    {
        $messages = Message::all();

        return response()->json($messages);
    }

    /**
     * Отправка ответа гостю.
     *
     */
    public function reply(Request $request, int $id)
    {
        $request->validate([
            'text' => 'required|string',
            'with_reply' => 'integer',
        ]);

        $replyText = $request->input('text');

        $replyToMessageId = null;

        $message = Message::findOrFail($id);

        if ($request->input('with_reply', null)) $replyToMessageId = $message->telegram_message_id;

        $response = $this->telegramService->sendMessage($message->telegram_chat_id, $replyText, $replyToMessageId);

        Message::create([
            'telegram_chat_id'     => $message->telegram_chat_id,
            'telegram_message_id'  => $response['result']['message_id'] ?? null,
            'telegram_first_name' => $messageData['chat']['first_name'] ?? '',
            // 'user_id'              => auth()->id() ?? null,
            'datetime'             => date('Y-m-d H:i:s', $response['result']['date']),
            'text'                 => $request->input('text'),
            'is_from_guest'        => false,
            'data'                 => $response['result'],
            'reply_to_message_id'  => $id,

        ]);


        return response()->json(['status' => 'success', 'response' => $response]);
    }
}
