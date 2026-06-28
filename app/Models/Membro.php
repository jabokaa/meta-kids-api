<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nome', 'avatar', 'cor', 'grupo_id'])]
class Membro extends Model
{
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function metas(): HasMany
    {
        return $this->hasMany(Meta::class);
    }
}