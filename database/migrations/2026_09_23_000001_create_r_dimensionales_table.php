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
        if (!Schema::hasTable('r_dimensionales')) {
            Schema::create('r_dimensionales', function (Blueprint $table) {
                $table->id();
                $table->string('ot_id');
                $table->string('clase');
                $table->string('nombre_moldura')->nullable();
                $table->string('cliente')->nullable();
                $table->string('moldura_numero')->nullable();
                $table->string('codigo_formato')->default('4CAL-02');
                $table->integer('nivel_revision')->default(0);
                $table->date('fecha_elaboracion')->nullable();
                $table->date('fecha_revision')->nullable();
                $table->date('fecha_aprobacion')->nullable();
                $table->integer('cantidad_pedido')->default(0);
                $table->integer('cantidad_consignacion')->default(0);
                $table->integer('cantidad_inspeccionada')->default(0);
                $table->float('porcentaje_inspeccion')->default(0);
                $table->unsignedBigInteger('inspector_id')->nullable();
                $table->string('archivo_excel_ruta')->nullable();
                $table->string('archivo_excel_nombre')->nullable();
                $table->text('observaciones')->nullable();
                $table->string('estado')->default('en_proceso');
                $table->timestamps();

                $table->foreign('inspector_id')->references('id')->on('users')->nullOnDelete();
                $table->index(['ot_id', 'clase']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('r_dimensionales');
    }
};
