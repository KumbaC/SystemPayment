<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayableInvoice extends Model
{
    protected $fillable = [
        'supplier_id',
        'reference',
        'issue_date',
        'due_date',
        'amount_ves',
        'paid_ves',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'amount_ves' => 'decimal:2',
            'paid_ves' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PayablePayment::class);
    }

    public function pendingAmount(): float
    {
        return max(0, (float) $this->amount_ves - (float) $this->paid_ves);
    }

    public function resolveStatus(): string
    {
        if ((float) $this->paid_ves <= 0) {
            return 'pending';
        }

        if ((float) $this->paid_ves >= (float) $this->amount_ves) {
            return 'paid';
        }

        return 'partial';
    }
}
