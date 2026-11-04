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
        Schema::create('view_performance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('url', 255)->index();
            $table->string('ruta', 100)->nullable()->index();
            $table->string('vista', 150)->nullable();
            $table->string('metodo', 10)->default('GET');
            $table->decimal('tiempo_ms', 10, 2)->index();
            $table->string('matricula', 50)->nullable()->index();
            $table->integer('perfil')->nullable();
            $table->smallInteger('status_code')->default(200);
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('view_performance_logs');
    }
};

