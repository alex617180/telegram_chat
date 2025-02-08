<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Services\TelegramService;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="Telegram Chat API",
 *         version="1.0.0",
 *         description="API для взаимодействия с Telegram ботом"
 *     ),
 *     @OA\Server(
 *         url="https://telegramchat.loc/",
 *         description="Localhost API server"
 *     )
 * )
 */
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
     * @OA\Get(
     *     path="/api/v1/messages",
     *     summary="Получение списка сообщений",
     *     tags={"Сообщения"},
     *     @OA\Response(
     *         response=200,
     *         description="Список сообщений"
     *     )
     * )
     */
    public function index()
    {
        $messages = Message::all();

        return response()->json($messages);
    }

    /**
     * Отправка ответа гостю.
     *
     * @OA\Post(
     *     path="/api/v1/messages/{id}/reply",
     *     summary="Отправка ответа гостю",
     *     tags={"Сообщения"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID сообщения, на которое отправляется ответ",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"text"},
     *             @OA\Property(property="text", type="string", example="Ваш ответ"),
     *             @OA\Property(
     *                 property="with_reply",
     *                 type="integer",
     *                 nullable=true,
     *                 example=1,
     *                 description="Если указано (1), сообщение отправляется как ответ в Telegram"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ответ успешно отправлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="response", type="object", example={"message_id": 12345, "date": 1700000000})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Поле 'text' обязательно")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Сообщение не найдено",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Сообщение не найдено")
     *         )
     *     )
     * )
     */
    public function reply(Request $request, int $id)
    {
        $request->validate([
            'text' => 'required|string',
            'with_reply' => 'integer',
        ]);

        $replyText = $request->input('text');

        $replyToMessageId = null;

        $message = Message::find($id);

        if (!$message) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Сообщение не найдено'
            ], 404);
        }


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
