<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    protected $fillable = [
        'categoria_id',
        'user_id',
        'imagen',
        'titulo',
        'slug',
        'extracto',
        'contenido',
        'publicado',
        'publicado_en',
    ];
    protected $casts = [
        'publicado' => 'boolean',
        'publicado_en' => 'timestamp',
        'tags' => 'array',
    ];
    public function categoria() : BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
    public function autor() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function tags() : BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
