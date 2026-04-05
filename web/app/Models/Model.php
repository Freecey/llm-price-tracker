<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Model extends Eloquent
{
    protected $table = 'models';
    
    protected $fillable = [
        'openrouter_id', 'name', 'status', 'specs', 'context_length', 
        'max_tokens', 'modality', 'input_modalities', 'output_modalities', 
        'provider_name', 'quantization', 'top_provider_max_completion_tokens'
    ];

    protected $casts = [
        'specs' => 'array',
        'links' => 'array',
        'input_modalities' => 'array',
        'output_modalities' => 'array',
    ];

    public function priceHistory(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }
}
