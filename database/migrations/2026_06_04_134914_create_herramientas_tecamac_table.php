<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Recrea desde cero las tablas herramientas_tecamac y herramientas_tecamac_imagenes.
     */
    public function up(): void
    {
        // Borrar primero la tabla hija (FK) y luego la padre
        Schema::dropIfExists('herramientas_tecamac_imagenes');
        Schema::dropIfExists('herramientas_tecamac');

        // ── Tabla principal ───────────────────────────────────────────────────
        Schema::create('herramientas_tecamac', function (Blueprint $table) {
            $table->id();

            // Procesos a los que pertenece la herramienta (JSON array, ej: ["Cepillado","Desbaste Exterior"])
            $table->json('proceso')->nullable();

            // Herramienta
            $table->string('nombre_herramienta')->nullable();
            $table->string('descripcion_herramienta')->nullable();
            $table->string('descripcion_inserto')->nullable();

            // Accesorio
            $table->string('nombre_accesorio')->nullable();            // nombre del accesorio
            $table->text('accesorios')->nullable();                    // descripción de accesorios

            // Cantidad en planta
            $table->unsignedInteger('cantidad_portaherramientas')->default(0);

            // Condiciones de corte (pulg.)
            $table->decimal('profundidad_corte', 8, 4)->nullable();
            $table->unsignedInteger('rpm')->nullable();
            $table->string('avances')->nullable();                     // ej. "0.012 AVANCE/MIN"

            // Stock
            $table->unsignedInteger('minimo')->nullable();
            $table->unsignedInteger('maximo')->nullable();

            // Control
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // ── Tabla de imágenes (N fotos por herramienta, por tipo) ─────────────
        Schema::create('herramientas_tecamac_imagenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('herramienta_id')
                  ->constrained('herramientas_tecamac')
                  ->onDelete('cascade');

            // Tipo de imagen:
            //   herramienta            → foto del inserto / herramienta
            //   accesorio              → foto del accesorio de la herramienta
            //   tornilleria            → foto de tornillería
            //   tornilleria_accesorio  → foto de accesorio de tornillería
            $table->enum('tipo', [
                'herramienta',
                'accesorio',
                'tornilleria',
                'tornilleria_accesorio',
                'imagen_fisica',
            ]);

            $table->string('nombre')->nullable();          // etiqueta de la foto
            $table->string('ruta');                        // ruta relativa en public/
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('herramientas_tecamac_imagenes');
        Schema::dropIfExists('herramientas_tecamac');
    }
};
