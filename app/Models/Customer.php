<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'document_type',
        'document_number',
        'phone',
        'email',
        'address',
        'active',
    ];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function fullDocument(): string
    {
        if (! $this->document_number) {
            return '';
        }

        return "{$this->document_type}-{$this->document_number}";
    }
}
