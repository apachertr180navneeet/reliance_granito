<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'consigner_id',
        'consigner_name',
        'container_number',
        'trailer_number',
        'buyer_po',
        'buyer_other',
        'shipping_logistics',
        'items',
        'total_quantity',
        'total_amount',
        'rounded_amount',
        'amount_in_words',
        'currency',
        'status',
    ];

    protected $casts = [
        'items' => 'array',
        'buyer_other' => 'array',
        'shipping_logistics' => 'array',
        'total_quantity' => 'float',
        'total_amount' => 'float',
        'rounded_amount' => 'float',
    ];

    /**
     * Relationship to the Consigner master record.
     */
    public function consigner(): BelongsTo
    {
        return $this->belongsTo(Consigner::class);
    }
}
