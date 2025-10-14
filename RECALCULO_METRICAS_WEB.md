# RECALCULO DE MÉTRICAS VÍA WEB

## 📋 Descripción

Sistema web para recalcular todas las métricas de proveedores (RG1, RL1, DEV1, ROK1, RET1) sin necesidad de acceso a terminal.

## 🌐 Acceso

### Método 1: Desde la Vista Principal
1. Navegar a **Material Kilo → Total Kg por Proveedor**
2. En la parte superior de los filtros, hacer clic en el botón verde **"Recalcular Todas las Métricas"**
3. Sigue las instrucciones en pantalla

### Método 2: Acceso Directo
- URL: `http://tu-dominio.com/material_kilo/recalcular-metricas`
- Ruta Laravel: `material_kilo.recalcular_metricas`

## 🔐 Seguridad

- ✅ **Protegido con autenticación**: Solo usuarios autenticados pueden acceder
- ✅ **Middleware auth**: La ruta está dentro del grupo de rutas protegidas
- ⚠️ **Recomendación**: Ejecutar en horarios de baja actividad

## ⚙️ Funcionamiento

### Proceso de Recálculo

1. **Inicio**
   - Click en "Iniciar Recálculo de Métricas"
   - Confirmación del usuario (popup de confirmación)

2. **Ejecución**
   - Barra de progreso animada
   - Mensaje: "Procesando... Por favor espere..."
   - Tiempo estimado: 2-5 minutos (dependiendo de los datos)

3. **Resultados**
   - Total de períodos procesados
   - Número de registros exitosos
   - Número de errores (si los hay)
   - Tiempo total de ejecución
   - Detalles de errores (si aplica)

4. **Finalización**
   - Botón para ver resultados en "Total Kg por Proveedor"
   - Opción para ejecutar nuevamente

## 📊 Qué se Recalcula

El sistema procesa:

- **Tabla afectada**: `proveedor_metrics`
- **Acción**: Trunca y regenera TODOS los registros
- **Fuentes de datos**:
  - `incidencias_proveedores` → RG1, RL1
  - `devoluciones_proveedores` → DEV1, ROK1, RET1

### Métricas Calculadas

| Métrica | Descripción | Origen |
|---------|-------------|--------|
| **RG1** | Reclamaciones Graves | incidencias_proveedores.clasificacion_incidencia = 'RG1' |
| **RL1** | Reclamaciones Leves | incidencias_proveedores.clasificacion_incidencia = 'RL1' |
| **DEV1** | Rechazos Almacén | devoluciones_proveedores.clasificacion_devolucion = 'DEV1' |
| **ROK1** | Aceptaciones Condicionadas | devoluciones_proveedores.clasificacion_devolucion = 'ROK1' |
| **RET1** | Retiradas de Tienda | devoluciones_proveedores.clasificacion_devolucion = 'RET1' |

### Agrupación

- Por **proveedor_id**
- Por **año** (campo entero)
- Por **mes** (campo entero de 1 a 12)

## 🔧 Archivos Modificados

### Rutas
**Archivo**: `routes/web.php`

```php
// Vista del formulario
Route::get('/material_kilo/recalcular-metricas', 
    'MaterialKiloController@recalcularMetricasWeb')
    ->name('material_kilo.recalcular_metricas');

// Endpoint AJAX para ejecutar el recálculo
Route::post('/material_kilo/ejecutar-recalculo-metricas', 
    'MaterialKiloController@ejecutarRecalculoMetricas')
    ->name('material_kilo.ejecutar_recalculo_metricas');
```

### Controlador
**Archivo**: `app/Http/Controllers/MainApp/MaterialKiloController.php`

**Métodos nuevos**:
- `recalcularMetricasWeb()`: Muestra la vista
- `ejecutarRecalculoMetricas()`: Ejecuta el recálculo y retorna JSON

### Vista
**Archivo**: `resources/views/MainApp/material_kilo/recalcular_metricas.blade.php`

Características:
- Interfaz Bootstrap responsive
- Alertas informativas
- Barra de progreso animada
- Resultados detallados
- Manejo de errores con detalles

### Vista Modificada
**Archivo**: `resources/views/MainApp/material_kilo/total_kg_por_proveedor.blade.php`

- Botón verde "Recalcular Todas las Métricas" agregado en la sección de filtros
- Texto explicativo sobre cuándo usar esta función

## 🚀 Uso en Producción

### Paso 1: Subir Archivos
```bash
# Subir por FTP o Git:
- routes/web.php
- app/Http/Controllers/MainApp/MaterialKiloController.php
- resources/views/MainApp/material_kilo/recalcular_metricas.blade.php
- resources/views/MainApp/material_kilo/total_kg_por_proveedor.blade.php
```

### Paso 2: Limpiar Caché (si es necesario)
Si tienes acceso a terminal en producción:
```bash
php artisan route:clear
php artisan cache:clear
php artisan config:clear
```

Si NO tienes acceso a terminal, crear archivo `clear_cache.php` en `public/`:
```php
<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('route:clear');
$kernel->call('cache:clear');
$kernel->call('config:clear');
echo "Cache limpiado";
```

Acceder vía navegador: `http://tu-dominio.com/clear_cache.php`

### Paso 3: Probar

1. Acceder a `http://tu-dominio.com/material_kilo/recalcular-metricas`
2. Verificar que carga correctamente
3. Ejecutar recálculo
4. Verificar resultados

## ⚠️ Consideraciones

### Timeouts
- **AJAX timeout**: 5 minutos (300,000 ms)
- **PHP max_execution_time**: Verificar que sea suficiente (recomendado: 300 segundos)

Si el proceso tarda más de 5 minutos:
```php
// En php.ini o .htaccess
max_execution_time = 600
```

### Base de Datos
- **Truncate**: Requiere que el usuario de BD tenga permisos de TRUNCATE
- **Foreign Keys**: 3 errores esperados por restricciones de clave foránea (proveedores que no existen en tabla principal)

### Logs
Todos los eventos se registran en `storage/logs/laravel.log`:
- Inicio y fin del proceso
- Cada período procesado
- Errores específicos con detalles

Buscar en logs:
```
===== INICIO RECÁLCULO WEB DE MÉTRICAS =====
===== FIN RECÁLCULO WEB DE MÉTRICAS =====
```

## 🐛 Troubleshooting

### Error: "Undefined route"
**Solución**: Limpiar caché de rutas
```bash
php artisan route:clear
```

### Error: "Class not found"
**Solución**: Verificar que el namespace en el controlador sea correcto
```php
namespace App\Http\Controllers\MainApp;
```

### Error: "View not found"
**Solución**: Verificar ruta de la vista
```
resources/views/MainApp/material_kilo/recalcular_metricas.blade.php
```

### Proceso toma más de 5 minutos
**Solución**: Aumentar timeout en JavaScript
```javascript
// En recalcular_metricas.blade.php, línea del AJAX
timeout: 600000 // 10 minutos
```

### Errores de Foreign Key
**Normal**: Si aparecen 2-3 errores sobre "foreign key constraint fails", es esperado.
Son proveedores en incidencias/devoluciones que no existen en tabla `proveedores`.

## 📈 Ventajas sobre el Script de Terminal

| Característica | Script Terminal | Web Interface |
|----------------|-----------------|---------------|
| **Acceso** | Requiere SSH/Terminal | Navegador web |
| **Autorización** | Acceso servidor | Login Laravel |
| **Progreso** | Solo logs | Barra visual |
| **Resultados** | Ver logs después | Pantalla inmediata |
| **Usabilidad** | Técnico | Usuario final |
| **Errores** | Log file | Pantalla + Log |

## 📝 Mantenimiento Futuro

### Mejoras Posibles

1. **Procesamiento por lotes**: Para bases de datos muy grandes
2. **WebSockets**: Progreso en tiempo real
3. **Scheduling**: Recálculo automático nocturno
4. **Email notification**: Notificar cuando termina
5. **Dry-run mode**: Previsualizar cambios sin aplicarlos

### Código Reutilizable

La lógica de recálculo está duplicada en:
1. `recalcular_metricas_completo.php` (standalone)
2. `MaterialKiloController::ejecutarRecalculoMetricas()` (web)

**Recomendación futura**: Extraer a un Service o Job de Laravel para evitar duplicación.

## 🔗 Referencias

- Documentación principal: `SOLUCION_METRICAS_PROVEEDORES.md`
- Script original: `recalcular_metricas_completo.php`
- Estructura de datos: `ESTRUCTURA_TABLAS_METRICAS.md`

---

**Última actualización**: Enero 2025  
**Versión**: 1.0  
**Autor**: Sistema de Métricas de Proveedores
