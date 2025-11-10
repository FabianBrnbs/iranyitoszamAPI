<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostalCode extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'settlement', 'county_id'];

    protected $with = ['county'];

    public function county(): BelongsTo
    {
        return $this->belongsTo(County::class);
    }
}