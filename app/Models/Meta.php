<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tag', 'data_fim', 'descricao', 'nome', 'imagem'])]
class Meta extends Model
{
    protected $casts = [
        'data_fim' => 'date',
    ];

    public function registros(): HasMany
    {
        return $this->hasMany(Registro::class);
    }
}