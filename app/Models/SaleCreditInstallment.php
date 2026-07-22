<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleCreditInstallment extends Model
{
    protected $fillable = [
        'sale_credit_id',
        'installment_number',
        'due_date',
        'amount_usd',
        'paid_usd',
        'late_fee_usd',
        'late_fee_applied',
        'whatsapp_sent_at',
        'paid_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount_usd' => 'decimal:4',
            'paid_usd' => 'decimal:4',
            'late_fee_usd' => 'decimal:4',
            'late_fee_applied' => 'boolean',
            'whatsapp_sent_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function credit(): BelongsTo
    {
        return $this->belongsTo(SaleCredit::class, 'sale_credit_id');
    }

    public function pendingUsd(): float
    {
        return max(0, ((float) $this->amount_usd + (float) $this->late_fee_usd) - (float) $this->paid_usd);
    }

    public function resolveStatus(): string
    {
        $pending = $this->pendingUsd();

        if ($pending <= 0) {
            return 'paid';
        }

        if ((float) $this->paid_usd > 0) {
            return 'partial';
        }

        if ($this->due_date && $this->due_date->isPast()) {
            return 'overdue';
        }

        return 'pending';
    }
}
