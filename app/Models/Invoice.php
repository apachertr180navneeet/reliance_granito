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
        'invoice_format',
        'delivery_term',
        'consigner_id',
        'consigner_name',
        'consignee_mode',
        'consignee_name',
        'consignee_address',
        'consignee_tax',
        'consignee_phone',
        'consignee_email',
        'buyer_name',
        'buyer_address',
        'buyer_tax',
        'buyer_phone',
        'buyer_email',
        'notify_parties',
        'container_number',
        'trailer_number',
        'container_type',
        'no_of_containers',
        'gross_weight',
        'net_weight',
        'total_packages',
        'package_type',
        'region',
        'lut_no',
        'iso_certification',
        'buyer_po',
        'buyer_other',
        'shipping_logistics',
        'items',
        'packing_list_details',
        'total_quantity',
        'total_amount',
        'rounded_amount',
        'amount_in_words',
        'currency',
        'exchange_rate',
        'igst_rate',
        'taxable_value_inr',
        'igst_amount_inr',
        'status',
    ];

    protected $casts = [
        'items' => 'array',
        'buyer_other' => 'array',
        'shipping_logistics' => 'array',
        'notify_parties' => 'array',
        'packing_list_details' => 'array',
        'total_quantity' => 'float',
        'total_amount' => 'float',
        'rounded_amount' => 'float',
        'exchange_rate' => 'float',
        'igst_rate' => 'float',
        'taxable_value_inr' => 'float',
        'igst_amount_inr' => 'float',
        'gross_weight' => 'float',
        'net_weight' => 'float',
        'no_of_containers' => 'integer',
        'total_packages' => 'integer',
    ];

    /**
     * Relationship to the Consigner master record.
     */
    public function consigner(): BelongsTo
    {
        return $this->belongsTo(Consigner::class);
    }
}
