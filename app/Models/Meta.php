<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tag',
    'data_fim',
    'descricao',
    'nome',
    'vezes_por_semana',
    'max_vezes_por_dia',
    'dia_inicio_semana',
    'imagem',
    'membro_id',
    'grupo_id',
])]
class Meta extends Model
{
    protected $casts = [
        'data_fim' => 'date',
        'vezes_por_semana' => 'integer',
        'max_vezes_por_dia' => 'integer',
        'dia_inicio_semana' => 'integer',
    ];

    public function membro(): BelongsTo
    {
        return $this->belongsTo(Membro::class);
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function registros(): HasMany
    {
        return $this->hasMany(Registro::class);
    }

    public function logrosSemanas(): HasMany
    {
        return $this->hasMany(LogroSemanal::class);
    }
}