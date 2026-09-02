<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade los campos asignados por el rol Master a la tabla `orden_trabajo`.
     */
    public function up(): void
    {
        Schema::table('orden_trabajo', function (Blueprint $table) {
            $table->string('orden_compra')->nullable()->after('prioridad');
            $table->string('cliente')->nullable()->after('orden_compra');
            $table->string('nombre_producto')->nullable()->after('cliente');
            $table->integer('cantidad')->nullable()->after('nombre_producto');
            $table->string('proveedor_material')->nullable()->after('cantidad');
            $table->string('material')->nullable()->after('proveedor_material');
            $table->date('fecha_entrega_fundicion')->nullable()->after('material');
            $table->string('semana_entrega_cliente')->nullable()->after('fecha_entrega_fundicion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_trabajo', function (Blueprint $table) {
            $table->dropColumn([
                'orden_compra',
                'cliente',
                'nombre_producto',
                'cantidad',
                'proveedor_material',
                'material',
                'fecha_entrega_fundicion',
                'semana_entrega_cliente',
            ]);
        });
    }
};
