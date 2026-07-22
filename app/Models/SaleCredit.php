<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleCredit extends Model
{
    protected $fillable = [
        'sale_id',
        'customer_id',
        'total_usd',
        'initial_payment_usd',
        'financed_usd',
        'installments_count',
        'installment_gap_days',
        'late_fee_usd',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_usd' => 'decimal:4',
            'initial_payment_usd' => 'decimal:4',
            'financed_usd' => 'decimal:4',
            'late_fee_usd' => 'decimal:4',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(SaleCreditInstallment::class);
    }

    public function resolveStatus(): string
    {
        $installments = $this->installments;

        if ($installments->isEmpty()) {
            return 'paid';
        }

        $totalDue = (float) $installments->sum(function ($i) {
            return (float) $i->amount_usd + (float) $i->late_fee_usd;
        });

        $totalPaid = (float) $installments->sum('paid_usd');

        if ($totalPaid <= 0) {
            return 'pending';
        }

        if ($totalPaid >= $totalDue) {
            return 'paid';
        }

        return 'partial';
    }
}
