<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['data', 'hora', 'emoticon', 'meta_id'])]
class Registro extends Model
{
    protected $casts = [
        'data' => 'date',
        'hora' => 'datetime:H:i:s',
    ];

    public function meta(): BelongsTo
    {
        return $this->belongsTo(Meta::class);
    }
}