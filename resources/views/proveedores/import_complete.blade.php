<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="2;url=/proveedores">
    <title>Importación Completada</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .completion-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 600px;
            text-align: center;
        }
        .icon-success {
            font-size: 64px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .message {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        .redirect-info {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 30px;
        }
        .spinner-border {
            width: 1rem;
            height: 1rem;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="completion-card">
        <div class="icon-success">✓</div>
        <h2 class="mb-3">Importación Completada</h2>
        <div class="alert alert-success message">
            {{ $message ?? 'Importación finalizada correctamente' }}
        </div>
        <p class="redirect-info">
            <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
            Redirigiendo a la lista de proveedores...
        </p>
        <a href="/proveedores" class="btn btn-primary btn-lg">
            <i class="fa fa-arrow-right"></i> Ir ahora a Proveedores
        </a>
    </div>

    <script>
        // LOG INMEDIATO para verificar que el script se ejecutó
        console.log('🚀 SCRIPT CARGADO - import_complete.blade.php');
        console.log('📍 URL actual:', window.location.href);
        console.log('📄 readyState:', document.readyState);
        
        // SOLUCIÓN DEFINITIVA: Esperar a que el DOM cargue completamente
        // Esto asegura que la página se muestre antes de redirigir
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✓ DOM completamente cargado - Iniciando redirección en 1 segundo');
            
            var redirectUrl = '/proveedores';
            
            // Esperar 1 segundo para que el usuario vea el mensaje de éxito
            setTimeout(function() {
                console.log('→ Redirigiendo a: ' + redirectUrl);
                window.location.href = redirectUrl;
            }, 1000);
            
            // Respaldo con replace después de 1.5 segundos
            setTimeout(function() {
                console.log('→ Intento de respaldo con replace()');
                window.location.replace(redirectUrl);
            }, 1500);
            
            // Último intento con assign después de 2 segundos
            setTimeout(function() {
                console.log('→ Último intento con assign()');
                window.location.assign(redirectUrl);
            }, 2000);
        });
        
        // Fallback de seguridad: si DOMContentLoaded ya pasó (página cacheada)
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            console.log('⚡ DOM ya estaba listo - Redirección inmediata');
            setTimeout(function() {
                window.location.href = '/proveedores';
            }, 1000);
        }
    </script>
</body>
</html>
