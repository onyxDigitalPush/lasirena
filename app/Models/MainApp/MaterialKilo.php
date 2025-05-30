<?php

namespace App\Models\MainApp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialKilo extends Model
{
    protected $table = 'material_kilos';

    protected $fillable = ['material_id','codigo_material', 'proveedor_id', 'mes', 'año', 'total_kg'];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
}
