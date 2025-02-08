<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE_NAME = 'messages';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE_NAME)) {
            Schema::create(self::TABLE_NAME, function (Blueprint $table) {
                $table->id();
                $table->bigInteger('telegram_chat_id')->comment('ID чата в Telegram');
                $table->bigInteger('telegram_message_id')->comment('ID сообщения в Telegram');
                $table->unsignedBigInteger('user_id')->nullable()->comment('ID пользователя веб-сервиса');
                $table->text('text')->comment('Текст сообщения');
                $table->timestamp('datetime')->comment('Дата и время сообщения');
                $table->boolean('is_from_guest')->default(true)->comment('Флаг, что сообщение от гостя: true - да, false - нет');
                $table->bigInteger('reply_to_message_id')->nullable()->comment('ID сообщения, на которое дан ответ');
                $table->json('data')->nullable()->comment('Полный JSON-объект сообщения');

                $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            
                // В Postgres для updated_at используем DEFAULT + GENERATED выражение
                $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'))->useCurrentOnUpdate();
                // Для Mysql:
                // $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            });        
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
};
