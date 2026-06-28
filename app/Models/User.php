<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['login', 'codigo', 'senha'])]
#[Hidden(['senha'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    public $timestamps = true;

    protected $casts = [
        'senha' => 'hashed',
    ];

    public function getAuthPassword(): string
    {
        return $this->senha;
    }

    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'usuarios_grupos');
    }
}
