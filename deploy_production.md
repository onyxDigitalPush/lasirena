# Comandos para Deploy en Producción

## 1. Optimizar caches
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 2. Instalar dependencias optimizadas
```bash
composer install --optimize-autoloader --no-dev
```

## 3. Generar key si es necesario
```bash
php artisan key:generate
```

## 4. Ejecutar migraciones
```bash
php artisan migrate --force
```

## 5. Limpiar caches si hay problemas
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

## 6. Verificar permisos
- `storage/` debe tener permisos de escritura (755 o 775)
- `bootstrap/cache/` debe tener permisos de escritura

## 7. Configuración del servidor web
- Apuntar el DocumentRoot a la carpeta `public/`
- Configurar URL rewriting para Laravel
