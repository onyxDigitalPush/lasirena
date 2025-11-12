<?php

namespace App\Http\Controllers\MainApp;

use App\Http\Controllers\Controller;
use App\Models\MainApp\EmailProveedor;
use App\Models\MainApp\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailProveedorController extends Controller
{
    public function enviar(Request $request)
    {
        $request->validate([
            'id_proveedor' => 'required|exists:proveedores,id_proveedor',
            'asunto' => 'required|max:255',
            'mensaje' => 'required',
            'email_remitente' => 'required|email',
            'emails_destinatarios' => 'required|string',
            'emails_bcc' => 'nullable|string',
            'archivo1' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip,rar,txt',
            'archivo2' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip,rar,txt',
            'archivo3' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip,rar,txt',
            'id_incidencia_proveedor' => 'nullable|integer|exists:incidencias_proveedores,id',
            'id_devolucion_proveedor' => 'nullable|integer|exists:devoluciones_proveedores,id',
        ]);

        // Normalizar y validar destinatarios
        $rawTo = trim($request->emails_destinatarios);
        $rawTo = str_replace([';', ' '], [',', ''], $rawTo);
        $toArray = array_filter(array_map('trim', explode(',', $rawTo)));
        foreach ($toArray as $email) {
            if (!preg_match('/^[^@]+@[^@]+\.[^@]+$/', $email)) {
                return redirect()->back()->with('error', "El destinatario '$email' debe tener el formato texto@texto.dominio");
            }
        }

        // Normalizar y validar bcc
        $bccArray = [];
        if ($request->emails_bcc) {
            $rawBcc = trim($request->emails_bcc);
            $rawBcc = str_replace([';', ' '], [',', ''], $rawBcc);
            $bccArray = array_filter(array_map('trim', explode(',', $rawBcc)));
            foreach ($bccArray as $email) {
                if (!preg_match('/^[^@]+@[^@]+\.[^@]+$/', $email)) {
                    return redirect()->back()->with('error', "El BCC '$email' debe tener el formato texto@texto.dominio");
                }
            }
        }

        if (empty($toArray) && empty($bccArray)) {
            return redirect()->back()->with('error', 'Lista de destinatarios inválida.');
        }

        // Guardar archivos con nombres únicos pero conservar nombres originales
        $archivos = [];
        foreach (['archivo1', 'archivo2', 'archivo3'] as $input) {
            if ($request->hasFile($input)) {
                $file = $request->file($input);
                $nombreOriginal = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $nombreUnico = pathinfo($nombreOriginal, PATHINFO_FILENAME) . '_' . time() . '_' . uniqid();
                $nombreConExtension = $nombreUnico . '.' . $extension;
                $path = $file->storeAs("emails_proveedores/temp", $nombreConExtension, 'public');
                $archivos[$nombreOriginal] = $path;
            }
        }

        $enviadosTo = [];
        $enviadosBcc = [];

        // Enviar a cada destinatario individualmente
        foreach ($toArray as $destinatario) {
            try {
                Mail::send([], [], function ($message) use ($request, $archivos, $destinatario, $bccArray) {
                    $message->from($request->email_remitente)
                        ->to($destinatario)
                        ->subject($request->asunto);
                    $bodyHtml = nl2br(e($request->mensaje));
                    $message->setBody($bodyHtml, 'text/html');
                    foreach ($archivos as $nombreOriginal => $rutaUnica) {
                        $filePath = storage_path('app/public/' . $rutaUnica);
                        if (file_exists($filePath)) {
                            $message->attach($filePath, ['as' => $nombreOriginal]);
                        }
                    }
                });
                $enviadosTo[] = $destinatario;
            } catch (\Exception $e) {
                Log::error('Error enviando email a destinatario: ' . $destinatario . ' - ' . $e->getMessage());
            }
        }

        // Enviar a cada BCC individualmente (si quieres que cada uno reciba su propio email)
        foreach ($bccArray as $bcc) {
            try {
                Mail::send([], [], function ($message) use ($request, $archivos, $bcc) {
                    $message->from($request->email_remitente)
                        ->to($bcc)
                        ->subject($request->asunto);
                    $bodyHtml = nl2br(e($request->mensaje));
                    $message->setBody($bodyHtml, 'text/html');
                    foreach ($archivos as $nombreOriginal => $rutaUnica) {
                        $filePath = storage_path('app/public/' . $rutaUnica);
                        if (file_exists($filePath)) {
                            $message->attach($filePath, ['as' => $nombreOriginal]);
                        }
                    }
                });
                $enviadosBcc[] = $bcc;
            } catch (\Exception $e) {
                Log::error('Error enviando email a BCC: ' . $bcc . ' - ' . $e->getMessage());
            }
        }

        // Si al menos uno se envió, guardar registro con los que sí se enviaron
        if (!empty($enviadosTo) || !empty($enviadosBcc)) {
            $email = new EmailProveedor([
                'id_proveedor' => (int) $request->id_proveedor,
                'id_incidencia_proveedor' => $request->id_incidencia_proveedor,
                'id_devolucion_proveedor' => $request->id_devolucion_proveedor,
                'email_remitente' => $request->email_remitente,
                'emails_destinatarios' => implode(',', $enviadosTo),
                'emails_bcc' => implode(',', $enviadosBcc),
                'asunto' => $request->asunto,
                'mensaje' => $request->mensaje,
                'enviado' => 1,
                'ruta_archivos' => $archivos ? json_encode($archivos) : null
            ]);
            $email->save();

            $msg = "Se enviaron y guardaron los correos.";
            return redirect()->back()->with('success', $msg);
        } else {
            return redirect()->back()->with('error', 'No se pudo enviar ningún correo.');
        }
    }

    public function historial($id)
    {
        $emails = EmailProveedor::where('id_proveedor', $id)->orderBy('created_at', 'desc')->get();

        // Procesar archivos para generar URLs con nombres originales
        foreach ($emails as $email) {
            if ($email->ruta_archivos) {
                $archivos = json_decode($email->ruta_archivos, true) ?: [];
                $archivosConUrls = [];

                // Verificar si es formato antiguo (array) o nuevo (objeto con nombres originales)
                if (is_array($archivos) && !empty($archivos) && is_numeric(array_keys($archivos)[0])) {
                    // FORMATO ANTIGUO: Array con rutas numéricas
                    foreach ($archivos as $archivo) {
                        $nombreArchivo = basename($archivo);
                        $url = asset('storage/' . $archivo);
                        $archivosConUrls[] = [
                            'ruta_original' => $archivo,
                            'nombre' => $nombreArchivo,
                            'url' => $url
                        ];
                    }
                } else {
                    // FORMATO NUEVO: Objeto con nombre_original => ruta_unica
                    foreach ($archivos as $nombreOriginal => $rutaUnica) {
                        $url = route('proveedores.descargar_archivo_email', [
                            'emailId' => $email->id_email_proveedor,
                            'nombreArchivo' => $nombreOriginal
                        ]);
                        $archivosConUrls[] = [
                            'ruta_original' => $rutaUnica,
                            'nombre' => $nombreOriginal,
                            'url' => $url
                        ];
                    }
                }

                $email->archivos_procesados = $archivosConUrls;
            } else {
                $email->archivos_procesados = [];
            }
        }

        return response()->json(['data' => $emails]);
    }

    /**
     * Descargar archivo de email de proveedor por nombre original
     */
    public function descargarArchivoEmail($emailId, $nombreArchivo)
    {
        try {
            Log::info("Solicitando descarga de archivo email - Email ID: {$emailId}, Archivo: {$nombreArchivo}");

            $email = EmailProveedor::findOrFail($emailId);
            Log::info("Email encontrado - ID: {$emailId}");

            if (!$email->ruta_archivos) {
                Log::error("Email sin archivos - ID: {$emailId}");
                abort(404, 'Este email no tiene archivos adjuntos');
            }

            $archivos = json_decode($email->ruta_archivos, true);
            if (!isset($archivos[$nombreArchivo])) {
                Log::error("Archivo no encontrado en JSON", [
                    'nombre_solicitado' => $nombreArchivo,
                    'archivos_disponibles' => array_keys($archivos)
                ]);
                abort(404, 'Archivo no encontrado');
            }

            $rutaArchivo = $archivos[$nombreArchivo];
            Log::info("Ruta de archivo obtenida del JSON: {$rutaArchivo}");

            // Construir rutas posibles (normalizar separadores para Windows)
            $posiblesRutas = [
                storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaArchivo)),
                storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $rutaArchivo),
                public_path('storage' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaArchivo)),
                public_path('storage' . DIRECTORY_SEPARATOR . $rutaArchivo)
            ];

            $rutaCompleta = null;
            foreach ($posiblesRutas as $ruta) {
                $ruta = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $ruta);
                if (file_exists($ruta)) {
                    $rutaCompleta = $ruta;
                    Log::info("Archivo encontrado en: {$rutaCompleta}");
                    break;
                }
            }

            if (!$rutaCompleta) {
                Log::error("Archivo no encontrado en ninguna ubicación", [
                    'rutas_intentadas' => $posiblesRutas,
                    'ruta_construida' => $rutaArchivo
                ]);
                abort(404, 'Archivo físico no encontrado en el sistema');
            }

            $extension = strtolower(pathinfo($rutaCompleta, PATHINFO_EXTENSION));
            $tamaño = filesize($rutaCompleta);

            Log::info("Iniciando descarga", [
                'archivo' => $nombreArchivo,
                'tamaño' => $tamaño,
                'extension' => $extension
            ]);

            // Verificar que el archivo no esté corrupto
            if ($tamaño === 0) {
                Log::warning("Archivo está vacío: {$rutaCompleta}");
                abort(422, 'El archivo está vacío o corrupto');
            }

            // Determinar MIME type
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'webp' => 'image/webp',
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'txt' => 'text/plain',
                'zip' => 'application/zip',
                'rar' => 'application/x-rar-compressed'
            ];

            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

            Log::info("Enviando archivo con headers directos");

            // Limpiar cualquier buffer anterior
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Headers HTTP directos para evitar corrupción
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
            header('Content-Length: ' . $tamaño);
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Enviar archivo directamente
            readfile($rutaCompleta);

            Log::info("Archivo email enviado exitosamente con readfile()");
            exit;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Email no encontrado - ID: {$emailId}");
            abort(404, 'Email no encontrado');
        } catch (\Exception $e) {
            Log::error('Error al descargar archivo de email', [
                'email_id' => $emailId,
                'nombre_archivo' => $nombreArchivo,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Error interno al procesar la descarga: ' . $e->getMessage());
        }
    }
}
