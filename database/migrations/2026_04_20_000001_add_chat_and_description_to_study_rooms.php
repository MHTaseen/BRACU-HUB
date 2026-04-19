<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_rooms', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->json('chat_messages')->nullable()->after('notes_data');
        });

        // Fix existing whiteboard_data and notes_data columns from JSON to TEXT
        // SQLite does not support column type changes, but since we stored plain
        // strings in a JSON column, the data is already compatible as TEXT.
        // We simply remove the JSON cast in the model (no DDL needed for SQLite).
    }

    public function down(): void
    {
        Schema::table('study_rooms', function (Blueprint $table) {
            $table->dropColumn(['description', 'chat_messages']);
        });
    }
};
