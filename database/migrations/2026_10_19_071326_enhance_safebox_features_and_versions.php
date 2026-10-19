<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add Master Vault PIN to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('vault_pin')->nullable()->after('password');
        });

        // 2. Add Category, Extra payload, and Expiration to safe_boxes table
        Schema::table('safe_boxes', function (Blueprint $table) {
            $table->string('category', 50)->default('Note')->after('title'); // Password, Card, API_Key, Note
            $table->text('meta_data')->nullable()->after('secret'); // Encrypted JSON for username, url, card number, exp, cvv, etc.
            $table->date('expires_at')->nullable()->after('status');
        });

        // 3. Create safe_box_versions table for Password Version History
        Schema::create('safe_box_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('safe_box_id')->constrained('safe_boxes')->onDelete('cascade');
            $table->text('secret'); // Encrypted old secret
            $table->text('meta_data')->nullable(); // Encrypted old metadata
            $table->string('reason')->default('Password Updated');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('safe_box_versions');

        Schema::table('safe_boxes', function (Blueprint $table) {
            $table->dropColumn(['category', 'meta_data', 'expires_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('vault_pin');
        });
    }
};
