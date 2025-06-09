<?php

namespace App\Models\MainApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProveedorMetric extends Model
{
    use HasFactory;
    
    protected $table = 'proveedor_metrics';
    
    protected $fillable = [
        'proveedor_id',
        'año',
        'mes',
        'rg1',
        'rl1',
        'dev1',
        'rok1',
        'ret1'
    ];
    
    protected $casts = [
        'rg1' => 'decimal:2',
        'rl1' => 'decimal:2',
        'dev1' => 'decimal:2',
        'rok1' => 'decimal:2',
        'ret1' => 'decimal:2'
    ];
    
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id', 'id_proveedor');
    }
}
