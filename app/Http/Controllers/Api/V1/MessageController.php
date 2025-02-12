<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetMessagesRequest;
use App\Http\Requests\ReplyRequest;
use App\Http\Resources\MessageResource;
use App\Http\Resources\ReplyResource;
use App\Repositories\MessageRepository;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

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
    private const PER_PAGE = 50;

    public function __construct(
        private MessageRepository $messageRepository,
        private TelegramService $telegramService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/messages",
     *     summary="Получение списка сообщений",
     *     tags={"Сообщения"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы (по умолчанию 1)",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Количество сообщений на странице (по умолчанию 50, максимум 1000)",
     *         required=false,
     *         @OA\Schema(type="integer", default=50, maximum=1000)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список сообщений с пагинацией",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=14),
     *                     @OA\Property(property="telegram_chat_id", type="integer", example=5975223022),
     *                     @OA\Property(property="telegram_message_id", type="integer", example=19),
     *                     @OA\Property(property="telegram_first_name", type="string", nullable=true, example="John"),
     *                     @OA\Property(property="datetime", type="string", format="date-time", example="2025-02-12 07:19:14"),
     *                     @OA\Property(property="text", type="string", example="HIII!!!"),
     *                     @OA\Property(property="is_from_guest", type="boolean", example=false),
     *                     @OA\Property(property="reply_to_message_id", type="integer", nullable=true, example=12),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-12 12:19:14")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string", example="https://telegramchat.loc/api/v1/messages?page=1"),
     *                 @OA\Property(property="last", type="string", example="https://telegramchat.loc/api/v1/messages?page=1"),
     *                 @OA\Property(property="prev", type="string", nullable=true, example=null),
     *                 @OA\Property(property="next", type="string", nullable=true, example=null)
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="path", type="string", example="https://telegramchat.loc/api/v1/messages"),
     *                 @OA\Property(property="per_page", type="integer", example=50),
     *                 @OA\Property(property="to", type="integer", example=13),
     *                 @OA\Property(property="total", type="integer", example=13),
     *                 @OA\Property(
     *                     property="links",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="url", type="string", nullable=true, example=null),
     *                         @OA\Property(property="label", type="string", example="&laquo; Previous"),
     *                         @OA\Property(property="active", type="boolean", example=false)
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(GetMessagesRequest $request)
    {
        $perPage = $request->input('per_page', self::PER_PAGE);
        $page = $request->input('page', 1);

        $messages = $this->messageRepository->paginateMessages($perPage, $page);

        return MessageResource::collection($messages)->response();
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
     *                 type="boolean",
     *                 nullable=true,
     *                 example=true,
     *                 description="Если указано true, сообщение отправляется как ответ в Telegram"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ответ успешно отправлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="object",
     *                 @OA\Property(property="telegram_message_id", type="integer", example=12345),
     *                 @OA\Property(property="chat_id", type="integer", example=987654),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="text", type="string", example="Ваш ответ"),
     *                 @OA\Property(property="datetime", type="string", format="date-time", example="2024-02-12 15:30:00")
     *             )
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
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка отправки сообщения",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Ошибка отправки сообщения")
     *         )
     *     )
     * )
     */
    public function reply(ReplyRequest $request, int $id): JsonResponse
    {
        $message = $this->messageRepository->findById($id);

        if (!$message) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Сообщение не найдено'
            ], 404);
        }

        $replyToMessageId = $request->boolean('with_reply') ? $message->telegram_message_id : null;

        $response = $this->telegramService->sendMessage(
            $message->telegram_chat_id,
            $request->input('text'),
            $replyToMessageId
        );

        if (empty($response) || isset($response['error'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка отправки сообщения'
            ], 500);
        }
        
        $this->messageRepository->saveMessage([
            'telegram_chat_id'    => $message->telegram_chat_id,
            'telegram_message_id' => $response['result']['message_id'] ?? null,
            'telegram_first_name' => $response['result']['chat']['first_name'] ?? '',
            // 'user_id'              => auth()->id() ?? null,
            'datetime'            => Carbon::createFromTimestamp($response['result']['date']),
            'text'                => $request->input('text'),
            'is_from_guest'       => false,
            'data'                => $response['result'],
            'reply_to_message_id' => $id,
        ]);

        return response()->json([
            'status'   => 'success',
            'message'  => new ReplyResource($response),
        ]);
    }
}
