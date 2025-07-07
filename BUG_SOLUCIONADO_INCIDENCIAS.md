## 🐛 BUG IDENTIFICADO Y SOLUCIONADO: Error al guardar incidencia

### 📋 Problema
Al intentar guardar una incidencia, se mostraba el error: "Error al guardar la incidencia"

### 🔍 Diagnóstico
**Error específico encontrado en logs:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'id_proveedor' in 'where clause' 
(SQL: select * from `gp_ls_proveedor_metrics` where (`id_proveedor` = 74 and `año` = 2025 and `mes` = 1) limit 1)
```

### 🎯 Causa raíz
**Inconsistencia en nombres de campos:**
- Tabla `proveedores`: usa campo `id_proveedor`
- Tabla `proveedor_metrics`: usa campo `proveedor_id`
- El método `actualizarMetricasIncidencias` usaba incorrectamente `id_proveedor` para buscar en `proveedor_metrics`

### ✅ Solución aplicada

#### 1. **Corregido método `actualizarMetricasIncidencias`**
```php
// ANTES (incorrecto):
ProveedorMetric::updateOrCreate([
    'id_proveedor' => $id_proveedor,  // ❌ Campo inexistente
    'año' => $año,
    'mes' => $mes
]);

// DESPUÉS (corregido):
ProveedorMetric::updateOrCreate([
    'proveedor_id' => $id_proveedor,  // ✅ Campo correcto
    'año' => $año,
    'mes' => $mes
]);
```

#### 2. **Mejorado manejo de errores**
- ✅ Ahora se muestran mensajes de error específicos
- ✅ Se incluye el stack trace para debugging
- ✅ Los errores se registran en logs con más detalle

### 🛠 Archivos modificados:
- ✅ `MaterialKiloController.php` - Corregido campo en `updateOrCreate`
- ✅ `MaterialKiloController.php` - Mejorado manejo de errores

### 🎯 Estado: ✅ SOLUCIONADO
La funcionalidad de guardar incidencias ahora debería funcionar correctamente.

### 🧪 Para verificar:
1. Abrir: http://127.0.0.1:8000/material_kilo/total-kg-proveedor
2. Hacer clic en "Incidencias"
3. Llenar los campos obligatorios:
   - Proveedor (requerido)
   - Año (requerido) 
   - Mes (requerido)
4. Hacer clic en "Guardar Incidencia"
5. Verificar que aparece mensaje de éxito

¡El error está corregido! 🎉
