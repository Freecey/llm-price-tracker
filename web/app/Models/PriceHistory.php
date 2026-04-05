<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceHistory extends Eloquent
{
    protected $table = 'model_prices_history';
    
    protected $fillable = [
        'model_id', 'input_price_per_m', 'output_price_per_m', 
        'context_length', 'change_type', 'timestamp'
    ];

    protected $casts = [
        'input_price_per_m' => 'decimal:4',
        'output_price_per_m' => 'decimal:4',
        'timestamp' => 'datetime'
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(Model::class);
    }
}
