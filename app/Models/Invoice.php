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
        'consignee_id',
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
        'supplied_by_name',
        'supplied_by_gstin',
        'supplied_by_invoice_no',
        'supplied_by_cgst',
        'supplied_by_sgst',
        'supplied_by_igst',
        'drawback_details',
        'epcg_details',
        'scheme_party_name',
        'containers_json',
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
        'containers_json' => 'array',
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
     * Relationship to the Consignee master record.
     */
    public function consignee(): BelongsTo
    {
        return $this->belongsTo(Consignee::class, 'consigner_id');
    }

    /**
     * Relationship to the Consigner master record (backward compatibility).
     */
    public function consigner(): BelongsTo
    {
        return $this->belongsTo(Consigner::class, 'consigner_id');
    }

    /**
     * Consignee ID accessor.
     */
    public function getConsigneeIdAttribute(): ?int
    {
        return $this->attributes['consigner_id'] ?? null;
    }

    /**
     * Consignee ID mutator.
     */
    public function setConsigneeIdAttribute(?int $value): void
    {
        $this->attributes['consigner_id'] = $value;
    }
}
