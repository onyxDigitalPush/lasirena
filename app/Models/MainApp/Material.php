<?php

namespace App\Models\MainApp;
use App\Models\MainApp\Proveedor;
use App\Models\MainApp\MaterialKilo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $table = 'materiales';

    protected $fillable = ['jerarquia', 'codigo', 'descripcion', 'proveedor_id', 'factor_conversion'];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function materialKilos(): HasMany
    {
        return $this->hasMany(MaterialKilo::class, 'material_id');
    }
}