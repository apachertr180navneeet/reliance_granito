<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consigner extends Model
{
    use HasFactory;

    protected $fillable = [
        'consigner_code',
        'name',
        'entity_type',
        'iec',
        'iec_branch',
        'gstin',
        'pan',
        'ad_code',
        'forex_bank',
        'registered_address',
        'factory_address',
        'region',
        'contact_person',
        'contact_phone',
        'contact_email',
        'status',
    ];

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
