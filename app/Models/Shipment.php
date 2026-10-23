<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_number',
        'consigner_id',
        'buyer_name',
        'buyer_address',
        'destination_port',
        'pol_name',
        'incoterms',
        'total_quantity',
        'total_packages',
        'package_type',
        'gross_weight',
        'net_weight',
        'container_number',
        'seal_number',
        'currency',
        'invoice_value',
        'status',
        'review_status',
    ];

    public function consigner(): BelongsTo
    {
        return $this->belongsTo(Consigner::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
