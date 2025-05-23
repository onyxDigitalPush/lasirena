<?php

namespace App\Models\MainApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MainApp\Material;
use App\Models\MainApp\MaterialKilo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    use HasFactory;  // importante si quieres usar factories

    protected $table = 'proveedores';

    protected $fillable = ['nombre'];

    public function materiales(): HasMany
    {
        return $this->hasMany(Material::class, 'proveedor_id');
    }

    public function materialKilos(): HasMany
    {
        return $this->hasMany(MaterialKilo::class, 'proveedor_id');
    }
}
