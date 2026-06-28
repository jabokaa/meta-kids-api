<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('metas', function (Blueprint $table) {
            $table->foreignId('membro_id')->nullable()->constrained('membros')->nullOnDelete()->after('imagem');
            $table->foreignId('grupo_id')->nullable()->constrained('grupos')->nullOnDelete()->after('membro_id');
        });
    }

    public function down(): void
    {
        Schema::table('metas', function (Blueprint $table) {
            $table->dropForeign(['membro_id']);
            $table->dropForeign(['grupo_id']);
            $table->dropColumn(['membro_id', 'grupo_id']);
        });
    }
};
