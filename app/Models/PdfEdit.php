<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfEdit extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_name',
        'annotations',
        'edits_count',
        'user_id',
    ];

    protected $casts = [
        'annotations' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
