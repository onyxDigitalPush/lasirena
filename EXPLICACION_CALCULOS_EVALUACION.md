# 📊 EXPLICACIÓN DE CÁLCULOS - Sistema de Evaluación Continua de Proveedores

## 🔵 VALORES EN AZUL (Número de Incidencias)

Los valores que aparecen en **azul** en la tabla representan el **número directo de incidencias** registradas para cada proveedor en el período seleccionado.

### Fórmula General:
```
Valor en Azul = Número de Incidencias
```

### Abreviaturas:
- **RG**: Rechazos Generales
- **RL**: Reclamaciones
- **DEV**: Devoluciones
- **ROK**: Roturas (Break/Rotura OK)
- **RET**: Retrasos

### ¿Cómo se calculan?

Los valores se obtienen directamente de las métricas registradas:

```php
// Código del controlador: MaterialKiloController.php
$proveedor->rg_ind1  = ($metricas->rg1 ?? 0);
$proveedor->rl_ind1  = ($metricas->rl1 ?? 0);
$proveedor->dev_ind1 = ($metricas->dev1 ?? 0);
$proveedor->rok_ind1 = ($metricas->rok1 ?? 0);
$proveedor->ret_ind1 = ($metricas->ret1 ?? 0);

// Total de incidencias
$proveedor->total_ind1 = $proveedor->rg_ind1 + $proveedor->rl_ind1 + 
                         $proveedor->dev_ind1 + $proveedor->rok_ind1 + 
                         $proveedor->ret_ind1;
```

### Obtención de Métricas:

Las métricas (`rg1`, `rl1`, `dev1`, etc.) provienen de la tabla `gp_ls_proveedor_metrics`:

#### Para un mes específico:
```php
$metricas = ProveedorMetric::where('proveedor_id', $id_proveedor)
    ->where('año', $año)
    ->where('mes', $mes)
    ->first();
```
En este caso, se muestran las incidencias exactas de ese mes.

#### Para todo el año:
```php
// Se calcula el PROMEDIO de todos los meses del año
$metricas_proveedor = ProveedorMetric::where('proveedor_id', $id_proveedor)
    ->where('año', $año)
    ->get();

$promedio = new stdClass();
$promedio->rg1  = $metricas_proveedor->avg('rg1');
$promedio->rl1  = $metricas_proveedor->avg('rl1');
$promedio->dev1 = $metricas_proveedor->avg('dev1');
$promedio->rok1 = $metricas_proveedor->avg('rok1');
$promedio->ret1 = $metricas_proveedor->avg('ret1');
```

**⚠️ IMPORTANTE**: Cuando se consulta todo el año (sin especificar mes), los valores mostrados son el **PROMEDIO MENSUAL**, no la suma total.

**Ejemplo**: Si el proveedor tiene:
- Mes 1: 1 reclamación
- Mes 2: 2 reclamaciones  
- Mes 3: 1 reclamación
- (y así 9 meses con total de 25 reclamaciones)

El valor mostrado será: **2.78** (promedio: 25 ÷ 9 meses)

---

## 🟡 VALORES PONDERADOS (Columnas Amarillas)

Los valores en **amarillo** representan los **valores ponderados**, que aplican un peso o importancia a cada tipo de incidencia según su criticidad para el negocio.

### Fórmula General:
```
Valor Ponderado = Número de Incidencias × Peso (%)
```

### Pesos Aplicados:

| Indicador | Peso | Justificación |
|-----------|------|---------------|
| **RET** (Retrasos) | 35% | Mayor impacto: afecta la planificación y operaciones |
| **RG** (Rechazos) | 30% | Alto impacto: producto no aceptado |
| **DEV** (Devoluciones) | 20% | Impacto medio-alto: costos de logística |
| **ROK** (Roturas) | 10% | Impacto medio: daños en transporte |
| **RL** (Reclamaciones) | 5% | Menor impacto: quejas formales |

### ¿Cómo se calculan?

```php
// Código del controlador: MaterialKiloController.php
$proveedor->rg_pond1  = $proveedor->rg_ind1 * 0.30;  // RG × 30%
$proveedor->rl_pond1  = $proveedor->rl_ind1 * 0.05;  // RL × 5%
$proveedor->dev_pond1 = $proveedor->dev_ind1 * 0.20; // DEV × 20%
$proveedor->rok_pond1 = $proveedor->rok_ind1 * 0.10; // ROK × 10%
$proveedor->ret_pond1 = $proveedor->ret_ind1 * 0.35; // RET × 35%

// Total ponderado
$proveedor->total_pond1 = $proveedor->rg_pond1 + $proveedor->rl_pond1 + 
                          $proveedor->dev_pond1 + $proveedor->rok_pond1 + 
                          $proveedor->ret_pond1;
```

### Interpretación:
- **Menor puntuación = Mejor desempeño** del proveedor
- La puntuación total ponderada resume el rendimiento global del proveedor

---

## 📋 EJEMPLO PRÁCTICO: Proveedor ID 257 - Año 2025 Completo

### Datos Reales en Base de Datos:

```
Proveedor 257 - Año 2025:
Mes 1:  RG=0, RL=1,  DEV=0
Mes 2:  RG=0, RL=2,  DEV=2
Mes 3:  RG=0, RL=1,  DEV=3
Mes 4:  RG=0, RL=6,  DEV=1
Mes 5:  RG=0, RL=2,  DEV=2
Mes 6:  RG=0, RL=4,  DEV=1
Mes 7:  RG=0, RL=4,  DEV=0
Mes 8:  RG=0, RL=4,  DEV=0
Mes 10: RG=3, RL=1,  DEV=0
-----------------------------------
TOTALES: RG=3, RL=25, DEV=9 (en 9 meses)
```

### Paso 1: Calcular Promedios Mensuales

```
RG_promedio  = 3 ÷ 9 meses  = 0.33
RL_promedio  = 25 ÷ 9 meses = 2.78
DEV_promedio = 9 ÷ 9 meses  = 1.00
ROK_promedio = 0 ÷ 9 meses  = 0.00
RET_promedio = 0 ÷ 9 meses  = 0.00
```

### Paso 2: Valores en Azul (Promedio de Incidencias)

```
RG_ind  = 0.33 incidencias promedio/mes
RL_ind  = 2.78 incidencias promedio/mes
DEV_ind = 1.00 incidencias promedio/mes
ROK_ind = 0.00 incidencias promedio/mes
RET_ind = 0.00 incidencias promedio/mes

TOTAL_ind = 0.33 + 2.78 + 1.00 + 0.00 + 0.00 = 4.11 incidencias promedio/mes
```

**Estos son los valores que aparecen en AZUL en la tabla**

### Paso 3: Calcular Valores Ponderados (Valores en Amarillo)

```
RG_pond  = 0.33 × 0.30 = 0.10 puntos
RL_pond  = 2.78 × 0.05 = 0.14 puntos
DEV_pond = 1.00 × 0.20 = 0.20 puntos
ROK_pond = 0.00 × 0.10 = 0.00 puntos
RET_pond = 0.00 × 0.35 = 0.00 puntos

TOTAL_pond = 0.10 + 0.14 + 0.20 + 0.00 + 0.00 = 0.44 puntos
```

**Estos son los valores que aparecen en AMARILLO en la tabla**

### Resultado en la Tabla:

| ID | Nombre | Total KG | Valores (azul) - Promedio Mensual | | | | | | Valores Ponderados (amarillo) | | | | | |
|----|--------|----------|-----|-----|-----|-----|-----|-----|-----|-----|-----|-----|-----|-----|
| | | | **RG** | **RL** | **DEV** | **ROK** | **RET** | **TOTAL** | **RG** | **RL** | **DEV** | **ROK** | **RET** | **TOTAL** |
| 257 | [Nombre] | [Total KG] | 0.33 | 2.78 | 1.00 | 0.00 | 0.00 | **4.11** | 0.10 | 0.14 | 0.20 | 0.00 | 0.00 | **0.44** |

---

## 🔍 INTERPRETACIÓN DE RESULTADOS

### Valores en Azul (Número de Incidencias):
- **Para un mes específico**: Muestra el número exacto de incidencias de ese mes
- **Para todo el año**: Muestra el **promedio mensual** de incidencias
- **Ejemplo año completo**: 2.78 significa que el proveedor tuvo un promedio de ~2.78 incidencias por mes
- **Ejemplo mes específico**: 1.00 significa que hubo exactamente 1 incidencia en ese mes

### Valores en Amarillo (Ponderados):
- Reflejan el **impacto real** en el negocio
- Incorporan la **importancia** de cada tipo de incidencia
- **Menor puntuación = Mejor proveedor**
- Se usa para **rankings** y toma de decisiones

### Diferencia Mes vs Año Completo:

**Consulta por Mes Específico (ej. Octubre 2025):**
- Valores azules = Incidencias exactas de octubre
- Valores ponderados = Incidencias × Pesos

**Consulta por Año Completo (ej. Todo 2025):**
- Valores azules = Promedio mensual de incidencias
- Valores ponderados = Promedio mensual × Pesos

### Ejemplo de Comparación:

**Proveedor A (año completo):**
- 10 reclamaciones promedio/mes
- Total ponderado: 0.50 puntos (10 × 5%)

**Proveedor B (año completo):**
- 5 retrasos promedio/mes
- Total ponderado: 1.75 puntos (5 × 35%)

➡️ **Proveedor A es mejor**, aunque tenga más incidencias promedio, porque son menos críticas.

---

## 📌 NOTA IMPORTANTE

### Verificación de Datos Reales:

Para consultar datos del proveedor ID 257 en 2025:

1. **Por año completo** (valores promedio):
   - URL: `material_kilo/evaluacion-continua-proveedores?año=2025&id_proveedor=257`
   - Muestra: **Promedio mensual** de incidencias
   - Ejemplo: RG=0.33, RL=2.78, DEV=1.00 (promedio de 9 meses)

2. **Por mes específico** (valores exactos):
   - URL: `material_kilo/evaluacion-continua-proveedores?año=2025&mes=10&id_proveedor=257`
   - Muestra: **Incidencias exactas** de octubre 2025
   - Ejemplo: RG=3, RL=1, DEV=0

### Consulta SQL para Verificar:

```sql
-- Ver datos por mes
SELECT mes, rg1, rl1, dev1, rok1, ret1 
FROM gp_ls_proveedor_metrics 
WHERE proveedor_id = 257 AND año = 2025 
ORDER BY mes;

-- Ver promedios del año
SELECT 
  COUNT(*) as meses_registrados,
  AVG(rg1) as rg_promedio,
  AVG(rl1) as rl_promedio,
  AVG(dev1) as dev_promedio,
  SUM(rg1) as rg_total,
  SUM(rl1) as rl_total,
  SUM(dev1) as dev_total
FROM gp_ls_proveedor_metrics 
WHERE proveedor_id = 257 AND año = 2025;
```

**Los cálculos se realizan automáticamente en el controlador `MaterialKiloController.php` en el método `evaluacionContinuaProveedores()`.**

---

## 📚 Referencias

- **Archivo del controlador**: `app/Http/Controllers/MainApp/MaterialKiloController.php`
- **Vista blade**: `resources/views/MainApp/material_kilo/evaluacion_continua_proveedores.blade.php`
- **Documentación completa**: `DOCUMENTACION_SISTEMA_EVALUACION_PROVEEDORES.md`
- **Script de ejemplo**: `calcular_proveedor_45.php`
