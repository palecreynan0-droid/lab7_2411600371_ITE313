<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name', 'contact_person', 'email', 'phone', 'address',
        'payment_terms', 'lead_time_days', 'minimum_order_amount', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'minimum_order_amount' => 'decimal:2',
    ];

    // Relationships
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // purchaseOrders() is added once the PurchaseOrder model exists (Part 2)
    // public function purchaseOrders() { return $this->hasMany(PurchaseOrder::class); }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithLeadTime($query, $days)
    {
        return $query->where('lead_time_days', '<=', $days);
    }

    // Accessors
    public function getFullContactAttribute(): string
    {
        return "{$this->contact_person} ({$this->email}, {$this->phone})";
    }
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function getPerformanceRatingAttribute(): float
    {
        $received = $this->purchaseOrders()->where('status', 'received')->get();

        if ($received->isEmpty()) {
            return 0;
        }

        $onTime = $received->filter(fn ($po) =>
            $po->expected_delivery_date && $po->received_date
            && $po->received_date->lte($po->expected_delivery_date)
        )->count();

        return round($onTime / $received->count() * 100, 1);
    }
}