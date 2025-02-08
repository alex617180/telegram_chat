<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class TelegramLongPollCommand extends Command
{
    protected $signature = 'telegram:longpoll';
    protected $description = 'Запуск long polling для получения обновлений от Telegram';

    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    public function handle()
    {
        $this->info('Запущен процесс long polling для Telegram');

        $offset = 0;
        while (true) {
            try {
                $updates = $this->telegramService->getUpdates($offset);
                foreach ($updates as $update) {
                    // Обработка полученного обновления
                    $this->telegramService->handleUpdate($update);
                    // Обновляем offset, чтобы не получать одно и то же сообщение снова
                    $offset = $update['update_id'] + 1;
                }
            } catch (\Exception $e) {
                Log::error('Ошибка long polling: ' . $e->getMessage());
                // Можно сделать задержку перед повторной попыткой
                sleep(1);
            }
        }
    }
}
