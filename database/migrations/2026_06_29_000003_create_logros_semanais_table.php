<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logros_semanais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membro_id')->constrained('membros')->cascadeOnDelete();
            $table->foreignId('meta_id')->constrained('metas')->cascadeOnDelete();
            $table->date('semana_inicio');
            $table->timestamps();

            // Uma estrela por meta por semana
            $table->unique(['meta_id', 'semana_inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logros_semanais');
    }
};
