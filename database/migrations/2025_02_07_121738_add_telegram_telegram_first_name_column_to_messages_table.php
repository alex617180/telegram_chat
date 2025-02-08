<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE_NAME = 'messages';
    private const COLUMN_NAME = 'telegram_first_name';
    private const COMMENT = 'Имя пользователя в Telegram';
    private const AFTER_FIELD_NAME = 'telegram_message_id';


    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable(self::TABLE_NAME)) {
            Schema::table(self::TABLE_NAME, function (Blueprint $table) {
                if (!Schema::hasColumn(self::TABLE_NAME, self::COLUMN_NAME)) {
                    $table->string(self::COLUMN_NAME)
                        ->after(self::AFTER_FIELD_NAME)
                        ->comment(self::COMMENT)
                        ->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable(self::TABLE_NAME)) {
            Schema::table(self::TABLE_NAME, function (Blueprint $table) {
                if (Schema::hasColumn(self::TABLE_NAME, self::COLUMN_NAME)) {
                    $table->dropColumn(self::COLUMN_NAME);
                }
            });
        }
    }
};
