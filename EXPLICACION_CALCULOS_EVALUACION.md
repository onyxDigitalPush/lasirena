# 📊 EXPLICACIÓN DE CÁLCULOS - Sistema de Evaluación Continua de Proveedores

## 🔵 VALORES EN AZUL (Indicadores por Millón de KG - ppm)

Los valores que aparecen en **azul** en la tabla representan los **indicadores normalizados por millón de kilogramos**. Esto permite comparar proveedores de diferentes tamaños de forma justa.

### Fórmula General:
```
Indicador (ppm) = (Número de Incidencias × 1,000,000) / Total KG del Proveedor
```

### Abreviaturas:
- **RG**: Rechazos Generales
- **RL**: Reclamaciones
- **DEV**: Devoluciones
- **ROK**: Roturas (Break/Rotura OK)
- **RET**: Retrasos

### ¿Cómo se calculan?

Los indicadores se calculan de la siguiente manera:

```php
// Código del controlador: MaterialKiloController.php
$proveedor->rg_ind1  = ($metricas->rg1 ?? 0) * 1000000 / $proveedor->total_kg_proveedor;
$proveedor->rl_ind1  = ($metricas->rl1 ?? 0) * 1000000 / $proveedor->total_kg_proveedor;
$proveedor->dev_ind1 = ($metricas->dev1 ?? 0) * 1000000 / $proveedor->total_kg_proveedor;
$proveedor->rok_ind1 = ($metricas->rok1 ?? 0) * 1000000 / $proveedor->total_kg_proveedor;
$proveedor->ret_ind1 = ($metricas->ret1 ?? 0) * 1000000 / $proveedor->total_kg_proveedor;

// Total de indicadores
$proveedor->total_ind1 = $proveedor->rg_ind1 + $proveedor->rl_ind1 + 
                         $proveedor->dev_ind1 + $proveedor->rok_ind1 + 
                         $proveedor->ret_ind1;
```

### Obtención de Métricas:

Las métricas (`rg1`, `rl1`, `dev1`, etc.) provienen de la tabla `proveedor_metrics`:

#### Para un mes específico:
```php
$metricas = ProveedorMetric::where('proveedor_id', $id_proveedor)
    ->where('año', $año)
    ->where('mes', $mes)
    ->first();
```

#### Para todo el año:
```php
// Se calcula el PROMEDIO de todos los meses
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

---

## 🟡 VALORES PONDERADOS (Columnas Amarillas)

Los valores en **amarillo** representan los **valores ponderados**, que aplican un peso o importancia a cada indicador según su criticidad para el negocio.

### Fórmula General:
```
Valor Ponderado = Indicador (ppm) × Peso (%)
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

## 📋 EJEMPLO PRÁCTICO: Proveedor ID 257 - Octubre 2025

### Paso 1: Obtener Total de KG

```sql
SELECT SUM(peso_total_kg) as total_kg
FROM material_kilos
WHERE id_proveedor = 257
  AND año = 2025
  AND mes = 10
```

**Supongamos que el resultado es: 15,432.50 kg**

### Paso 2: Obtener Métricas del Mes

```sql
SELECT rg1, rl1, dev1, rok1, ret1
FROM proveedor_metrics
WHERE proveedor_id = 257
  AND año = 2025
  AND mes = 10
```

**Ejemplo de resultados:**
- rg1 = 0.00 (sin rechazos)
- rl1 = 1.00 (1 reclamación)
- dev1 = 2.00 (2 devoluciones)
- rok1 = 0.00 (sin roturas)
- ret1 = 1.00 (1 retraso)

### Paso 3: Calcular Indicadores (Valores en Azul)

```
RG_ind  = (0.00 × 1,000,000) / 15,432.50 = 0.00 ppm
RL_ind  = (1.00 × 1,000,000) / 15,432.50 = 64.80 ppm
DEV_ind = (2.00 × 1,000,000) / 15,432.50 = 129.59 ppm
ROK_ind = (0.00 × 1,000,000) / 15,432.50 = 0.00 ppm
RET_ind = (1.00 × 1,000,000) / 15,432.50 = 64.80 ppm

TOTAL_ind = 0.00 + 64.80 + 129.59 + 0.00 + 64.80 = 259.19 ppm
```

**Estos son los valores que aparecen en AZUL en la tabla**

### Paso 4: Calcular Valores Ponderados (Valores en Amarillo)

```
RG_pond  = 0.00 × 0.30 = 0.00 puntos
RL_pond  = 64.80 × 0.05 = 3.24 puntos
DEV_pond = 129.59 × 0.20 = 25.92 puntos
ROK_pond = 0.00 × 0.10 = 0.00 puntos
RET_pond = 64.80 × 0.35 = 22.68 puntos

TOTAL_pond = 0.00 + 3.24 + 25.92 + 0.00 + 22.68 = 51.84 puntos
```

**Estos son los valores que aparecen en AMARILLO en la tabla**

### Resultado en la Tabla:

| ID | Nombre | Total KG | Valores (azul) | | | | | | Valores Ponderados (amarillo) | | | | | |
|----|--------|----------|-----|-----|-----|-----|-----|-----|-----|-----|-----|-----|-----|-----|
| | | | **RG** | **RL** | **DEV** | **ROK** | **RET** | **TOTAL** | **RG** | **RL** | **DEV** | **ROK** | **RET** | **TOTAL** |
| 257 | [Nombre] | 15,432.50 kg | 0.00 | 64.80 | 129.59 | 0.00 | 64.80 | **259.19** | 0.00 | 3.24 | 25.92 | 0.00 | 22.68 | **51.84** |

---

## 🔍 INTERPRETACIÓN DE RESULTADOS

### Valores en Azul (Indicadores ppm):
- Muestran la **frecuencia** de incidencias normalizadas
- Permiten **comparar proveedores** de diferentes tamaños
- **Ejemplo**: 64.80 ppm significa ~65 incidencias por cada millón de kilogramos

### Valores en Amarillo (Ponderados):
- Reflejan el **impacto real** en el negocio
- Incorporan la **importancia** de cada tipo de incidencia
- **Menor puntuación = Mejor proveedor**
- Se usa para **rankings** y toma de decisiones

### Ejemplo de Comparación:

**Proveedor A:**
- 10 incidencias leves (reclamaciones)
- Total ponderado: bajo (peso 5%)

**Proveedor B:**
- 5 incidencias graves (retrasos)
- Total ponderado: alto (peso 35%)

➡️ **Proveedor A es mejor**, aunque tenga más incidencias, porque son menos críticas.

---

## 📌 NOTA IMPORTANTE

Para consultar datos reales del proveedor ID 257 en octubre 2025, debes:

1. Acceder a la vista de Evaluación Continua
2. Filtrar por:
   - Año: 2025
   - Mes: Octubre (10)
   - ID Proveedor: 257
3. Revisar los valores en la tabla principal

**Los cálculos se realizan automáticamente en el controlador `MaterialKiloController.php` en el método `evaluacionContinuaProveedores()`.**

---

## 📚 Referencias

- **Archivo del controlador**: `app/Http/Controllers/MainApp/MaterialKiloController.php`
- **Vista blade**: `resources/views/MainApp/material_kilo/evaluacion_continua_proveedores.blade.php`
- **Documentación completa**: `DOCUMENTACION_SISTEMA_EVALUACION_PROVEEDORES.md`
- **Script de ejemplo**: `calcular_proveedor_45.php`
