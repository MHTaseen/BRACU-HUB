<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            // Faculty-entered quiz/assignment marks
            $table->decimal('marks_obtained', 5, 2)->nullable()->after('file_path');
            $table->decimal('total_marks', 5, 2)->nullable()->default(100)->after('marks_obtained');
        });
    }

    public function down(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropColumn(['marks_obtained', 'total_marks']);
        });
    }
};
