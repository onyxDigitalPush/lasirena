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
      console.error("⚠️ Error al destruir tabla:", e);
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

  // Inicializa el DataTable
  function initHistorialTable() {
    try {
      var $table = $("#historial_emails_table");
      if ($table.length === 0) {
        console.error("❌ Tabla no encontrada en DOM");
        return;
      }

      setTimeout(function () {
        try {
          historialDataTable = $table.DataTable({
            order: [[7, "desc"]],
            pageLength: 10,
            lengthMenu: [
              [10, 25, 50],
              [10, 25, 50],
            ],
            columnDefs: [{ orderable: false, targets: [5, 6] }],
            responsive: true,
            destroy: true,
            searching: true,
            paging: true,
            info: true,
            autoWidth: false,
            language: {
              emptyTable: "No hay registros disponibles",
              zeroRecords: "No se encontraron registros coincidentes",
              info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
              infoEmpty: "Mostrando 0 a 0 de 0 registros",
              infoFiltered: "(filtrado de _MAX_ registros totales)",
              search: "Buscar:",
              paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior",
              },
              lengthMenu: "Mostrar _MENU_ registros",
            },
          });
        } catch (e) {
          console.error("❌ Error al crear DataTable:", e);
        }
      }, 100);
    } catch (e) {
      console.error("❌ Error en initHistorialTable:", e);
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
          $("#userModal").modal("show");
        })
        .fail(function (xhr, status, error) {
          console.error("❌ Error:", error);
          alert("Error al cargar los datos del proveedor");
        })
        .always(function () {
          $btn.removeClass("loading").prop("disabled", false);
        });
    });

  // MODAL HISTORIAL
  $(document)
    .off("click.historyModal")
    .on("click.historyModal", ".open-history", function (e) {
      e.preventDefault();
      var $btn = $(this);
      if ($btn.hasClass("loading") || loadingHistory) return false;
      $btn.addClass("loading").prop("disabled", true);
      loadingHistory = true;

      var id = $btn.data("id");
      var nombre = $btn.data("nombre") || "";

      if (!id) {
        $btn.removeClass("loading").prop("disabled", false);
        loadingHistory = false;
        return alert("ID proveedor no especificado");
      }

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
        '<tr><td colspan="8" class="text-center py-4"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>'
      );

      // 4. Abrir modal
      $("#historialEmailsModal").modal("show");

      // 5. Cargar datos
      $.get("/proveedor/" + id + "/historial")
        .done(function (res) {
          $tbody.empty();
          var emails = res && res.data ? res.data : [];

          if (!emails.length) {
            $tbody.html(
              '<tr><td colspan="8" class="text-center">No hay emails disponibles</td></tr>'
            );
          } else {
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
                  // 🔥 CAMBIO 1: Botones más pequeños con clase nueva
                  archivosHtml += `<a class="btn btn-xs btn-outline-primary btn-archivo-download" href="${
                    archivo.url
                  }" target="_blank" download title="Descargar archivo ${
                    idx + 1
                  }"><i class="fa fa-download"></i> ${idx + 1}</a>`;
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
            });
          }

          // 6. Inicializar DataTable
          initHistorialTable();
        })
        .fail(function (xhr, status, error) {
          console.error("❌ Error cargando historial:", error);
          $tbody.html(
            '<tr><td colspan="8" class="text-center text-danger">Error al cargar el historial</td></tr>'
          );
        })
        .always(function () {
          $btn.removeClass("loading").prop("disabled", false);
          loadingHistory = false;
        });
    });

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

  // Cierre de modal historial
  $("#historialEmailsModal").on("hide.bs.modal", function () {
    $("#hist_proveedor_nombre").text("");
    $("#mensaje_preview").empty();
    $("#mensaje_preview_container").hide();
    loadingHistory = false;
    destroyHistorialTable();
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
})(jQuery);
