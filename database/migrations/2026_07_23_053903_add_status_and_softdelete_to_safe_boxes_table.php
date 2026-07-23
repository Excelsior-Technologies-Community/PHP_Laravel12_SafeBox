<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('safe_boxes', function (Blueprint $table) {

            if (!Schema::hasColumn('safe_boxes', 'status')) {
                $table->enum('status', [
                    'Active',
                    'Locked',
                    'Archived'
                ])->default('Active')->after('secret');
            }

            if (!Schema::hasColumn('safe_boxes', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('safe_boxes', function (Blueprint $table) {

            if (Schema::hasColumn('safe_boxes', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('safe_boxes', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
