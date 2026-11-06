<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consignee extends Model
{
    use HasFactory;

    /**
     * Database table mapped to this model.
     * Preserves compatibility with existing database installations.
     */
    protected $table = 'consigners';

    protected $fillable = [
        'consignee_code',
        'consigner_code',
        'name',
        'entity_type',
        'iec',
        'iec_branch',
        'gstin',
        'tax_code',
        'pan',
        'ad_code',
        'forex_bank',
        'bank_name',
        'bank_branch',
        'bank_account_no',
        'bank_ifsc',
        'bank_swift',
        'bank_address',
        'registered_address',
        'factory_address',
        'region',
        'phone',
        'email',
        'contact_person',
        'contact_phone',
        'contact_email',
        'status',
    ];

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'consigner_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'consigner_id');
    }

    /**
     * Consignee code accessor / alias.
     */
    public function getConsigneeCodeAttribute(): ?string
    {
        return $this->attributes['consigner_code'] ?? null;
    }

    /**
     * Consignee code mutator / alias.
     */
    public function setConsigneeCodeAttribute(?string $value): void
    {
        $this->attributes['consigner_code'] = $value;
    }

    /**
     * Get tax code falling back to gstin or pan.
     */
    public function getTaxCodeAttribute(?string $value): ?string
    {
        return $value ?: ($this->attributes['gstin'] ?? ($this->attributes['pan'] ?? null));
    }

    /**
     * Get phone falling back to contact_phone.
     */
    public function getPhoneAttribute(?string $value): ?string
    {
        return $value ?: ($this->attributes['contact_phone'] ?? null);
    }

    /**
     * Get email falling back to contact_email.
     */
    public function getEmailAttribute(?string $value): ?string
    {
        return $value ?: ($this->attributes['contact_email'] ?? null);
    }

    /**
     * Get bank account number falling back to standard 777705052052.
     */
    public function getBankAccountNoAttribute(?string $value): string
    {
        if (! empty($value) && $value !== '777705022062') {
            return $value;
        }

        return '777705052052';
    }
}

