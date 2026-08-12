<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
    ];

    public function feeTypes()
    {
        return $this->hasMany(FeeType::class);
    }

    public function feeCategory(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class);
    }
}
