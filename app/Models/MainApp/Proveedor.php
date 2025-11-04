<?php

namespace App\Models\MainApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\MainApp\Material;
use App\Models\MainApp\MaterialKilo;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';
    protected $primaryKey = 'id_proveedor';
    public $incrementing = false;   
    protected $keyType = 'int';

    protected $fillable = ['id_proveedor', 'nombre_proveedor', 'familia', 'subfamilia'];

    public function materiales(): HasMany
    {
        return $this->hasMany(Material::class, 'proveedor_id', 'id_proveedor');
    }

    public function materialKilos(): HasMany
    {
        return $this->hasMany(MaterialKilo::class, 'proveedor_id', 'id_proveedor');
    }
}
