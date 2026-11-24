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
        if (!Schema::hasTable('sync_checkpoints')) {
            Schema::create('sync_checkpoints', function (Blueprint $table) {
                $table->id();
                $table->string('channel', 20)->default('push'); // 'push' o 'pull'
                $table->string('table_name', 100);
                $table->timestamp('last_synced_at')->nullable();
                $table->unsignedBigInteger('last_synced_id')->nullable();
                $table->unsignedInteger('records_count')->default(0);
                $table->string('status', 30)->default('idle'); // 'idle', 'in_progress', 'success', 'error'
                $table->text('last_error')->nullable();
                $table->timestamps();

                $table->unique(['channel', 'table_name']);
                $table->index(['channel', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_checkpoints');
    }
};

