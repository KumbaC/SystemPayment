<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'code_encrypted', 'note', 'amount', 'whatsapp', 'expires_at', 'active',
    ];

    protected $dates = ['expires_at'];

    public function setCodeAttribute($value)
    {
        $this->attributes['code_encrypted'] = Crypt::encryptString($value);
    }

    public function getCodeAttribute()
    {
        if (empty($this->code_encrypted)) return null;
        try {
            return Crypt::decryptString($this->code_encrypted);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
