<!DOCTYPE html>
<html>
<head>
    <title>Test XLSX Upload</title>
</head>
<body>
    <h1>Test XLSX Upload</h1>
    
    @if (session('success'))
        <div style="color: green; padding: 10px; border: 1px solid green; margin: 10px 0;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="color: red; padding: 10px; border: 1px solid red; margin: 10px 0;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('test.upload.post') }}" enctype="multipart/form-data">
        @csrf
        <div>
            <label for="archivo">Seleccionar Archivo XLSX:</label>
            <input type="file" name="archivo" id="archivo" accept=".xlsx" required>
        </div>
        <br>
        <button type="submit">Subir Archivo</button>
    </form>
    
    <script>
        // Para debuggear el envío del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            console.log('Formulario enviado');
            console.log('Archivo seleccionado:', document.getElementById('archivo').files[0]);
        });
    </script>
</body>
</html>
