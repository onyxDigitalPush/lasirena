(function ($) {
  "use strict";

  if ($(document).data("proveedorListInit")) return;
  $(document).data("proveedorListInit", true);

  // --- Funciones auxiliares ---
  function escapeHtml(s) {
    return $("<div>")
      .text(s || "")
      .html();
  }

  function nl2br(s) {
    return escapeHtml(s || "").replace(/\r\n|\r|\n/g, "<br>");
  }

  function fmtDate(dstr) {
    if (!dstr) return "";
    var d = new Date(dstr);
    if (isNaN(d.getTime())) d = new Date((dstr || "").replace(" ", "T"));
    if (isNaN(d.getTime())) return dstr;
    var p = (n) => (n < 10 ? "0" + n : n);
    return `${p(d.getDate())}-${p(d.getMonth() + 1)}-${d.getFullYear()} ${p(
      d.getHours()
    )}:${p(d.getMinutes())}:${p(d.getSeconds())}`;
  }

  var proveedoresTable = null;
  var loadingHistory = false;
  var historialDataTable = null;

  // Destruye completamente tabla y DataTable
  function destroyHistorialTable() {
    try {
      if (historialDataTable) {
        try {
          historialDataTable.destroy(true);
        } catch (e) {}
        historialDataTable = null;
      }

      if (
        $.fn.DataTable &&
        $.fn.DataTable.isDataTable("#historial_emails_table")
      ) {
        try {
          $("#historial_emails_table").DataTable().destroy(true);
        } catch (e) {}
      }

      // ELIMINAR tabla del DOM
      $("#historial_emails_table_wrapper").remove();
      $("#historial_emails_table").remove();
    } catch (e) {
      // Error silencioso en producción
    }
  }

  // Crear tabla HTML desde cero
  function createHistorialTable() {
    const html = `
    <table id="historial_emails_table" class="table table-sm table-striped table-bordered">
      <thead class="thead-dark">
        <tr>
          <th class="text-center">Tipo</th>
          <th class="text-center">Remitente</th>
          <th class="text-center">Destinatarios</th>
          <th class="text-center">BCC</th>
          <th class="text-center">Asunto</th>
          <th class="text-center">Mensaje</th>
          <th class="text-center">Archivos</th>
          <th class="text-center">Fecha envío</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>`;

    // SOLO USAR EL CONTENEDOR FIJO
    $("#tabla_historial_container").html(html);
  }

  // Inicializa el DataTable - VERSIÓN MEJORADA
  function initHistorialTable() {
    try {
      var $table = $("#historial_emails_table");
      if ($table.length === 0) {
        return;
      }

      // Verificar si DataTable está disponible
      if (typeof $.fn.DataTable === 'undefined') {
        return;
      }

      setTimeout(function () {
        try {
          // Verificar si ya hay filas de datos
          var $tbody = $table.find('tbody');
          var rowCount = $tbody.find('tr').length;

          // Si sólo hay una fila que contiene un td con colspan (mensaje "No hay emails"),
          // evitar inicializar DataTable para prevenir errores de DataTables en producción
          if (rowCount === 1) {
            var $firstTd = $tbody.find('tr').first().find('td');
            if ($firstTd.length === 1 && $firstTd.attr('colspan')) {
              return;
            }
          }
          
          historialDataTable = $table.DataTable({
            order: [[7, "desc"]], // Ordenar por fecha descendente
            pageLength: 10,
            lengthMenu: [[10, 25, 50], [10, 25, 50]],
            columnDefs: [
              { orderable: false, targets: [5, 6] }, // Botones no ordenables
              { className: "text-center", targets: [0, 5, 6, 7] } // Centrar ciertas columnas
            ],
            responsive: true,
            destroy: true,
            searching: true,
            paging: true,
            info: true,
            autoWidth: false,
            processing: false,
            language: {
              emptyTable: "No hay emails registrados para este proveedor",
              zeroRecords: "No se encontraron emails coincidentes con la búsqueda",
              info: "Mostrando _START_ a _END_ de _TOTAL_ emails",
              infoEmpty: "Mostrando 0 a 0 de 0 emails",
              infoFiltered: "(filtrado de _MAX_ emails totales)",
              search: "Buscar emails:",
              paginate: {
                first: "Primero",
                last: "Último", 
                next: "Siguiente",
                previous: "Anterior",
              },
              lengthMenu: "Mostrar _MENU_ emails por página",
            },
          });
          
        } catch (e) {
          // Error silencioso en producción
        }
      }, 150); // Aumentar el delay ligeramente
    } catch (e) {
      // Error silencioso en producción
    }
  }

  // --- Tabla principal de proveedores ---
  $(function () {
    proveedoresTable = $("#table_proveedores").DataTable({
      orderCellsTop: true,
      fixedHeader: true,
      pageLength: 25,
      lengthMenu: [10, 25, 50, 100],
      responsive: true,
    });

    $("#table_proveedores thead tr:eq(1) th").each(function (i) {
      $("input", this).on("keyup change", function () {
        if (i === 2 && this.value.trim().toLowerCase() === "sin email") {
          proveedoresTable.column(i).search("^\\s*$", true, false).draw();
        } else {
          proveedoresTable.column(i).search(this.value).draw();
        }
      });
      
      // Filtro desplegable para la columna de Familia (columna 3)
      $("select", this).on("change", function () {
        proveedoresTable.column(i).search(this.value).draw();
      });
    });

  });

  // MODAL EDITAR PROVEEDOR
  $(document)
    .off("click.editModal")
    .on("click.editModal", ".open-modal", function (e) {
      e.preventDefault();
      var $btn = $(this);
      if ($btn.hasClass("loading")) return false;
      $btn.addClass("loading").prop("disabled", true);

      var url = $btn.data("url");

      $.get(url)
        .done(function (proveedor) {
          $("#codigo_proveedor_old").val(proveedor.id_proveedor);
          $("#codigo_proveedor_edit").val(proveedor.id_proveedor);
          $("#nombre_proveedor_edit").val(proveedor.nombre_proveedor);
          $("#email_proveedor_edit").val(proveedor.email_proveedor);
          $("#familia_edit").val(proveedor.familia || "");
          $("#subfamilia_edit").val(proveedor.subfamilia || "");
          $("#userModal").modal("show");
        })
        .fail(function (xhr, status, error) {
          alert("Error al cargar los datos del proveedor");
        })
        .always(function () {
          $btn.removeClass("loading").prop("disabled", false);
        });
    });

  // MODAL HISTORIAL - VERSIÓN MEJORADA PARA PRODUCCIÓN
  // Usar múltiples métodos de vinculación para mayor compatibilidad
  $(document)
    .off("click.historyModal")
    .on("click.historyModal", ".open-history", manejarClickHistorial);
    
  // También vincular directamente si los elementos ya existen
  $(document).ready(function() {
    $(".open-history").off("click.historyModalDirect").on("click.historyModalDirect", manejarClickHistorial);
  });
  
  function manejarClickHistorial(e) {
      e.preventDefault();
      var $btn = $(this);
      
      // Prevenir múltiples clicks
      if ($btn.hasClass("loading") || loadingHistory) {
        return false;
      }
      
      $btn.addClass("loading").prop("disabled", true);
      loadingHistory = true;

      var id = $btn.data("id");
      var nombre = $btn.data("nombre") || "Sin nombre";

      // Validar ID más robustamente
      if (!id || id === "" || isNaN(id)) {
        $btn.removeClass("loading").prop("disabled", false);
        loadingHistory = false;
        return alert("Error: ID de proveedor no válido");
      }

      // Verificar que jQuery está disponible
      if (typeof $ === 'undefined' || typeof jQuery === 'undefined') {
        $btn.removeClass("loading").prop("disabled", false);
        loadingHistory = false;
        return alert("Error: jQuery no disponible");
      }

      // Verificar que Bootstrap está disponible
      if (typeof $.fn.modal === 'undefined') {
        $btn.removeClass("loading").prop("disabled", false);
        loadingHistory = false;
        return alert("Error: Bootstrap no está cargado correctamente");
      }

      // Verificar que el modal existe
      var $modal = $("#historialEmailsModal");
      if ($modal.length === 0) {
        $btn.removeClass("loading").prop("disabled", false);
        loadingHistory = false;
        return alert("Error: Modal de historial no disponible");
      }

      // Configurar información del proveedor
      $("#hist_proveedor_nombre").text(nombre);
      $("#mensaje_preview").empty();
      $("#mensaje_preview_container").hide();

      // 1. Destruir tabla anterior
      destroyHistorialTable();

      // 2. Crear tabla nueva
      createHistorialTable();

      // 3. Mostrar loading en tbody
      var $tbody = $("#historial_emails_table tbody");
      $tbody.html(
        '<tr><td colspan="8" class="text-center py-4"><i class="fa fa-spinner fa-spin"></i> Cargando historial de emails...</td></tr>'
      );

      // 4. Abrir modal
      try {
        // Método 1: Bootstrap modal
        $modal.modal("show");
      } catch (error) {
        try {
          // Método alternativo: manipular clases directamente
          $modal.addClass("show").css("display", "block");
          $("body").addClass("modal-open");
          if ($(".modal-backdrop").length === 0) {
            $("body").append('<div class="modal-backdrop fade show"></div>');
          }
        } catch (alternativeError) {
          $btn.removeClass("loading").prop("disabled", false);
          loadingHistory = false;
          return alert("Error al abrir el modal del historial: " + error.message);
        }
      }

      // 5. Cargar datos con manejo robusto de errores
      var ajaxUrl = "/proveedor/" + id + "/historial";
      
      $.ajax({
        url: ajaxUrl,
        method: "GET",
        timeout: 15000, // 15 segundos timeout
        dataType: "json"
      })
        .done(function (res) {
          $tbody.empty();
          var emails = res && res.data ? res.data : [];

          if (!emails.length) {
            $tbody.html(
              '<tr><td colspan="8" class="text-center text-muted">' +
              '<i class="fa fa-inbox"></i><br>' +
              'No hay emails registrados para este proveedor</td></tr>'
            );
          } else {
            var emailsProcessed = 0;
            emails.forEach(function (email) {
              var tipo = email.id_incidencia_proveedor
                ? "Incidencia"
                : email.id_devolucion_proveedor
                ? "Reclamación"
                : "General";

              var tipoBadge =
                tipo === "Incidencia"
                  ? '<span class="badge badge-warning">Incidencia</span>'
                  : tipo === "Reclamación"
                  ? '<span class="badge badge-info">Reclamación</span>'
                  : '<span class="badge badge-secondary">General</span>';

              var remitente = escapeHtml(email.email_remitente || "");
              var destinatarios = escapeHtml(email.emails_destinatarios || "");
              var bcc = escapeHtml(email.emails_bcc || "");
              var asunto = escapeHtml(email.asunto || "");
              var mensajeEnc = encodeURIComponent(email.mensaje || "");
              var archivosHtml = "";
              var archivos = email.archivos_procesados || [];

              if (archivos && archivos.length) {
                archivos.forEach(function (archivo, idx) {
                  // CAMBIO 1: Botones más pequeños con clase nueva
                  archivosHtml += `<button type="button" class="btn btn-xs btn-outline-primary btn-archivo-download" 
                    data-url="${archivo.url}" data-nombre="${archivo.nombre}" title="Descargar archivo ${
                    idx + 1
                  }"><i class="fa fa-download"></i> ${idx + 1}</button>`;
                });
              } else {
                archivosHtml =
                  '<span class="text-muted small">Sin archivos</span>';
              }

              var fecha = fmtDate(
                email.created_at || email.fecha_envio_proveedor || ""
              );

              var row = `
          <tr>
            <td class="text-center">${tipoBadge}</td>
            <td>${remitente}</td>
            <td>${destinatarios}</td>
            <td>${bcc}</td>
            <td>${asunto}</td>
            <td class="text-center">
              <button class="btn btn-sm btn-outline-secondary btn-ver-mensaje" data-mensaje="${mensajeEnc}">Ver</button>
            </td>
            <td class="archivos-cell text-center">${archivosHtml}</td>
            <td class="text-center">${fecha}</td>
          </tr>`;

              $tbody.append(row);
              emailsProcessed++;
            });
          }

          // 6. Inicializar DataTable (sólo si hay emails reales)
          if (emails.length > 0) {
            setTimeout(function() {
              initHistorialTable();
            }, 100);
          }
        })
        .fail(function (xhr, status, error) {
          
          var errorMessage = "Error desconocido";
          var errorDetails = "";
          
          if (xhr.status === 404) {
            errorMessage = "Proveedor no encontrado";
            errorDetails = "El proveedor con ID " + id + " no existe";
          } else if (xhr.status === 500) {
            errorMessage = "Error interno del servidor";
            errorDetails = "Por favor, contacte al administrador";
          } else if (xhr.status === 0) {
            errorMessage = "Error de conexión";
            errorDetails = "Verifique su conexión a internet";
          } else if (status === "timeout") {
            errorMessage = "Tiempo de espera agotado";
            errorDetails = "La consulta tardó demasiado tiempo";
          } else {
            errorMessage = "Error al cargar historial";
            errorDetails = "Código: " + xhr.status + " | " + error;
          }
          
          $tbody.html(
            '<tr><td colspan="8" class="text-center text-danger p-4">' +
            '<i class="fa fa-exclamation-triangle mb-2" style="font-size: 24px;"></i><br>' +
            '<strong>' + errorMessage + '</strong><br>' +
            '<small>' + errorDetails + '</small><br><br>' +
            '<button class="btn btn-sm btn-outline-secondary" onclick="$(this).closest(\'.modal\').modal(\'hide\')">Cerrar</button>' +
            '</td></tr>'
          );
        })
        .always(function () {
          $btn.removeClass("loading").prop("disabled", false);
          loadingHistory = false;
        });
  }

  // BOTÓN VER MENSAJE (delegado)
  $(document)
    .off("click.messagePreview")
    .on("click.messagePreview", ".btn-ver-mensaje", function (e) {
      e.preventDefault();
      var enc = $(this).data("mensaje") || "";
      var msg = "";
      try {
        msg = decodeURIComponent(enc);
      } catch (ex) {
        msg = enc || "";
      }
      $("#mensaje_preview").html(nl2br(msg));
      $("#mensaje_preview_container").show();
    });

  // BOTÓN CERRAR VISTA (delegado)
  $(document)
    .off("click.closePreview")
    .on("click.closePreview", "#cerrar_preview_mensaje", function (e) {
      e.preventDefault();
      $("#mensaje_preview").empty();
      $("#mensaje_preview_container").hide();
    });

  // Función para cerrar el modal historial
  function cerrarModalHistorial() {
    $("#hist_proveedor_nombre").text("");
    $("#mensaje_preview").empty();
    $("#mensaje_preview_container").hide();
    loadingHistory = false;
    destroyHistorialTable();
    
    // Limpiar backdrop si existe
    $(".modal-backdrop").remove();
    $("body").removeClass("modal-open");
  }

  // Cierre de modal historial (Bootstrap)
  $("#historialEmailsModal").on("hide.bs.modal", cerrarModalHistorial);
  
  // Cierre manual del modal (botón X y backdrop)
  $(document).on("click", "#historialEmailsModal .close, .modal-backdrop", function() {
    var $modal = $("#historialEmailsModal");
    try {
      $modal.modal("hide");
    } catch (e) {
      // Si Bootstrap falla, cerrar manualmente
      $modal.removeClass("show").css("display", "none");
      cerrarModalHistorial();
    }
  });

  // Cierre de modal editar
  $("#userModal").on("hide.bs.modal", function () {
    try {
      $("#editUserForm")[0].reset();
    } catch (e) {}
  });

  // Limpiar loading de todos los botones
  $(".modal").on("hidden.bs.modal", function () {
    $(".loading").removeClass("loading").prop("disabled", false);
  });

  // Forzar descarga directa de archivos (sin abrir nueva pestaña)
  $(document).off("click.descargaArchivo").on("click.descargaArchivo", ".btn-archivo-download", function(e) {
    e.preventDefault();
    var url = $(this).data("url");
    var nombre = $(this).data("nombre") || "archivo";
    // Crear enlace temporal y forzar descarga
    var a = document.createElement('a');
    a.href = url;
    a.download = nombre;
    a.style.display = 'none';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  });
})(jQuery);
