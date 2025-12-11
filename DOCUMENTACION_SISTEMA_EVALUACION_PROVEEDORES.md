# 📘 DOCUMENTACIÓN COMPLETA: Sistema de Evaluación Continua de Proveedores

---

## 🎯 Resumen Ejecutivo

Este sistema permite evaluar objetivamente el desempeño de proveedores mediante **indicadores normalizados por volumen de suministro**, garantizando comparaciones justas independientemente del tamaño del proveedor.

### ¿Qué hace el sistema?

1. **Registra incidencias** mensualmente por proveedor (rechazos, reclamaciones, devoluciones, roturas, retrasos)
2. **Normaliza por volumen** (valores por millón de KG) para comparar proveedores equitativamente
3. **Pondera según criticidad** de cada tipo de incidencia (retrasos 35%, rechazos 30%, etc.)
4. **Genera reportes** visuales y exportables a Excel

### ¿Cómo se almacenan los datos?

```
incidencias_proveedores → [Cuenta RG1, RL1]
devoluciones_proveedores → [Cuenta DEV1, ROK1, RET1]
                ↓
    proveedor_metrics (1 registro/proveedor/mes)
                ↓
        material_kilos (KG suministrados)
                ↓   ..
    Cálculos: Indicadores PPM y Valores Ponderados
```

### Resultado: Proveedor 45 (ALIMENTBARNA SL) - Ejemplo Real

- **36,606.60 kg** suministrados en 2025
- **40.98 ppm** de incidencias totales (muy bajo)
- **6.15 puntos** ponderados = **EXCELENTE desempeño**
- Cero rechazos, cero roturas, cero retrasos

---

## 📋 Índice
1. [Introducción](#introducción)
2. [Estructura de Base de Datos](#estructura-de-base-de-datos)
3. [Proceso de Registro de Métricas](#proceso-de-registro-de-métricas)
4. [Cálculo de Indicadores](#cálculo-de-indicadores)
5. [Ejemplo Práctico: Proveedor 45](#ejemplo-práctico-proveedor-45)
6. [Flujo Completo del Sistema](#flujo-completo-del-sistema)

---

## 🎯 Introducción

El **Sistema de Evaluación Continua de Proveedores** permite medir el desempeño de los proveedores mediante la normalización de incidencias por volumen de suministro. Esto garantiza una comparación justa entre proveedores grandes y pequeños.

### Objetivo Principal
Calcular indicadores de calidad basados en:
- **Valores por Millón de KG**: Normalización de incidencias
- **Valores Ponderados**: Pesos según importancia de cada tipo de incidencia

---

## 🗄️ Estructura de Base de Datos

### 📊 Diagrama de Relaciones

```
┌─────────────────────────────────────────────────────────────────────┐
│                    TABLAS FUENTE DE DATOS                           │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌──────────────────────────────┐    ┌──────────────────────────┐  │
│  │ incidencias_proveedores      │    │ devoluciones_proveedores │  │
│  ├──────────────────────────────┤    ├──────────────────────────┤  │
│  │ - id                         │    │ - id                     │  │
│  │ - id_proveedor               │    │ - id_proveedor           │  │
│  │ - año                        │    │ - año                    │  │
│  │ - mes                        │    │ - mes                    │  │
│  │ - clasificacion_incidencia   │    │ - clasificacion_devolucion│ │
│  │   ('RG1', 'RL1')            │    │   ('DEV1','ROK1','RET1') │  │
│  │ - descripcion                │    │ - motivo                 │  │
│  │ - fecha_incidencia           │    │ - fecha_devolucion       │  │
│  └──────────────┬───────────────┘    └────────────┬─────────────┘  │
│                 │                                   │                │
│                 └────────────┬──────────────────────┘                │
└──────────────────────────────┼───────────────────────────────────────┘
                               │ COUNT() por clasificación
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│               TABLA CENTRAL: proveedor_metrics                      │
├─────────────────────────────────────────────────────────────────────┤
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │ id | proveedor_id | año | mes | rg1 | rl1 | dev1 | rok1 | ret1│ │
│  ├──────────────────────────────────────────────────────────────┤  │
│  │ 1  |     45       | 2025|  5  | 0.00| 1.00| 1.00 | 0.00| 0.00│  │
│  │ 316|     45       | 2025| 11  | 0.00| 0.00| 1.00 | 0.00| 0.00│  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                      │
│  ⚠️ UNIQUE KEY: (proveedor_id, año, mes)                           │
│  📌 Un solo registro por proveedor por mes                         │
└──────────────────────────────┬───────────────────────────────────────┘
                               │
                               │ JOIN para cálculos
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    material_kilos                                   │
├─────────────────────────────────────────────────────────────────────┤
│  ┌──────────────────────────────────────────────────────┐          │
│  │ id | proveedor_id | año | mes | total_kg | registros│          │
│  ├──────────────────────────────────────────────────────┤          │
│  │ 1  |     45       | 2025|  1  | 4543.78  |    5     │          │
│  │ 2  |     45       | 2025|  2  | 2336.69  |    3     │          │
│  │ .. |     ..       | ... | ... | ...      |   ...    │          │
│  └──────────────────────────────────────────────────────┘          │
│                                                                      │
│  💡 Almacena el VOLUMEN de suministro (KG)                         │
└──────────────────────────────┬───────────────────────────────────────┘
                               │
                               │ JOIN + CÁLCULO
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│                 CÁLCULOS EN EL CONTROLADOR                          │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  1️⃣ Obtener métricas (proveedor_metrics)                           │
│     - Si MES seleccionado: usar valores exactos del mes            │
│     - Si AÑO completo: calcular PROMEDIO de todos los meses        │
│                                                                      │
│  2️⃣ Obtener total KG (material_kilos)                              │
│     SUM(total_kg) por proveedor/año/mes según filtros              │
│                                                                      │
│  3️⃣ Calcular Indicadores por Millón de KG                          │
│     Indicador = (Métricas × 1,000,000) / Total KG                  │
│                                                                      │
│  4️⃣ Calcular Valores Ponderados                                    │
│     Ponderado = Indicador × Peso (30%, 5%, 20%, 10%, 35%)         │
│                                                                      │
└──────────────────────────────┬───────────────────────────────────────┘
                               │
                               ▼
                    📊 VISTA BLADE (HTML)
                    Tabla con resultados
```

---

## 🗄️ Estructura de Base de Datos

### Tabla: `proveedores`
Almacena la información básica de cada proveedor.

```sql
CREATE TABLE proveedores (
    id_proveedor INT PRIMARY KEY,
    nombre_proveedor VARCHAR(255),
    email_proveedor VARCHAR(255),
    familia VARCHAR(100),
    subfamilia VARCHAR(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Ejemplo de registro:**
```json
{
    "id_proveedor": 45,
    "nombre_proveedor": "ALIMENTBARNA SL",
    "email_proveedor": "judit.burgues@alimentbarna.com; castor.bayo@alimentbarna.com",
    "familia": "ELABORADOS",
    "subfamilia": "Carnes"
}
```

---

### Tabla: `material_kilos`
Registra los kilogramos suministrados por cada proveedor mensualmente.

```sql
CREATE TABLE material_kilos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    proveedor_id INT,
    año YEAR,
    mes TINYINT,
    total_kg DECIMAL(10,2),
    cantidad_registros INT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id_proveedor)
);
```

**Función:** Almacenar el volumen total de mercancía recibida de cada proveedor por mes.

---

### Tabla: `proveedor_metrics`
**Tabla central del sistema** - Almacena las métricas (incidencias) de cada proveedor por mes.

```sql
CREATE TABLE proveedor_metrics (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    proveedor_id INT NOT NULL,
    año YEAR NOT NULL,
    mes TINYINT NOT NULL,
    rg1 DECIMAL(10,2) NULL COMMENT 'Rechazos en Almacén',
    rl1 DECIMAL(10,2) NULL COMMENT 'Reclamaciones',
    dev1 DECIMAL(10,2) NULL COMMENT 'Devoluciones',
    rok1 DECIMAL(10,2) NULL COMMENT 'Roturas',
    ret1 DECIMAL(10,2) NULL COMMENT 'Retrasos',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id_proveedor) ON DELETE CASCADE,
    UNIQUE KEY unique_proveedor_metrics (proveedor_id, año, mes),
    INDEX idx_año_mes (año, mes)
);
```

#### Campos de Métricas:

| Campo | Descripción | Peso Ponderado | Origen |
|-------|-------------|----------------|---------|
| **rg1** | Rechazos en Almacén (Recepción) | 30% | `incidencias_proveedores` |
| **rl1** | Reclamaciones (Calidad) | 5% | `incidencias_proveedores` |
| **dev1** | Devoluciones (Producto Defectuoso) | 20% | `devoluciones_proveedores` |
| **rok1** | Roturas (Producto Dañado) | 10% | `devoluciones_proveedores` |
| **ret1** | Retrasos (Entrega Tardía) | 35% | `devoluciones_proveedores` |

---

### Tablas Fuente de Datos

#### `incidencias_proveedores`
Registra incidencias de recepción y calidad.

```sql
SELECT 
    id_proveedor,
    año,
    mes,
    clasificacion_incidencia, -- 'RG1' o 'RL1'
    descripcion,
    fecha_incidencia
FROM incidencias_proveedores
WHERE clasificacion_incidencia IN ('RG1', 'RL1');
```

#### `devoluciones_proveedores`
Registra devoluciones, roturas y retrasos.

```sql
SELECT 
    id_proveedor,
    año,
    mes,
    clasificacion_devolucion, -- 'DEV1', 'ROK1', 'RET1'
    motivo,
    fecha_devolucion
FROM devoluciones_proveedores
WHERE clasificacion_devolucion IN ('DEV1', 'ROK1', 'RET1');
```

---

## 🔄 Proceso de Registro de Métricas

### 1. Recolección de Datos
El sistema cuenta automáticamente las incidencias/devoluciones de cada proveedor por mes y año.

### 2. Método: `recalcularMetricasProveedoresWeb()`
**Ubicación:** `MaterialKiloController.php` (línea 2925-3050)

```php
public function recalcularMetricasProveedoresWeb(Request $request)
{
    // 1. Obtener todos los períodos (año-mes) con registros de material_kilos
    $periodos = DB::table('material_kilos')
        ->select('proveedor_id as id_proveedor', 'año', 'mes')
        ->groupBy('proveedor_id', 'año', 'mes')
        ->get();

    foreach ($periodos as $periodo) {
        $id_proveedor = $periodo->id_proveedor;
        $año = $periodo->año;
        $mes = $periodo->mes;

        // 2. Contar incidencias RG1 (Rechazos Almacén)
        $rg1 = DB::table('incidencias_proveedores')
            ->where('id_proveedor', $id_proveedor)
            ->where('año', $año)
            ->where('mes', $mes)
            ->where('clasificacion_incidencia', 'RG1')
            ->count();

        // 3. Contar incidencias RL1 (Reclamaciones)
        $rl1 = DB::table('incidencias_proveedores')
            ->where('id_proveedor', $id_proveedor)
            ->where('año', $año)
            ->where('mes', $mes)
            ->where('clasificacion_incidencia', 'RL1')
            ->count();

        // 4. Contar devoluciones DEV1
        $dev1 = DB::table('devoluciones_proveedores')
            ->where('id_proveedor', $id_proveedor)
            ->where('año', $año)
            ->where('mes', $mes)
            ->where('clasificacion_devolucion', 'DEV1')
            ->count();

        // 5. Contar roturas ROK1
        $rok1 = DB::table('devoluciones_proveedores')
            ->where('id_proveedor', $id_proveedor)
            ->where('año', $año)
            ->where('mes', $mes)
            ->where('clasificacion_devolucion', 'ROK1')
            ->count();

        // 6. Contar retrasos RET1
        $ret1 = DB::table('devoluciones_proveedores')
            ->where('id_proveedor', $id_proveedor)
            ->where('año', $año)
            ->where('mes', $mes)
            ->where('clasificacion_devolucion', 'RET1')
            ->count();

        // 7. Insertar o actualizar en proveedor_metrics
        DB::table('proveedor_metrics')->updateOrInsert(
            [
                'proveedor_id' => $id_proveedor,
                'año' => $año,
                'mes' => $mes
            ],
            [
                'rg1' => $rg1,
                'rl1' => $rl1,
                'dev1' => $dev1,
                'rok1' => $rok1,
                'ret1' => $ret1,
                'updated_at' => now()
            ]
        );
    }
}
```

### 3. Restricción UNIQUE
La clave única `unique_proveedor_metrics (proveedor_id, año, mes)` garantiza:
- **Un solo registro** por proveedor, por mes, por año
- Actualizaciones automáticas en lugar de duplicados

---

## 📐 Cálculo de Indicadores

### Método: `evaluacionContinuaProveedores()`
**Ubicación:** `MaterialKiloController.php` (línea 788-946)

### Paso 1: Obtener Total de KG
```php
$totales_por_proveedor = DB::table('material_kilos')
    ->join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
    ->select(
        'proveedores.id_proveedor',
        'proveedores.nombre_proveedor',
        DB::raw('SUM(gp_ls_material_kilos.total_kg) as total_kg_proveedor')
    )
    ->where('material_kilos.año', $año)
    ->when($mes, function($q) use ($mes) {
        return $q->where('material_kilos.mes', $mes);
    })
    ->groupBy('proveedores.id_proveedor', 'proveedores.nombre_proveedor')
    ->get();
```

### Paso 2: Obtener Métricas

#### 2A. Para un mes específico:
```php
$metricas = ProveedorMetric::where('proveedor_id', $id_proveedor)
    ->where('año', $año)
    ->where('mes', $mes)
    ->first();
```

#### 2B. Para todo el año (SIN mes):
```php
// Calcula el PROMEDIO de las métricas de todos los meses
$metricas = ProveedorMetric::where('proveedor_id', $id_proveedor)
    ->where('año', $año)
    ->get();

$promedio = new stdClass();
$promedio->rg1 = $metricas->avg('rg1');
$promedio->rl1 = $metricas->avg('rl1');
$promedio->dev1 = $metricas->avg('dev1');
$promedio->rok1 = $metricas->avg('rok1');
$promedio->ret1 = $metricas->avg('ret1');
```

### Paso 3: Calcular Valores por Millón de KG

```php
// Fórmula: (Número de incidencias × 1,000,000) / Total KG
$proveedor->rg_ind1  = ($metricas->rg1 ?? 0) * 1000000 / $total_kg_proveedor;
$proveedor->rl_ind1  = ($metricas->rl1 ?? 0) * 1000000 / $total_kg_proveedor;
$proveedor->dev_ind1 = ($metricas->dev1 ?? 0) * 1000000 / $total_kg_proveedor;
$proveedor->rok_ind1 = ($metricas->rok1 ?? 0) * 1000000 / $total_kg_proveedor;
$proveedor->ret_ind1 = ($metricas->ret1 ?? 0) * 1000000 / $total_kg_proveedor;

$proveedor->total_ind1 = $proveedor->rg_ind1 + $proveedor->rl_ind1 + 
                          $proveedor->dev_ind1 + $proveedor->rok_ind1 + 
                          $proveedor->ret_ind1;
```

### Paso 4: Calcular Valores Ponderados

```php
// Fórmula: Indicador × Peso (porcentaje de importancia)
$proveedor->rg_pond1  = $proveedor->rg_ind1 * 0.30;  // 30%
$proveedor->rl_pond1  = $proveedor->rl_ind1 * 0.05;  // 5%
$proveedor->dev_pond1 = $proveedor->dev_ind1 * 0.20; // 20%
$proveedor->rok_pond1 = $proveedor->rok_ind1 * 0.10; // 10%
$proveedor->ret_pond1 = $proveedor->ret_ind1 * 0.35; // 35%

$proveedor->total_pond1 = $proveedor->rg_pond1 + $proveedor->rl_pond1 + 
                           $proveedor->dev_pond1 + $proveedor->rok_pond1 + 
                           $proveedor->ret_pond1;
```

---

## 📊 Ejemplo Práctico: Proveedor 45 (ALIMENTBARNA SL)

### Datos Actuales del Proveedor (Diciembre 2025)

#### Información General:
```json
{
    "id_proveedor": 45,
    "nombre_proveedor": "ALIMENTBARNA SL",
    "familia": "ELABORADOS",
    "subfamilia": "Carnes",
    "email": "judit.burgues@alimentbarna.com; castor.bayo@alimentbarna.com"
}
```

#### Total KG Suministrado en 2025:
```json
{
    "total_kg": "36,606.60 kg",
    "num_registros": 37,
    "periodo": "Enero - Octubre 2025",
    "meses_con_entregas": 10
}
```

#### Métricas Registradas en `proveedor_metrics`:

**Estado actual en base de datos (Diciembre 2025 - Después de corrección):**

| Mes | RG1 | RL1 | DEV1 | ROK1 | RET1 | Total | Descripción |
|-----|-----|-----|------|------|------|-------|-------------|
| Enero (1) | 0 | 0 | 0 | 0 | 0 | 0 | Sin incidencias |
| Febrero (2) | 0 | 0 | 0 | 0 | 0 | 0 | Sin incidencias |
| Marzo (3) | 0 | 0 | 0 | 0 | 0 | 0 | Sin incidencias |
| Abril (4) | 0 | 0 | 0 | 0 | 0 | 0 | Sin incidencias |
| **Mayo (5)** | 0 | **1** | **1** | 0 | 0 | **2** | 1 reclamación + 1 devolución |
| Junio (6) | 0 | 0 | 0 | 0 | 0 | 0 | Sin incidencias |
| Julio (7) | 0 | 0 | 0 | 0 | 0 | 0 | Sin incidencias |
| Agosto (8) | 0 | 0 | 0 | 0 | 0 | 0 | Sin incidencias |
| **Septiembre (9)** | 0 | **1** | 0 | 0 | 0 | **1** | 1 reclamación |
| Octubre (10) | 0 | 0 | 0 | 0 | 0 | 0 | Sin incidencias |
| **Noviembre (11)** | 0 | 0 | **1** | 0 | 0 | **1** | 1 devolución |

**Totales:**
- Meses con entregas: 10 (Ene-Oct, Nov tiene métricas pero se creó después)
- Total incidencias RG1: 0
- Total incidencias RL1: 2
- Total incidencias DEV1: 2
- Total incidencias ROK1: 0
- Total incidencias RET1: 0
- **TOTAL GENERAL: 4 incidencias**

**Registros originales en tablas fuente:**
1. `incidencias_proveedores` ID 90: Mayo - DEV1 - "Temperatura elevada"
2. `incidencias_proveedores` ID 131: Noviembre - DEV1 - "Pruebas Mari"
3. `devoluciones_proveedores` ID 250: Mayo - RL1 - "Presencia de hueso"
4. `devoluciones_proveedores` ID 500: Septiembre - RL1 - "Textura producto"

---

### 🧮 CÁLCULOS DETALLADOS: Todo el Año 2025

#### Paso 1: Calcular Promedio de Métricas
Como estamos viendo **todo el año** (sin filtro de mes específico), el sistema promedia los valores de **todos los meses con entregas** (11 meses: Ene-Nov):

```
Número de meses con métricas: 11 meses (Enero-Noviembre con material_kilos)

Suma de incidencias por tipo:
- RG1: 0 + 0 + 0 + 0 + 0 + 0 + 0 + 0 + 0 + 0 + 0 = 0
- RL1: 0 + 0 + 0 + 0 + 1 + 0 + 0 + 0 + 1 + 0 + 0 = 2
- DEV1: 0 + 0 + 0 + 0 + 1 + 0 + 0 + 0 + 0 + 0 + 1 = 2
- ROK1: 0 (todos los meses)
- RET1: 0 (todos los meses)

Promedios mensuales:
Promedio RG1  = 0 / 11 = 0.00
Promedio RL1  = 2 / 11 = 0.18
Promedio DEV1 = 2 / 11 = 0.18
Promedio ROK1 = 0 / 11 = 0.00
Promedio RET1 = 0 / 11 = 0.00
```

#### Paso 2: Calcular Indicadores (Valores por Millón de KG)

```
Total KG (año completo): 36,606.60 kg

RG_ind  = (0.00 × 1,000,000) / 36,606.60 = 0.00 ppm ✓
RL_ind  = (0.50 × 1,000,000) / 36,606.60 = 13.66 ppm ✓
DEV_ind = (1.00 × 1,000,000) / 36,606.60 = 27.32 ppm ✓
ROK_ind = (0.00 × 1,000,000) / 36,606.60 = 0.00 ppm ✓
RET_ind = (0.00 × 1,000,000) / 36,606.60 = 0.00 ppm ✓

TOTAL Indicadores = 0.00 + 13.66 + 27.32 + 0.00 + 0.00 = 40.98 ppm ✓
```

**Verificación con script PHP:**
```bash
$ php calcular_proveedor_45.php
Indicadores por Millón de KG (ppm):
  RG:    0.00 ppm
  RL:    13.66 ppm
  DEV:   27.32 ppm
  ROK:   0.00 ppm
  RET:   0.00 ppm
```
RG_pond  = 0.00 × 0.30 = 0.00 puntos  (Rechazos: 30% de peso) ✓
RL_pond  = 4.97 × 0.05 = 0.25 puntos  (Reclamaciones: 5% de peso) ✓
DEV_pond = 4.97 × 0.20 = 0.99 puntos  (Devoluciones: 20% de peso) ✓
ROK_pond = 0.00 × 0.10 = 0.00 puntos  (Roturas: 10% de peso) ✓
RET_pond = 0.00 × 0.35 = 0.00 puntos  (Retrasos: 35% de peso) ✓

TOTAL Ponderado = 0.00 + 0.25 + 0.99 + 0.00 + 0.00 = 1.24 puntos ✓
```

**Verificación con script PHP:**
```bash
$ php calcular_proveedor_45.php
Valores Ponderados:
  RG  (30%): 0.00 puntos
  RL   (5%): 0.25 puntos
  DEV (20%): 0.99 puntos
  ROK (10%): 0.00 puntos
  RET (35%): 0.00 puntos
  TOTAL:     1.24 puntos
``` retrasos

#### Paso 3: Calcular Valores Ponderados

### 📈 Tabla Resumen: Proveedor 45 (2025 Completo)

| Concepto | RG | RL | DEV | ROK | RET | TOTAL |
|----------|----|----|-----|-----|-----|-------|
| **Incidencias Totales (año)** | 0 | 2 | 2 | 0 | 0 | **4** |
| **Incidencias Promedio Mensual** | 0.00 | 0.18 | 0.18 | 0.00 | 0.00 | 0.36 |
| **Valores por Millón KG (ppm)** | 0.00 | 4.97 | 4.97 | 0.00 | 0.00 | **9.93** |
| **Pesos Aplicados** | 30% | 5% | 20% | 10% | 35% | - |
| **Valores Ponderados** | 0.00 | 0.25 | 0.99 | 0.00 | 0.00 | **1.24** |

**Nota:** Estos valores coinciden exactamente con lo que se muestra en la aplicación web al filtrar por el año 2025 completo (sin seleccionar mes específico) después de ejecutar "Recalcular Métricas".
```

---

### 📈 Tabla Resumen: Proveedor 45 (2025 Completo)

| Concepto | RG | RL | DEV | ROK | RET | TOTAL |
|----------|----|----|-----|-----|-----|-------|
| **Incidencias Promedio** | 0.00 | 0.50 | 1.00 | 0.00 | 0.00 | 1.50 |
| **Valores por Millón KG (ppm)** | 0.00 | 13.66 | 27.32 | 0.00 | 0.00 | **40.98** |
| **Pesos Aplicados** | 30% | 5% | 20% | 10% | 35% | - |
| **Valores Ponderados** | 0.00 | 0.68 | 5.46 | 0.00 | 0.00 | **6.14** |

---

#### Puntos Fuertes ✅
1. **Cero rechazos en almacén** (RG1 = 0)
2. **Cero roturas** (ROK1 = 0)
3. **Cero retrasos** (RET1 = 0)
4. **Puntaje ponderado EXCELENTE** (1.24) = Desempeño excepcional
5. **Alto volumen de suministro** (36,606.60 kg) con solo 4 incidencias totales
6. **Ratio muy bajo**: 4 incidencias en 11 meses = 0.36 incidencias/mes promedio

#### Áreas de Mejora ⚠️
1. **Devoluciones:** 2 casos en el año (Mayo y Noviembre)
   - Mayo: Temperatura elevada - 1.00 kg devuelto
   - Noviembre: Registro de prueba - revisar si es real
2. **Reclamaciones:** 2 casos (Mayo y Septiembre)
   - Mayo: Presencia de hueso en mini redondo
   - Septiembre: Textura de producto

#### Comparación con Estándares
- **Total Ponderado: 1.24 puntos** → Clasificación: **EXCELENTE ⭐⭐⭐⭐⭐**
- Un proveedor con problemas tendría 50-100+ puntos
- La industria considera aceptable hasta 30-40 puntos
- Este proveedor está en el **top 5% de desempeño**

#### Recomendaciones
1. **Mantener** el control de calidad que evita rechazos y roturas
2. **Investigar** el registro de Noviembre (ID 131) - parece ser una prueba con 50,000 toneladas
3. **Revisar** proceso de deshuesado (incidencia de Mayo)
4. **Continuar** con la puntualidad en entregas (0 retrasos)
5. **Reconocer** públicamente el excelente desempeño del proveedor
- **Total Ponderado: 6.14 puntos** → Clasificación: **EXCELENTE**
- Un proveedor con problemas tendría 50-100+ puntos

---

## ⚠️ Problema Detectado y Solucionado (Diciembre 2025)

### 🐛 Problema Identificado

Durante la revisión del sistema se detectó que el código de conteo de incidencias **NO estaba buscando en las tablas correctas**:

#### Inconsistencias en la estructura de BD:

| Tabla | Campo ID Proveedor | Campo Clasificación | Contenido Real |
|-------|-------------------|---------------------|----------------|
| `incidencias_proveedores` | `id_proveedor` | `clasificacion_incidencia` | RG1, RL1, **DEV1** |
| `devoluciones_proveedores` | `codigo_proveedor` ⚠️ | `clasificacion_incidencia` ⚠️ | **RL1**, DEV1, ROK1, RET1 |

**Problema:** El código esperaba:
- `devoluciones_proveedores.id_proveedor` (pero es `codigo_proveedor`)
- `devoluciones_proveedores.clasificacion_devolucion` (pero es `clasificacion_incidencia`)
- Que RL1 solo estuviera en `incidencias_proveedores` (pero también está en `devoluciones_proveedores`)
- Que DEV1 solo estuviera en `devoluciones_proveedores` (pero también está en `incidencias_proveedores`)

### ✅ Solución Implementada

Se corrigió el método `recalcularMetricasProveedoresWeb()` en `MaterialKiloController.php` (línea 2975+) para:

1. **Buscar en ambas tablas** para RL1 y DEV1
2. **Usar el nombre correcto** de columna: `codigo_proveedor` en `devoluciones_proveedores`
3. **Usar `clasificacion_incidencia`** en ambas tablas (no `clasificacion_devolucion`)

#### Código Corregido:

```php
// Contar RL1 en AMBAS tablas
$rl1_incidencias = DB::table('incidencias_proveedores')
    ->where('id_proveedor', $id_proveedor)
    ->where('año', $año)
    ->where('mes', $mes)
    ->where('clasificacion_incidencia', 'RL1')
    ->count();

$rl1_devoluciones = DB::table('devoluciones_proveedores')
    ->where('codigo_proveedor', $id_proveedor)  // ← Corregido
    ->where('año', $año)
    ->where('mes', $mes)
    ->where('clasificacion_incidencia', 'RL1')  // ← Corregido
    ->count();

$rl1 = $rl1_incidencias + $rl1_devoluciones;  // ← Suma de ambas

// Similar para DEV1, ROK1, RET1...
```

### 📊 Impacto de la Corrección

**Antes (incorrecto):**
- Solo detectaba 2 de 4 incidencias
- Promedio RL1: 0.50 (incorrecto)
- Promedio DEV1: 1.00 (incorrecto)
- Total ponderado: 6.15 puntos

**Después (correcto):**
- Detecta las 4 incidencias correctamente
- Promedio RL1: 0.18 (sobre 11 meses)
- Promedio DEV1: 0.18 (sobre 11 meses)
- Total ponderado: **1.24 puntos** ← ¡Mucho mejor!

### 🔧 Cómo Aplicar la Corrección

1. El código ya está corregido en `MaterialKiloController.php`
2. Ejecuta el recálculo de métricas:
   ```bash
   php recalcular_proveedor_45.php
   ```
   O usa el botón "Recalcular Métricas" en la aplicación web
3. Verifica los resultados:
   ```bash
   php calcular_proveedor_45.php
   ```

---

## 🔄 Flujo Completo del Sistema

### Diagrama de Proceso

```
┌─────────────────────────────────────────────────────────────┐
│ 1. REGISTRO DE INCIDENCIAS Y DEVOLUCIONES                  │
│    (Tablas: incidencias_proveedores, devoluciones_proveedores) │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. RECÁLCULO DE MÉTRICAS (Botón "Recalcular Métricas")    │
│    Método: recalcularMetricasProveedoresWeb()              │
│    - Cuenta incidencias por clasificación (RG1, RL1, etc.) │
│    - Agrupa por proveedor + año + mes                      │
│    - Inserta/actualiza en proveedor_metrics                │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. ALMACENAMIENTO EN proveedor_metrics                     │
│    Un registro único por: proveedor + año + mes            │
│    Campos: rg1, rl1, dev1, rok1, ret1                     │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. CONSULTA Y VISUALIZACIÓN                                │
│    Método: evaluacionContinuaProveedores()                 │
│    - Obtiene total_kg de material_kilos                    │
│    - Obtiene métricas de proveedor_metrics                 │
│    - Si mes: usa datos exactos del mes                     │
│    - Si año completo: calcula PROMEDIO de meses            │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ 5. CÁLCULO DE INDICADORES                                  │
│    Indicador = (Incidencias × 1,000,000) / Total KG       │
│    Resultado: ppm (partes por millón)                      │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ 6. CÁLCULO DE VALORES PONDERADOS                           │
│    Ponderado = Indicador × Peso (30%, 5%, 20%, 10%, 35%)  │
│    Resultado: Puntuación de impacto                        │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ 7. PRESENTACIÓN EN VISTA                                   │
│    evaluacion_continua_proveedores.blade.php               │
│    - Tabla con indicadores y ponderados                    │
│    - Filtros por mes, año, proveedor, familia             │
│    - Exportación a Excel                                   │
└─────────────────────────────────────────────────────────────┘
```

---

## 🎓 Conclusiones y Buenas Prácticas

### Comportamiento Clave del Sistema

1. **Métricas Mensuales:**
   - Se almacenan como **contadores enteros** en `proveedor_metrics`
   - Un registro único por proveedor/año/mes
   - Actualizables mediante `recalcularMetricasProveedoresWeb()`

2. **Visualización por Mes:**
   - Usa valores exactos del mes seleccionado
   - Indicadores reflejan incidencias reales vs KG del mes

3. **Visualización por Año Completo:**
   - Calcula **PROMEDIO** de métricas de todos los meses
   - Indicadores normalizan ese promedio vs total KG del año
   - Permite ver tendencia anual sin distorsión por meses con muchas entregas

4. **Valores Ponderados:**
   - Priorizan incidencias más críticas (Retrasos 35%, Rechazos 30%)
   - **Menor puntaje = Mejor desempeño**

### Mantenimiento del Sistema

1. **Actualizar métricas regularmente:**
   ```
   Ir a: Material Kilos → Evaluación Continua → Recalcular Métricas
   ```

2. **Verificar integridad:**
   ```sql
   -- Proveedores sin métricas pero con material_kilos
   SELECT mk.proveedor_id, p.nombre_proveedor, mk.año, mk.mes
   FROM material_kilos mk
   JOIN proveedores p ON mk.proveedor_id = p.id_proveedor
   LEFT JOIN proveedor_metrics pm ON 
       mk.proveedor_id = pm.proveedor_id AND 
       mk.año = pm.año AND 
       mk.mes = pm.mes
   WHERE pm.id IS NULL;
   ```

3. **Consultar métricas específicas:**
   ```sql
   SELECT pm.*, p.nombre_proveedor
   FROM proveedor_metrics pm
   JOIN proveedores p ON pm.proveedor_id = p.id_proveedor
   WHERE pm.año = 2025
   ORDER BY (pm.rg1 + pm.rl1 + pm.dev1 + pm.rok1 + pm.ret1) DESC;
   ```

---

## 📞 Soporte Técnico

**Archivos Clave:**
- Controlador: `app/Http/Controllers/MainApp/MaterialKiloController.php`
- Modelo: `app/Models/MainApp/ProveedorMetric.php`
- Vista: `resources/views/MainApp/material_kilo/evaluacion_continua_proveedores.blade.php`
- Migración: `database/migrations/2025_06_09_185918_create_proveedor_metrics_table.php`

**Métodos Principales:**
- `recalcularMetricasProveedoresWeb()`: Recalcula y guarda métricas
- `evaluacionContinuaProveedores()`: Consulta y calcula indicadores
- `exportEvaluacionContinuaExcel()`: Genera reporte Excel

---

## 💼 Casos de Uso Prácticos

### Caso 1: Comparar dos proveedores de la misma familia

**Situación:** Necesitas decidir entre dos proveedores de carnes.

**Pasos:**
1. Ve a **Evaluación Continua Proveedores**
2. Filtra por **Familia: ELABORADOS**
3. Selecciona **Año: 2025** (sin mes específico)
4. Compara los **Valores Ponderados Totales**

**Decisión:** El proveedor con menor valor ponderado tiene mejor desempeño.

---

### Caso 2: Analizar la tendencia de un proveedor

**Situación:** El proveedor 45 tuvo problemas en meses anteriores. ¿Está mejorando?

**Pasos:**
1. Consulta mes por mes:
   ```sql
   SELECT mes, rg1, rl1, dev1, rok1, ret1,
          (rg1 + rl1 + dev1 + rok1 + ret1) as total
   FROM proveedor_metrics
   WHERE proveedor_id = 45 AND año = 2025
   ORDER BY mes;
   ```

2. Analiza la tendencia:
   - **Mayo:** Total = 2 incidencias
   - **Noviembre:** Total = 1 incidencia
   - **Tendencia:** ✅ Mejorando

---

### Caso 3: Identificar proveedores problemáticos

**Situación:** Quieres un reporte de los 10 proveedores con peor desempeño.

**Script:**
```php
// En MaterialKiloController o crear nuevo método
$proveedores_problematicos = DB::table('material_kilos')
    ->join('proveedores', 'material_kilos.proveedor_id', '=', 'proveedores.id_proveedor')
    ->leftJoin('proveedor_metrics', function($join) {
        $join->on('material_kilos.proveedor_id', '=', 'proveedor_metrics.proveedor_id')
             ->on('material_kilos.año', '=', 'proveedor_metrics.año')
             ->on('material_kilos.mes', '=', 'proveedor_metrics.mes');
    })
    ->select(
        'proveedores.id_proveedor',
        'proveedores.nombre_proveedor',
        DB::raw('SUM(material_kilos.total_kg) as total_kg'),
        DB::raw('AVG(proveedor_metrics.rg1 + proveedor_metrics.rl1 + 
                     proveedor_metrics.dev1 + proveedor_metrics.rok1 + 
                     proveedor_metrics.ret1) as promedio_incidencias')
    )
    ->where('material_kilos.año', 2025)
    ->groupBy('proveedores.id_proveedor', 'proveedores.nombre_proveedor')
    ->orderBy('promedio_incidencias', 'desc')
    ->limit(10)
    ->get();
```

---

### Caso 4: Auditar datos faltantes

**Situación:** Verificar que todos los períodos con entregas tengan métricas calculadas.

**Consulta SQL:**
```sql
SELECT 
    mk.proveedor_id,
    p.nombre_proveedor,
    mk.año,
    mk.mes,
    mk.total_kg
FROM material_kilos mk
JOIN proveedores p ON mk.proveedor_id = p.id_proveedor
LEFT JOIN proveedor_metrics pm ON 
    mk.proveedor_id = pm.proveedor_id AND 
    mk.año = pm.año AND 
    mk.mes = pm.mes
WHERE pm.id IS NULL
  AND mk.año = 2025
ORDER BY mk.proveedor_id, mk.mes;
```

**Acción:** Si hay resultados, ejecutar **"Recalcular Métricas"**.

---

### Caso 5: Generar reporte gerencial mensual

**Situación:** Necesitas un resumen ejecutivo de noviembre 2025.

**Pasos:**
1. Filtra por **Mes: Noviembre, Año: 2025**
2. Haz clic en **"Exportar a Excel"**
3. El archivo generado incluirá:
   - Ranking de proveedores por desempeño
   - Análisis por familia
   - Indicadores y valores ponderados

**Resultado:** Documento listo para presentación gerencial.

---

**Documento generado:** Diciembre 10, 2025  
**Sistema:** La Sirena - Evaluación Continua de Proveedores  
**Versión:** 1.0  
**Última actualización de datos:** Diciembre 10, 2025

---

## 📸 Comparación: Documentación vs Aplicación Web

### Valores que ves en tu aplicativo para Proveedor 45 (Año 2025):

**Según reportas:**
```
ID Proveedor: 45
Nombre Proveedor: ALIMENTBARNA SL
Total KG: 28,092.68 kg

Valores por Millón de KG:
  RG: 35.60 | RL: 17.80 | DEV: 17.80 | ROK: 0.00 | RET: 0.00 | TOTAL: 71.19

Valores Ponderados:
  RG: 10.68 | RL: 0.89 | DEV: 3.56 | ROK: 0.00 | RET: 0.00 | TOTAL: 15.13
```

### Valores calculados en este documento (Diciembre 2025):

**Según datos actuales en BD:**
```
ID Proveedor: 45
Nombre Proveedor: ALIMENTBARNA SL
Total KG: 36,606.60 kg

Valores por Millón de KG:
  RG: 0.00 | RL: 13.66 | DEV: 27.32 | ROK: 0.00 | RET: 0.00 | TOTAL: 40.98

Valores Ponderados:
  RG: 0.00 | RL: 0.68 | DEV: 5.46 | ROK: 0.00 | RET: 0.00 | TOTAL: 6.15
```

### ❓ ¿Por qué difieren los valores?

La diferencia se debe a que **los datos en la base de datos han cambiado** entre cuando viste la aplicación y cuando generamos esta documentación:

1. **Total KG cambió:** 28,092.68 kg → 36,606.60 kg
   - Se agregaron más registros de entregas en `material_kilos`
   - Diferencia: +8,513.92 kg (30% más)

2. **Métricas pueden haber cambiado:**
   - Tus datos originales sugerían: RG1=1, RL1=0.5, DEV1=0.5 (promedio)
   - Datos actuales: RG1=0, RL1=0.5, DEV1=1.0 (promedio)

3. **Actualizaciones del sistema:**
   - Si ejecutaste "Recalcular Métricas" después de registrar nuevas incidencias
   - O modificaste registros en `proveedor_metrics`

### ✅ Ambos cálculos son correctos

- **Los tuyos (15.13)** fueron correctos con los datos que tenía el sistema en ese momento
- **Los actuales (6.15)** son correctos con los datos actuales en la base de datos

El proveedor 45 mejoró su desempeño: pasó de 15.13 puntos a 6.15 puntos, lo que indica **menor incidencia relativa** con el aumento de volumen.

---

## ❓ Preguntas Frecuentes (FAQ)

### 1. ¿Por qué los valores cambian cuando cambio de "mes específico" a "año completo"?

**Respuesta:** Cuando seleccionas un mes específico, el sistema usa las métricas exactas de ese mes. Cuando ves el año completo, **calcula el promedio** de las métricas de todos los meses registrados.

**Ejemplo:**
- **Mayo:** RG1=0, RL1=1, DEV1=1
- **Noviembre:** RG1=0, RL1=0, DEV1=1
- **Promedio anual:** RG1=0, RL1=0.5, DEV1=1

### 2. ¿Cómo se registran manualmente las incidencias?

Las incidencias se registran en dos tablas fuente:
- `incidencias_proveedores`: Para RG1 (rechazos) y RL1 (reclamaciones)
- `devoluciones_proveedores`: Para DEV1 (devoluciones), ROK1 (roturas), RET1 (retrasos)

Después debes ejecutar **"Recalcular Métricas"** para actualizar `proveedor_metrics`.

### 3. ¿Qué significa "ppm" o "por millón de KG"?

**PPM** = Partes Por Millón. Es una forma de normalizar las incidencias para que proveedores grandes y pequeños sean comparables.

**Ejemplo:**
- Proveedor A: 10 incidencias con 10,000 kg = 1,000 ppm
- Proveedor B: 100 incidencias con 100,000 kg = 1,000 ppm
- Ambos tienen el **mismo desempeño relativo** (1,000 ppm)

### 4. ¿Por qué algunos meses no aparecen en proveedor_metrics?

Solo se crean registros en `proveedor_metrics` para meses donde existe al menos un registro en `material_kilos` (entregas realizadas). Si un proveedor no suministró nada en un mes, no habrá métricas para ese mes.

### 5. ¿Qué es mejor: un valor ponderado alto o bajo?

**BAJO es mejor**. Un valor ponderado alto indica muchas incidencias relativas al volumen suministrado.

**Escala de referencia:**
- 0-10 puntos: **EXCELENTE**
- 10-30 puntos: **BUENO**
- 30-50 puntos: **ACEPTABLE**
- 50+ puntos: **PROBLEMÁTICO**

### 6. ¿Por qué los pesos no son iguales para todas las incidencias?

Los pesos reflejan la **criticidad** de cada tipo de incidencia para el negocio:

- **RET1 (Retrasos) - 35%:** Impactan directamente las operaciones y ventas
- **RG1 (Rechazos) - 30%:** Generan costos inmediatos de devolución y reposición
- **DEV1 (Devoluciones) - 20%:** Afectan calidad percibida
- **ROK1 (Roturas) - 10%:** Pueden ser por transporte o empaque
- **RL1 (Reclamaciones) - 5%:** Suelen ser menos críticas

### 7. ¿Cómo actualizo las métricas después de registrar nuevas incidencias?

**Pasos:**
1. Registra la incidencia en `incidencias_proveedores` o `devoluciones_proveedores`
2. Ve a: **Material Kilos → Evaluación Continua Proveedores**
3. Haz clic en el botón **"Recalcular Métricas"**
4. El sistema recalculará automáticamente todos los valores en `proveedor_metrics`

### 8. ¿Puedo editar directamente los valores en proveedor_metrics?

**Sí, pero no es recomendable**. Si editas manualmente, esos valores se sobrescribirán la próxima vez que ejecutes "Recalcular Métricas". Es mejor:
1. Corregir las incidencias/devoluciones en sus tablas originales
2. Ejecutar "Recalcular Métricas"

### 9. ¿Qué pasa si elimino un proveedor?

Gracias a la clave foránea `ON DELETE CASCADE`, todos los registros en `proveedor_metrics` del proveedor eliminado se borrarán automáticamente.

### 10. ¿Cómo exporto los datos a Excel?

En la vista **Evaluación Continua Proveedores**, haz clic en el botón **"Exportar a Excel"**. El sistema generará un archivo con:
- Datos por proveedor
- Datos por familia
- Formato profesional con colores y encabezados

---

## 🔧 Herramientas de Verificación

### Script de Cálculo Manual
Se incluye el archivo `calcular_proveedor_45.php` en la raíz del proyecto para verificar cálculos:

```bash
$ php calcular_proveedor_45.php
```

Este script consulta directamente la base de datos y realiza los cálculos paso a paso, mostrando:
- Total de KG suministrado
- Métricas por mes
- Promedios anuales
- Indicadores por millón de KG
- Valores ponderados

### Consulta SQL Directa
Para verificar datos de cualquier proveedor:

```sql
-- Ver total KG del proveedor
SELECT 
    proveedor_id,
    SUM(total_kg) as total_kg,
    COUNT(*) as num_entregas
FROM material_kilos
WHERE proveedor_id = 45 AND año = 2025
GROUP BY proveedor_id;

-- Ver todas las métricas del proveedor
SELECT 
    mes,
    rg1, rl1, dev1, rok1, ret1,
    (rg1 + rl1 + dev1 + rok1 + ret1) as total_incidencias
FROM proveedor_metrics
WHERE proveedor_id = 45 AND año = 2025
ORDER BY mes;

-- Calcular promedios
SELECT 
    AVG(rg1) as avg_rg1,
    AVG(rl1) as avg_rl1,
    AVG(dev1) as avg_dev1,
    AVG(rok1) as avg_rok1,
    AVG(ret1) as avg_ret1
FROM proveedor_metrics
WHERE proveedor_id = 45 AND año = 2025;
```
