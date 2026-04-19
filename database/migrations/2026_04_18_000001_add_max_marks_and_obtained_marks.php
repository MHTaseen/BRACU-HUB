<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds max_marks to assignments (e.g. quiz out of 20, mid out of 30)
     * and marks_obtained to assignment_submissions (faculty-entered marks).
     */
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // Maximum marks possible for this assessment (e.g. 100, 20, 30)
            $table->decimal('max_marks', 6, 2)->default(100)->after('weight');
        });

        Schema::table('assignment_submissions', function (Blueprint $table) {
            // Marks awarded by faculty — null means not yet graded
            $table->decimal('marks_obtained', 6, 2)->nullable()->after('file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn('max_marks');
        });

        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropColumn('marks_obtained');
        });
    }
};
