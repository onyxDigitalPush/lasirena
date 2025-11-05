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

        // Guardar registro inicial en BD (enviado = 0)
        $email = new EmailProveedor([
            'id_proveedor' => (int) $request->id_proveedor,
            'id_incidencia_proveedor' => $request->id_incidencia_proveedor,
            'id_devolucion_proveedor' => $request->id_devolucion_proveedor,
            'email_remitente' => $request->email_remitente,
            'emails_destinatarios' => $request->emails_destinatarios,
            'emails_bcc' => $request->emails_bcc,
            'asunto' => $request->asunto,
            'mensaje' => $request->mensaje,
            'enviado' => 0
        ]);
        $email->save();

        // Guardar archivos en disco y actualizar ruta_archivos
        $archivos = [];
        foreach (['archivo1', 'archivo2', 'archivo3'] as $input) {
            if ($request->hasFile($input)) {
                $file = $request->file($input);
                $path = $file->store("emails_proveedores/{$email->id_email_proveedor}", 'public');
                $archivos[] = $path;
            }
        }
        $email->ruta_archivos = $archivos ? json_encode($archivos) : null;
        $email->save();

        // Preparar destinatarios / bcc limpiando y quitando entradas vacías
        $to = array_filter(array_map('trim', explode(';', $request->emails_destinatarios)));
        $bccs = $request->emails_bcc ? array_filter(array_map('trim', explode(';', $request->emails_bcc))) : [];

        if (empty($to)) {
            // mantener registro con enviado = 0
            return redirect()->back()->with('error', 'Lista de destinatarios inválida.');
        }

        // Enviar email real (DESPUÉS de guardar)
        try {
            Mail::send([], [], function ($message) use ($request, $archivos, $to, $bccs) {
                $message->from($request->email_remitente)
                    ->to($to)
                    ->subject($request->asunto);

                // Convertir saltos de línea a <br> y escapar HTML para preservar formato y evitar inyección
                $bodyHtml = nl2br(e($request->mensaje));
                $message->setBody($bodyHtml, 'text/html');

                if (!empty($bccs)) {
                    $message->bcc($bccs);
                }

                foreach ($archivos as $archivo) {
                    $filePath = storage_path('app/public/' . $archivo);
                    if (file_exists($filePath)) {
                        $message->attach($filePath);
                    }
                }
            });

            // Si llegó aquí, envío ok -> marcar enviado
            $email->enviado = 1;
            $email->save();

            return redirect()->back()->with('success', 'Correo enviado y guardado en historial.');
        } catch (Exception $e) {
            // Loguear error y devolver mensaje; registro queda con enviado = 0
            Log::error('Error enviando email a proveedor: ' . $e->getMessage(), [
                'email_id' => $email->id_email_proveedor,
                'id_proveedor' => $email->id_proveedor,
            ]);

            return redirect()->back()->with('error', 'No se pudo enviar el correo: ' . $e->getMessage());
        }
    }

    public function historial($id)
    {
        $emails = EmailProveedor::where('id_proveedor', $id)->orderBy('created_at', 'desc')->get();
        
        // Procesar archivos para generar URLs directas
        foreach ($emails as $email) {
            if ($email->ruta_archivos) {
                $archivos = json_decode($email->ruta_archivos, true) ?: [];
                $archivosConUrls = [];
                
                foreach ($archivos as $archivo) {
                    $nombreArchivo = basename($archivo);
                    $url = asset('storage/' . $archivo);
                    $archivosConUrls[] = [
                        'ruta_original' => $archivo,
                        'nombre' => $nombreArchivo,
                        'url' => $url
                    ];
                }
                
                $email->archivos_procesados = $archivosConUrls;
            } else {
                $email->archivos_procesados = [];
            }
        }
        
        return response()->json(['data' => $emails]);
    }
}
