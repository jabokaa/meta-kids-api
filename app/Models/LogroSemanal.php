<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['membro_id', 'meta_id', 'semana_inicio'])]
class LogroSemanal extends Model
{
    protected $casts = [
        'semana_inicio' => 'date',
    ];

    public function membro(): BelongsTo
    {
        return $this->belongsTo(Membro::class);
    }

    public function meta(): BelongsTo
    {
        return $this->belongsTo(Meta::class);
    }
}
