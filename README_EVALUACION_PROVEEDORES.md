# 📊 Sistema de Evaluación Continua de Proveedores - Quick Start

## 🎯 ¿Qué es?

Sistema que evalúa proveedores mediante indicadores normalizados (ppm) y valores ponderados, permitiendo comparaciones justas independientemente del volumen de suministro.

## 📁 Documentación Completa

👉 **[Ver Documentación Completa](./DOCUMENTACION_SISTEMA_EVALUACION_PROVEEDORES.md)**

## 🚀 Inicio Rápido

### 1. Registrar Incidencias

Las incidencias se registran en:
- `incidencias_proveedores` → RG1 (rechazos), RL1 (reclamaciones)
- `devoluciones_proveedores` → DEV1 (devoluciones), ROK1 (roturas), RET1 (retrasos)

### 2. Actualizar Métricas

```
Material Kilos → Evaluación Continua → Botón "Recalcular Métricas"
```

### 3. Visualizar Resultados

Accede a la vista y filtra por:
- Mes / Año
- Proveedor específico
- Familia de productos

### 4. Exportar Reportes

Botón "Exportar a Excel" genera reporte completo con:
- Datos por proveedor
- Análisis por familia
- Indicadores y valores ponderados

## 📊 Ejemplo Real: Proveedor 45 (ALIMENTBARNA SL)

```
Total KG (2025):     36,606.60 kg
Incidencias (ppm):   40.98 ppm
Valor Ponderado:     6.15 puntos → EXCELENTE
```

### Desglose:
| Métrica | RG | RL | DEV | ROK | RET | TOTAL |
|---------|----|----|-----|-----|-----|-------|
| **Indicadores (ppm)** | 0.00 | 13.66 | 27.32 | 0.00 | 0.00 | 40.98 |
| **Ponderados** | 0.00 | 0.68 | 5.46 | 0.00 | 0.00 | **6.15** |

## 🔑 Conceptos Clave

### Valores por Millón de KG (ppm)
```
Indicador = (Incidencias × 1,000,000) / Total KG
```
Normaliza incidencias para comparar proveedores de diferentes tamaños.

### Valores Ponderados
```
Ponderado = Indicador × Peso
```
Aplica importancia según criticidad:
- RET (Retrasos): 35%
- RG (Rechazos): 30%
- DEV (Devoluciones): 20%
- ROK (Roturas): 10%
- RL (Reclamaciones): 5%

**📉 Menor puntuación = Mejor desempeño**

## 🗂️ Tablas Principales

```
incidencias_proveedores  ┐
devoluciones_proveedores ┤→ proveedor_metrics ←→ material_kilos
                         ┘       (métricas)        (volumen KG)
```

## 🔧 Script de Verificación

Para verificar cálculos de cualquier proveedor:

```bash
$ php calcular_proveedor_45.php
```

(Edita el archivo para cambiar el ID del proveedor)

## ⚖️ Escala de Evaluación

| Puntos | Clasificación |
|--------|---------------|
| 0-10   | EXCELENTE ✅  |
| 10-30  | BUENO 👍      |
| 30-50  | ACEPTABLE ⚠️  |
| 50+    | PROBLEMÁTICO ❌|

## 📞 Archivos del Sistema

- **Controlador:** `app/Http/Controllers/MainApp/MaterialKiloController.php`
- **Modelo:** `app/Models/MainApp/ProveedorMetric.php`
- **Vista:** `resources/views/MainApp/material_kilo/evaluacion_continua_proveedores.blade.php`
- **Migración:** `database/migrations/2025_06_09_185918_create_proveedor_metrics_table.php`

## 🆘 Soporte

Para dudas sobre cálculos, estructura de datos, o interpretación de resultados, consulta la **[Documentación Completa](./DOCUMENTACION_SISTEMA_EVALUACION_PROVEEDORES.md)** que incluye:

- ✅ Explicación detallada de cada tabla
- ✅ Diagramas de flujo
- ✅ Ejemplos paso a paso
- ✅ Preguntas frecuentes (FAQ)
- ✅ Casos de uso prácticos
- ✅ Scripts SQL de verificación

---

**Sistema:** La Sirena - Evaluación Continua de Proveedores  
**Versión:** 1.0 | **Fecha:** Diciembre 2025
