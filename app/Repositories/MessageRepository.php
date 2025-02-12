<?php

namespace App\Repositories;

use App\Models\Message;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageRepository
{
    /**
     * @param int $id
     * @return Message|null
     */
    public function findById(int $id): ?Message
    {
        return Message::find($id);
    }

    /**
     * @param array $data
     * @return Message
     */
    public function saveMessage(array $data): Message
    {
        return Message::create($data);
    }

    /**
     * Получает список сообщений с пагинацией.
     *
     * @param int $perPage Количество сообщений на странице.
     * @param int $page Номер страницы.
     * @return LengthAwarePaginator
     */
    public function paginateMessages(int $perPage, int $page): LengthAwarePaginator
    {
        return Message::query()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
