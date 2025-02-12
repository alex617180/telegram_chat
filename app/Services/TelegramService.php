<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Repositories\MessageRepository;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class TelegramService
{
    
    private $token;
    private int $timeout;
    private string $apiUrl;
    private const EXTRA_TIMEOUT = 5;

    public function __construct(
        private Client $httpClient,
        private MessageRepository $messageRepository
    )
    {
        $this->token = config('services.telegram.token');

        if (empty($this->token)) {
            throw new InvalidArgumentException('Telegram API token не задан в .env');
        }
        
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}/";
        $this->timeout = config('services.telegram.poll_timeout', 30);
    }

    /**
     * Получает обновления через long polling.
     *
     * @param int $offset Смещение обновлений.
     * @return array
     */
    public function getUpdates(int $offset = 0): array
    {
        try {
            $response = $this->httpClient->get($this->apiUrl . 'getUpdates', [
                'query' => [
                    'offset' => $offset,
                    'timeout' => $this->timeout,
                ],
                'timeout' => $this->timeout + self::EXTRA_TIMEOUT,
                'http_errors' => true,
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['result'] ?? [];
        } catch (RequestException|GuzzleException $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * Обработка входящего обновления.
     *
     * @param array $update
     * @return void
     */
    public function handleUpdate(array $update)
    {
        if (isset($update['message'])) {
            $messageData = $update['message'];

            $this->messageRepository->saveMessage([
                'telegram_chat_id' => $messageData['chat']['id'],
                'telegram_message_id' => $messageData['message_id'],
                'telegram_first_name' => $messageData['chat']['first_name'] ?? '',
                'text' => $messageData['text'] ?? '',
                'datetime' => Carbon::createFromTimestamp($messageData['date']),
                'is_from_guest' => true,
                'data' => $messageData,
            ]);
        }
    }

    /**
     * Отправка сообщения через Telegram API с повторами в случае ошибок.
     *
     * @param int      $chatId           Идентификатор чата, куда отправляется сообщение.
     * @param string   $text             Текст сообщения.
     * @param int|null $replyToMessageId (Опционально) Идентификатор сообщения, на которое производится ответ.
     * @return array|null
     */
    public function sendMessage(int $chatId, string $text, ?int $replyToMessageId = null): ?array
    {
        $params = ['chat_id' => $chatId, 'text' => $text];
        if ($replyToMessageId !== null) {
            $params['reply_to_message_id'] = $replyToMessageId;
        }
    
        $attempts = 3;  // Количество повторов запроса
        $delay = 500000; // 500 мс (0.5 сек)
    
        while ($attempts--) {
            try {
                $response = $this->httpClient->post($this->apiUrl . 'sendMessage', [
                    'json'       => $params,
                    'timeout'    => $this->timeout + self::EXTRA_TIMEOUT,
                    'http_errors'=> true,
                ]);
    
                $responseData = json_decode($response->getBody(), true);
    
                if (!isset($responseData['ok']) || $responseData['ok'] === false) {
                    Log::warning("Ошибка Telegram API: " . json_encode($responseData));
                    return ['error' => $responseData['description'] ?? 'Неизвестная ошибка'];
                }
    
                return $responseData;
            } catch (RequestException|GuzzleException $exception) {
                Log::warning("Ошибка запроса к Telegram, попытка #" . (3 - $attempts), [
                    'error' => $exception->getMessage(),
                ]);
                usleep($delay);
            }
        }
    
        return ['error' => 'Ошибка связи с Telegram API. Попробуйте позже.'];
    }
    
    /**
     * Обработка исключений при запросе к Telegram API.
     *
     * @param GuzzleException $exception Исключение Guzzle.
     * @return array
     */
    private function handleException(GuzzleException $exception): array
    {
        $errorMessage = 'Ошибка запроса к Telegram API: ' . $exception->getMessage();
        
        if ($exception instanceof RequestException && $exception->hasResponse()) {
            $response = $exception->getResponse();
            $body = (string) $response->getBody();
            $errorMessage .= " | HTTP {$response->getStatusCode()}: $body";
        }
    
        Log::error($errorMessage);
    
        return ['error' => 'Ошибка связи с Telegram API. Попробуйте позже.'];
    }
}
