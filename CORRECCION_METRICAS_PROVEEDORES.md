# RESUMEN DE CORRECCIONES - Sistema de Métricas de Proveedores

## 📋 PROBLEMA IDENTIFICADO

Las columnas de **Reclamaciones Graves**, **Reclamaciones Leves**, **Rechazos en Almacén**, **Aceptaciones Almacén** y **Retiradas de Tiendas** mostraban **0** o estaban vacías, a pesar de tener datos en las tablas `incidencias_proveedores` y `devoluciones_proveedores`.

---

## 🔍 CAUSAS DEL PROBLEMA

### 1. **Función `actualizarMetricasIncidencias` incompleta**
   - ❌ Solo consultaba la tabla `incidencias_proveedores`
   - ❌ No consultaba la tabla `devoluciones_proveedores`
   - ❌ Buscaba RG1 y RL1 en la tabla equivocada

### 2. **Tabla `proveedor_metrics` vacía**
   - ❌ La tabla estaba completamente vacía
   - ❌ Nunca se habían calculado las métricas correctamente

### 3. **Falta de llamada en `guardarDevolucionCompleta`**
   - ❌ No se llamaba `actualizarMetricasIncidencias()` después de guardar
   - ❌ No se actualizaba la métrica en `actualizarDevolucion()`

### 4. **Error en nombre de campo del formulario**
   - ❌ El select de proveedor usaba `name="id_proveedor"` 
   - ❌ El controlador esperaba `name="codigo_proveedor"`

---

## ✅ CORRECCIONES REALIZADAS

### 1. **Función `actualizarMetricasIncidencias` corregida**

**Ubicación:** `MaterialKiloController.php` línea ~940

**Cambios:**
- ✅ Ahora consulta **DOS tablas**: `incidencias_proveedores` y `devoluciones_proveedores`
- ✅ RG1 y RL1 se obtienen de `devoluciones_proveedores`
- ✅ DEV1, ROK1, RET1 se obtienen de `incidencias_proveedores`
- ✅ Usa `codigo_proveedor` para buscar en devoluciones

```php
private function actualizarMetricasIncidencias($id_proveedor, $año, $mes)
{
    // Contar INCIDENCIAS (DEV1, ROK1, RET1)
    $metricas_incidencias = DB::table('incidencias_proveedores')
        ->where('id_proveedor', $id_proveedor)
        ->where('año', $año)
        ->where('mes', $mes)
        ->select([
            DB::raw('SUM(CASE WHEN clasificacion_incidencia = "DEV1" THEN 1 ELSE 0 END) as dev1'),
            DB::raw('SUM(CASE WHEN clasificacion_incidencia = "ROK1" THEN 1 ELSE 0 END) as rok1'),
            DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RET1" THEN 1 ELSE 0 END) as ret1'),
        ])
        ->first();

    // Contar DEVOLUCIONES (RG1, RL1)
    $metricas_devoluciones = DB::table('devoluciones_proveedores')
        ->where('codigo_proveedor', $id_proveedor)
        ->where('año', $año)
        ->where('mes', $mes)
        ->select([
            DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RG1" THEN 1 ELSE 0 END) as rg1'),
            DB::raw('SUM(CASE WHEN clasificacion_incidencia = "RL1" THEN 1 ELSE 0 END) as rl1'),
        ])
        ->first();

    // Actualizar métricas
    ProveedorMetric::updateOrCreate(...);
}
```

---

### 2. **Agregada llamada en `guardarDevolucionCompleta`**

**Ubicación:** `MaterialKiloController.php` línea ~1627

**Antes:**
```php
$devolucion = DevolucionProveedor::create([...]);

if ($request->ajax()) {
    return response()->json([...]);
}
```

**Después:**
```php
$devolucion = DevolucionProveedor::create([...]);

// ✅ AGREGADO: Actualizar métricas
$this->actualizarMetricasIncidencias($request->codigo_proveedor, $request->año, $request->mes);

if ($request->ajax()) {
    return response()->json([...]);
}
```

---

### 3. **Agregada llamada en `actualizarDevolucion`**

**Ubicación:** `MaterialKiloController.php` línea ~1768

**Cambio similar al anterior:**
```php
$devolucion->update([...]);

// ✅ AGREGADO: Actualizar métricas
$this->actualizarMetricasIncidencias($request->codigo_proveedor, $request->año, $request->mes);

return redirect()->route(...);
```

---

### 4. **Corregido nombre de campo en formulario de devoluciones**

**Ubicación:** `total_kg_por_proveedor.blade.php` línea ~348

**Antes:**
```blade
<select id="proveedor_devolucion" name="id_proveedor" class="form-control" required>
```

**Después:**
```blade
<select id="proveedor_devolucion" name="codigo_proveedor" class="form-control" required>
```

---

### 5. **Script de recalculación de métricas**

**Archivo creado:** `recalcular_metricas.php`

Este script:
- ✅ Lee todos los periodos (año/mes) de incidencias y devoluciones
- ✅ Calcula las métricas para cada combinación proveedor/año/mes
- ✅ Inserta o actualiza la tabla `proveedor_metrics`

**Ejecución:**
```bash
php recalcular_metricas.php
```

**Resultado:**
```
========================================
RECALCULANDO MÉTRICAS DE PROVEEDORES
========================================

Periodos de incidencias encontrados: 3
Periodos de devoluciones encontrados: 3
Total de periodos únicos a procesar: 5

✓ Proveedor 45 - 2025/9 => RG1=0, RL1=1, DEV1=1, ROK1=0, RET1=0
✓ Proveedor 74 - 2025/9 => RG1=0, RL1=0, DEV1=0, ROK1=1, RET1=0
✓ Proveedor 10537 - 2025/9 => RG1=0, RL1=0, DEV1=1, ROK1=0, RET1=0
✓ Proveedor 45 - 2025/10 => RG1=1, RL1=0, DEV1=0, ROK1=0, RET1=0
✓ Proveedor 257 - 2025/10 => RG1=1, RL1=0, DEV1=0, ROK1=0, RET1=0

========================================
RESUMEN
========================================
Total procesados: 5
Total errores: 0

¡Proceso completado!
```

---

## 📊 ESTRUCTURA DE DATOS

### Tabla: `incidencias_proveedores`
- **Campo clave:** `clasificacion_incidencia`
- **Valores:**
  - `"DEV1"` = Rechazo en Almacén
  - `"ROK1"` = Aceptación Almacén (Incidencia Tienda)
  - `"RET1"` = Retirada de Tiendas (Incidencia VAD)

### Tabla: `devoluciones_proveedores`
- **Campo clave:** `clasificacion_incidencia`
- **Valores:**
  - `"RG1"` = Reclamación Grave
  - `"RL1"` = Reclamación Leve
- **Nota:** Usa `codigo_proveedor` (VARCHAR) en lugar de `id_proveedor`

### Tabla: `proveedor_metrics`
- **Campos:** `proveedor_id`, `año`, `mes`, `rg1`, `rl1`, `dev1`, `rok1`, `ret1`
- **Propósito:** Almacenar los totales calculados para mostrar en la vista

---

## 🎯 RESULTADO

Ahora cuando visites la página **Total KG por Proveedor**:

1. ✅ Se muestran las **Reclamaciones Graves (RG1)**
2. ✅ Se muestran las **Reclamaciones Leves (RL1)**
3. ✅ Se muestran los **Rechazos en Almacén (DEV1)**
4. ✅ Se muestran las **Aceptaciones Almacén (ROK1)**
5. ✅ Se muestran las **Retiradas de Tiendas (RET1)**

Las métricas se actualizan automáticamente cuando:
- ✅ Se crea una nueva incidencia
- ✅ Se actualiza una incidencia existente
- ✅ Se crea una nueva devolución (reclamación de cliente)
- ✅ Se actualiza una devolución existente

---

## 🚀 PASOS SIGUIENTES

### Si agregas nuevas incidencias o devoluciones:
- Las métricas se actualizarán **automáticamente** ✅

### Si necesitas recalcular todas las métricas:
```bash
cd c:\xampp7\htdocs\lasirena
php recalcular_metricas.php
```

### Para verificar los datos:
```bash
php artisan tinker --execute="echo json_encode(DB::table('proveedor_metrics')->get()->toArray());"
```

---

## ⚠️ IMPORTANTE

- El campo `codigo_proveedor` en `devoluciones_proveedores` es **VARCHAR**
- El campo `id_proveedor` en `incidencias_proveedores` es **INTEGER**
- La función `actualizarMetricasIncidencias` maneja ambos tipos correctamente

---

**Fecha de corrección:** 10 de Octubre, 2025
**Archivos modificados:**
1. `MaterialKiloController.php`
2. `total_kg_por_proveedor.blade.php`
3. `recalcular_metricas.php` (nuevo)
