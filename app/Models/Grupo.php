<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nome', 'codigo', 'senha', 'avatar', 'cor'])]
class Grupo extends Model
{
    protected $casts = [
        'senha' => 'hashed',
    ];

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuarios_grupos');
    }

    public function membros(): HasMany
    {
        return $this->hasMany(Membro::class);
    }
}