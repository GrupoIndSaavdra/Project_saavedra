<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Tabla: scar_modelos
     * Almacena los datos del Formato SCAR (Solicitud de Acción Correctiva de Rechazo)
     * generado por Calidad cada vez que se rechaza un modelo de fundición.
     */
    public function up(): void
    {
        Schema::create('scar_modelos', function (Blueprint $table) {
            $table->id();

            // ── Relación con la OT / liberación ─────────────────────────────
            $table->string('ot', 200)->index();

            $table->string('no_scar', 50);

            $table->string('tipo_modelo', 50)->nullable();

            $table->string('codigo_modelo', 80)->nullable();

            // ── Proveedor ────────────────────────────────────────────────────
            $table->string('proveedor', 200)->nullable();

            // ── Cuerpo del SCAR ──────────────────────────────────────────────
            $table->text('descripcion_no_conformidad')->nullable();

            $table->text('causa_raiz')->nullable();

            $table->text('acciones_correctivas')->nullable();

            $table->date('fecha_emision')->nullable();

            $table->date('fecha_compromiso')->nullable();

            // ── Seguimiento ──────────────────────────────────────────────────
            $table->string('estatus', 30)->default('abierto');

            // ── Inspector / Calidad ──────────────────────────────────────────
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('user_nombre', 120)->nullable();

            // ── Datos Complementarios & Evidencias ───────────────────────────
            $table->string('cliente_empresa', 255)->default('Industrial Saavedra')->nullable();
            $table->string('area_solicitante', 150)->default('Calidad')->nullable();
            $table->string('nombre_solicitante', 255)->nullable();
            $table->string('nombre_moldura', 255)->nullable();
            
            $table->boolean('evidencia_reporte')->default(true);
            $table->boolean('evidencia_dibujos')->default(false);
            $table->boolean('evidencia_ayudas')->default(false);
            $table->boolean('evidencia_fotos')->default(false);
            $table->boolean('evidencia_otro')->default(false);
            
            $table->boolean('accion_regreso')->default(false);
            $table->boolean('accion_fabricacion')->default(false);
            $table->boolean('accion_otro')->default(false);
            $table->text('accion_otro_texto')->nullable();

            // ── PDF generado ─────────────────────────────────────────────────
            $table->string('pdf_filename', 255)->nullable();
            $table->string('pdf_firmado_filename', 255)->nullable();

            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scar_modelos');
    }
};
