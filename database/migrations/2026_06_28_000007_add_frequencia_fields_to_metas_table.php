<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('metas', function (Blueprint $table) {
            $table->unsignedTinyInteger('vezes_por_semana')->default(1)->after('nome');
            $table->unsignedTinyInteger('max_vezes_por_dia')->default(1)->after('vezes_por_semana');
            $table->unsignedTinyInteger('dia_inicio_semana')->nullable()->after('max_vezes_por_dia');
        });
    }

    public function down(): void
    {
        Schema::table('metas', function (Blueprint $table) {
            $table->dropColumn([
                'vezes_por_semana',
                'max_vezes_por_dia',
                'dia_inicio_semana',
            ]);
        });
    }
};
