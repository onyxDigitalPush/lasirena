//fecha en los inputs cuando se digita la fecha incidencia
document
  .getElementById("fecha_incidencia")
  .addEventListener("change", function () {
    const fecha = this.value.split("-"); // "2025-01-01" → ["2025", "01", "01"]

    const año = parseInt(fecha[0]);
    const mes = parseInt(fecha[1]); // no hay que sumar ni restar meses

    // Asignamos los valores a los selects
    document.getElementById("año_incidencia").value = año;
    document.getElementById("mes_incidencia").value = mes;
  });

//fecha devolucion reclamaciones clientes
document
  .getElementById("fecha_reclamacion")
  .addEventListener("change", function () {
    var fecha_dev = this.value.split("-"); // "2025-01-01" → ["2025", "01", "01"]

    var año_dev = parseInt(fecha_dev[0]);
    var mes_dev = parseInt(fecha_dev[1]); // no hay que sumar ni restar meses

    // Asignamos los valores a los selects
    document.getElementById("año_devolucion").value = año_dev;
    document.getElementById("mes_devolucion").value = mes_dev;
  });

//codigo de proveedor
document
  .getElementById("codigo_proveedor_incidencia")
  .addEventListener("blur", function () {
    const codigoProveedor = this.value;

    if (codigoProveedor.trim() === "") return;
    const baseUrl = window.appBaseUrl;
    fetch(`${baseUrl}/material_kilo/buscar-proveedor/${codigoProveedor}`)
      .then((response) => {
        if (!response.ok) throw new Error("Proveedor no encontrado");
        return response.json();
      })
      .then((data) => {
        const select = document.getElementById("proveedor_incidencia");
        // Buscar opción que coincida con el ID recibido
        const optionToSelect = Array.from(select.options).find(
          (opt) => opt.value == data.id_proveedor
        );

        if (optionToSelect) {
          select.value = optionToSelect.value;
        } else {
          alert("Proveedor no encontrado en el listado.");
        }
      })
      .catch((error) => {
        console.error(error);
        alert("Error buscando proveedor.");
      });
  });

//codigo de proveedor devoluciones reclamaciones cliente
document
  .getElementById("codigo_proveedor_devolucion")
  .addEventListener("blur", function () {
    const codigoProveedor = this.value;

    if (codigoProveedor.trim() === "") return;
    const baseUrl = window.appBaseUrl;
    fetch(`${baseUrl}/material_kilo/buscar-proveedor/${codigoProveedor}`)
      .then((response) => {
        if (!response.ok) throw new Error("Proveedor no encontrado");
        return response.json();
      })
      .then((data) => {
        const select = document.getElementById("proveedor_devolucion");
        // Buscar opción que coincida con el ID recibido
        const optionToSelect = Array.from(select.options).find(
          (opt) => opt.value == data.id_proveedor
        );

        if (optionToSelect) {
          select.value = optionToSelect.value;
        } else {
          alert("Proveedor no encontrado en el listado.");
        }
      })
      .catch((error) => {
        console.error(error);
        alert("Error buscando proveedor.");
      });
  });

$(document).ready(function () {
  console.log("jQuery y DataTables cargados correctamente");

  // Abrir modal de subir Excel de reclamación de cliente igual que los otros modales
  $(document).on("click", "#abrirModalSubirExcel", function (e) {
    e.preventDefault();
    e.stopPropagation();

    console.log("Click en botón subir Excel detectado");

    // Limpiar cualquier modal backdrop existente
    $(".modal-backdrop").remove();
    $("body").removeClass("modal-open");

    try {
      // Usar Bootstrap 4 modal
      $("#modalSubirExcel").modal({
        backdrop: true,
        keyboard: true,
        focus: true,
        show: true,
      });
      console.log("Modal Bootstrap 4 ejecutado");
    } catch (error) {
      console.error("Error con Bootstrap modal, usando fallback:", error);

      // Fallback manual
      $("#modalSubirExcel").show().addClass("show").css({
        display: "block",
        "padding-right": "15px",
      });

      $("body").addClass("modal-open").css("padding-right", "15px");

      // Crear backdrop manualmente
      if ($(".modal-backdrop").length === 0) {
        $('<div class="modal-backdrop fade show"></div>').appendTo("body");
      }

      console.log("Fallback manual ejecutado");
    }
  });

  var table = $("#table_total_kg_proveedor").DataTable({
    columnDefs: [
      {
        targets: [3], //columna de registros
        type: "num",
        render: function (data, type, row) {
          if (typeof data === "string") {
            data = data.replace(/,/g, ""); // Elimina comas
          }
          return type === "sort" || type === "type" ? parseFloat(data) : data;
        },
      },
    ],
    paging: true,
    pageLength: 25,
    info: true,
    ordering: true,
    searching: true,
    orderCellsTop: false, // Cambiado para headers complejos

    // ...existing code...ra headers complejos
    fixedHeader: false, // Deshabilitado temporalmente
    order: [[2, "desc"]], // Ordenar por Total KG descendente por defecto
    columnDefs: [
      {
        targets: [0, 1], // ID Proveedor y Nombre Proveedor - searchable
        orderable: true,
        searchable: true,
      },
      {
        targets: 2, // Total KG
        orderable: true,
        searchable: false,
        render: function (data, type, row) {
          // Para ordenar y buscar, extraer el número del HTML
          if (type === "sort" || type === "type" || type === "filter") {
            var html = $("<div>").html(data);
            var dataTotal = html.find('[data-total]').attr('data-total');
            if (dataTotal !== undefined && dataTotal !== null) {
              var n = parseFloat(String(dataTotal));
              return isNaN(n) ? 0 : n;
            }
            var text = html.text();
            var normalized = text.replace(/\./g, "").replace(/,/g, ".");
            var num = parseFloat(normalized.replace(/[^\d.-]/g, ""));
            return isNaN(num) ? 0 : num;
          }
          return data;
        },
      },
      {
        targets: 3, // Total KG
        orderable: true,
        searchable: false,
        render: function (data, type, row) {
          if (type === "sort" || type === "type" || type === "filter") {
            var text = $("<div>").html(data).text(); // elimina HTML si lo hay
            var num = parseFloat(text.replace(/[^\d.-]/g, "")); // elimina comas y otros caracteres
            return isNaN(num) ? 0 : num;
          }
          return data;
        },
      },
      {
        targets: 4,
        orderable: true,
        searchable: false,
        render: function (data, type, row) {
          if (type === "sort" || type === "type" || type === "filter") {
            var div = document.createElement("div");
            div.innerHTML = data;
            var percentText = div.textContent || div.innerText || "";
            var match = percentText.match(/(\d+[\.,]?\d*)/);
            var num = match ? parseFloat(match[1].replace(",", ".")) : 0;
            return num;
          }
          return data;
        },
      },
      {
        targets: [5, 6, 7, 8, 9], // Columnas de métricas (RG1, RL1, DEV1, ROK1, RET1)
        orderable: false,
        searchable: false,
      },
    ],
    language: {
      decimal: "",
      emptyTable: "No hay datos disponibles en la tabla",
      info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
      infoEmpty: "Mostrando 0 a 0 de 0 entradas",
      infoFiltered: "(filtrado de _MAX_ entradas totales)",
      infoPostFix: "",
      thousands: ",",
      lengthMenu: "Mostrar _MENU_ entradas",
      loadingRecords: "Cargando...",
      processing: "Procesando...",
      search: "Buscar:",
      zeroRecords: "No se encontraron registros coincidentes",
      paginate: {
        first: "Primero",
        last: "Último",
        next: "Siguiente",
        previous: "Anterior",
      },
      aria: {
        sortAscending: ": activar para ordenar la columna ascendente",
        sortDescending: ": activar para ordenar la columna descendente",
      },
    },
  }); // Función para actualizar totales basado en filas filtradas
  function actualizarTotales() {
    var filasVisibles = table.rows({ filter: "applied" }).data();
    var totalProveedores = filasVisibles.length;
    var totalKg = 0;

    // Calcular suma de KG de las filas visibles
    filasVisibles.each(function (data, index) {
        // La columna 2 contiene el total KG (necesitamos extraer el número del badge)
        var cellHtml = $(data[2]);
        var dataTotal = cellHtml.find('[data-total]').attr('data-total');
        var kgValue = 0;
        if (dataTotal !== undefined && dataTotal !== null) {
          kgValue = parseFloat(String(dataTotal)) || 0;
        } else {
          var kgText = cellHtml.text() || data[2];
          var normalized = String(kgText).replace(/\./g, "").replace(/,/g, ".");
          kgValue = parseFloat(normalized.replace(/[^\d.-]/g, "")) || 0;
        }
        totalKg += kgValue;
    });

    // Actualizar los elementos en la interfaz usando los IDs específicos
    $("#total-proveedores").text(totalProveedores);
    $("#total-kg-general").text(
      new Intl.NumberFormat("es-ES", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(totalKg) + " kg"
    );

    // Actualizar porcentajes de las filas visibles
    actualizarPorcentajes(totalKg);
  }

  // Función para actualizar porcentajes basado en el nuevo total
  function actualizarPorcentajes(totalKgFiltrado) {
    if (totalKgFiltrado <= 0) return;

    table.rows({ filter: "applied" }).every(function () {
      var data = this.data();
      var node = this.node();

      // Extraer el valor de KG de la fila
      var kgText = $(data[2]).text() || data[2];
      var cellHtml = $(data[2]);
      var dataTotal = cellHtml.find('[data-total]').attr('data-total');
      var kgValue = 0;
      if (dataTotal !== undefined && dataTotal !== null) {
        kgValue = parseFloat(String(dataTotal)) || 0;
      } else {
        var kgText = cellHtml.text() || data[2];
        var normalized = String(kgText).replace(/\./g, "").replace(/,/g, ".");
        kgValue = parseFloat(normalized.replace(/[^\d.-]/g, "")) || 0;
      }

      // Calcular nuevo porcentaje
      var nuevoPorcentaje = (kgValue / totalKgFiltrado) * 100;

      // Actualizar la celda de porcentaje (columna 4)
      var $progressContainer = $(node).find("td:eq(4) .progress");
      var $progressBar = $progressContainer.find(".progress-bar");
      var $progressText = $progressContainer.find(".position-absolute");

      // Actualizar la barra de progreso
      var anchoMinimo = Math.max(nuevoPorcentaje, 1);
      $progressBar.css("width", anchoMinimo + "%");
      $progressBar.attr("aria-valuenow", nuevoPorcentaje);

      // Actualizar el color de la barra según el porcentaje
      $progressBar.removeClass("bg-success bg-warning bg-info");
      if (nuevoPorcentaje >= 50) {
        $progressBar.addClass("bg-success");
        $progressText.css("color", "white");
      } else if (nuevoPorcentaje >= 25) {
        $progressBar.addClass("bg-warning");
        $progressText.css("color", "#333");
      } else {
        $progressBar.addClass("bg-info");
        $progressText.css("color", "#333");
      }

      // Actualizar el texto del porcentaje
      $progressText.text(nuevoPorcentaje.toFixed(1) + "%");
    });
  } // Aplica los filtros de las celdas con inputs (búsqueda directa)
  $("#table_total_kg_proveedor thead input").each(function () {
    var $input = $(this);
    var columnIndex = $input.closest("th").index();

    console.log(
      "Configurando filtro para columna:",
      columnIndex,
      "Placeholder:",
      $input.attr("placeholder")
    );

    $input.on("keyup change", function () {
      var searchValue = this.value.trim();
      console.log("Filtrando columna", columnIndex, "con valor:", searchValue);

      if (table.column(columnIndex).search() !== searchValue) {
        table.column(columnIndex).search(searchValue).draw();

        // Actualizar totales después de filtrar
        setTimeout(function () {
          actualizarTotales();
        }, 100);
      }
    });
  });
  // También actualizar totales cuando se use el buscador general
  table.on("search.dt", function () {
    actualizarTotales();
  });

  // Actualizar totales cuando se redibuje la tabla
  table.on("draw.dt", function () {
    actualizarTotales();
  });

  // Actualizar totales al cargar la página inicialmente
  actualizarTotales();
  // Funcionalidad para filtros
  $("#aplicarFiltros").on("click", function () {
    var mes = $("#filtro_mes").val();
    var año = $("#filtro_año").val();

    // Construir URL con parámetros
    var url = new URL(window.location.href);

    if (mes) {
      url.searchParams.set("mes", mes);
    } else {
      url.searchParams.delete("mes");
    }

    if (año) {
      url.searchParams.set("año", año);
    } else {
      url.searchParams.delete("año");
    }

    // Recargar página con nuevos filtros
    window.location.href = url.toString();
  });

  // Función para controlar la visibilidad del botón Guardar Métricas
  function controlarBotonGuardarMetricas() {
    var mes = $("#filtro_mes").val();
    var $botonGuardar = $("#guardarMetricas");

    if (!mes) {
      $botonGuardar.prop("disabled", true);
      $botonGuardar.attr(
        "title",
        "Debe seleccionar un mes específico para guardar métricas"
      );
      $botonGuardar.removeClass("btn-success").addClass("btn-secondary");
    } else {
      $botonGuardar.prop("disabled", false);
      $botonGuardar.attr("title", "Guardar Métricas");
      $botonGuardar.removeClass("btn-secondary").addClass("btn-success");
    }
  }

  // Controlar el botón al cambiar el mes
  $("#filtro_mes").on("change", controlarBotonGuardarMetricas);

  // Controlar el botón al cargar la página
  controlarBotonGuardarMetricas();

  $("#limpiarFiltros").on("click", function () {
    $("#filtro_mes").val("");
    $("#filtro_año").val("");

    // Remover parámetros de URL y recargar
    var url = new URL(window.location.href);
    url.searchParams.delete("mes");
    url.searchParams.delete("año");
    window.location.href = url.toString();
  });
  // Funcionalidad para guardar métricas
  $("#guardarMetricas").on("click", function () {
    var mes = $("#filtro_mes").val();
    var año = $("#filtro_año").val();

    if (!año) {
      Swal.fire({
        icon: "warning",
        title: "Año requerido",
        text: "Debe seleccionar el año.",
        confirmButtonText: "Entendido",
      });
      return;
    }

    if (!mes) {
      Swal.fire({
        icon: "warning",
        title: "Mes requerido para guardar métricas",
        text: 'Debe seleccionar un mes específico para guardar las métricas. No es posible guardar métricas para "Todos los meses".',
        confirmButtonText: "Entendido",
      });
      return;
    }

    // Recopilar datos de métricas
    var metricas = {};
    var hasData = false;

    $(".metrica-input").each(function () {
      var $input = $(this);
      var proveedorId = $input.data("proveedor");
      var metrica = $input.data("metrica");
      var valor = $input.val();

      if (!metricas[proveedorId]) {
        metricas[proveedorId] = {};
      }

      if (valor && valor.trim() !== "") {
        metricas[proveedorId][metrica] = parseFloat(valor);
        hasData = true;
      } else {
        metricas[proveedorId][metrica] = null;
      }
    });

    if (!hasData) {
      $.confirm({
        title: "Sin datos",
        content: "No hay métricas para guardar.",
        type: "blue",
        buttons: {
          entendido: {
            text: "Entendido",
            btnClass: "btn-blue",
          },
        },
      });
      return;
    }

    // Mostrar loading
    var loadingDialog = $.confirm({
      title: "Guardando métricas...",
      content: "Por favor espere",
      type: "blue",
      buttons: false,
      closeIcon: false,
    }); // Enviar datos al servidor
    $.ajax({
      url: window.guardarMetricasUrl,
      method: "POST",
      data: {
        _token: $('meta[name="csrf-token"]').attr("content"),
        metricas: metricas,
        mes: mes,
        año: año,
      },
      success: function (response) {
        loadingDialog.close();
        $.confirm({
          title: "Éxito",
          content: response.message || "Métricas guardadas correctamente",
          type: "green",
          buttons: {
            entendido: {
              text: "Entendido",
              btnClass: "btn-green",
            },
          },
        });
      },
      error: function (xhr) {
        loadingDialog.close();
        var errorMessage = "Error al guardar las métricas";

        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        }

        $.confirm({
          title: "Error",
          content: errorMessage,
          type: "red",
          buttons: {
            entendido: {
              text: "Entendido",
              btnClass: "btn-red",
            },
          },
        });
      },
    });
  });

  // Inicializar filtros desde URL
  var urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has("mes")) {
    $("#filtro_mes").val(urlParams.get("mes"));
  }
  if (urlParams.has("año")) {
    $("#filtro_año").val(urlParams.get("año"));
  }

  // Validación de inputs numéricos
  $(".metrica-input").on("input", function () {
    var value = $(this).val();
    if (value && !/^\d*\.?\d*$/.test(value)) {
      $(this).val(value.slice(0, -1));
    }
  });

  // Manejar apertura del modal de incidencias
  $("#gestionarIncidencias").on("click", function (e) {
    e.preventDefault();
    e.stopPropagation();

    console.log("Click en botón incidencias detectado");

    // Limpiar cualquier modal backdrop existente
    $(".modal-backdrop").remove();
    $("body").removeClass("modal-open");

    try {
      // Intentar usar Bootstrap modal
      $("#modalIncidencias").modal({
        backdrop: true,
        keyboard: true,
        focus: true,
        show: true,
      });
      console.log("Modal Bootstrap ejecutado");
    } catch (error) {
      console.error("Error con Bootstrap modal, usando fallback:", error);

      // Fallback manual
      $("#modalIncidencias").show().addClass("show").css({
        display: "block",
        "padding-right": "15px",
      });

      $("body").addClass("modal-open").css("padding-right", "15px");

      // Crear backdrop manualmente
      if ($(".modal-backdrop").length === 0) {
        $('<div class="modal-backdrop fade show"></div>').appendTo("body");
      }

      console.log("Fallback manual ejecutado");
    }
  });

  // Función para cerrar modal manualmente
  function cerrarModal() {
    $("#modalIncidencias").hide().removeClass("show").css({
      display: "none",
      "padding-right": "",
    });
    $(".modal-backdrop").remove();
    $("body").removeClass("modal-open").css("padding-right", "");
  }

  // Event listeners para cerrar el modal
  $(document).on(
    "click",
    '#modalIncidencias .close, #modalIncidencias [data-dismiss="modal"]',
    function () {
      console.log("Cerrando modal...");
      try {
        $("#modalIncidencias").modal("hide");
      } catch (error) {
        cerrarModal();
      }
    }
  );

  // Cerrar con Escape
  $(document).on("keyup", function (e) {
    if (e.key === "Escape" && $("#modalIncidencias").is(":visible")) {
      try {
        $("#modalIncidencias").modal("hide");
      } catch (error) {
        cerrarModal();
      }
    }
  });

  // Cerrar haciendo clic en el backdrop
  $(document).on("click", ".modal-backdrop, #modalIncidencias", function (e) {
    if (e.target === this) {
      try {
        $("#modalIncidencias").modal("hide");
      } catch (error) {
        cerrarModal();
      }
    }
  });

  // Asegurar que el modal se puede cerrar
  $("#modalIncidencias").on("shown.bs.modal", function () {
    console.log("Modal mostrado correctamente");
  });

  $("#modalIncidencias").on("hidden.bs.modal", function () {
    console.log("Modal cerrado correctamente");
    $(".modal-backdrop").remove();
    $("body").removeClass("modal-open").css("padding-right", "");
  });

  // Verificar que el modal existe
  if ($("#modalIncidencias").length) {
    console.log("Modal de incidencias encontrado en el DOM");
  } else {
    console.error("Modal de incidencias NO encontrado en el DOM");
  }

  // Manejar envío del formulario de incidencias
  $("#guardarIncidencia").on("click", function () {
    var formData = new FormData(document.getElementById("formIncidencia"));

    $.ajax({
      url: window.appBaseUrl + "/material_kilo/guardar-incidencia",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
      },
      beforeSend: function () {
        $("#guardarIncidencia")
          .prop("disabled", true)
          .html('<i class="fa fa-spinner fa-spin mr-1"></i>Guardando...');
      },
      success: function (response) {
        if (response.success) {
          $.confirm({
            title: "Éxito",
            content:
              "Incidencia guardada correctamente. Las métricas se han actualizado automáticamente.",
            type: "green",
            buttons: {
              aceptar: {
                text: "Aceptar",
                btnClass: "btn-green",
                action: function () {
                  // Cerrar modal y recargar página para actualizar métricas
                  $("#modalIncidencias").modal("hide");
                  location.reload();
                },
              },
            },
          });

          // Limpiar formulario
          document.getElementById("formIncidencia").reset();
        }
      },
      error: function (xhr) {
        var errorMessage = "Error al guardar la incidencia";

        if (xhr.responseJSON && xhr.responseJSON.error) {
          errorMessage = xhr.responseJSON.error;
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        }

        $.confirm({
          title: "Error",
          content: errorMessage,
          type: "red",
          buttons: {
            entendido: {
              text: "Entendido",
              btnClass: "btn-red",
            },
          },
        });
      },
      complete: function () {
        $("#guardarIncidencia")
          .prop("disabled", false)
          .html('<i class="fa fa-save mr-1"></i>Guardar Incidencia');
      },
    });
  });

  // Auto-calcular días de respuesta cuando se cambian las fechas
  $("#fecha_envio_proveedor, #fecha_respuesta_proveedor").on(
    "change",
    function () {
      var fechaEnvio = $("#fecha_envio_proveedor").val();
      var fechaRespuesta = $("#fecha_respuesta_proveedor").val();

      if (fechaEnvio && fechaRespuesta) {
        var envio = new Date(fechaEnvio);
        var respuesta = new Date(fechaRespuesta);
        var diferencia = Math.ceil((respuesta - envio) / (1000 * 60 * 60 * 24));

        // Mostrar información calculada (opcional)
        console.log("Días de respuesta calculados:", diferencia);
      }
    }
  );

  // Auto-calcular días de respuesta cuando se cambian las fechas en devoluciones
  $("#fecha_envio_proveedor_dev, #fecha_respuesta_proveedor_dev").on(
    "change",
    function () {
      var fechaEnvio = $("#fecha_envio_proveedor_dev").val();
      var fechaRespuesta = $("#fecha_respuesta_proveedor_dev").val();

      if (fechaEnvio && fechaRespuesta) {
        var envio = new Date(fechaEnvio);
        var respuesta = new Date(fechaRespuesta);
        var diferencia = Math.ceil((respuesta - envio) / (1000 * 60 * 60 * 24));

        // Mostrar información calculada (opcional)
        console.log(
          "Días de respuesta calculados para devolución:",
          diferencia
        );
      }
    }
  );

  // Auto-llenar proveedor cuando se abre el modal desde una fila específica
  $(document).on("click", '[data-target="#modalIncidencias"]', function () {
    var proveedorId = $(this).closest("tr").data("proveedor-id");
    if (proveedorId) {
      $("#proveedor_incidencia").val(proveedorId);
    }
  });

  // Limpiar formulario cuando se cierra el modal
  $("#modalIncidencias").on("hidden.bs.modal", function () {
    document.getElementById("formIncidencia").reset();
  });

  // Gestión de devoluciones
  // $("#gestionarDevoluciones").on("click", function (e) {
  //   e.preventDefault();

  //   $(".modal-backdrop").remove();
  //   $("body").removeClass("modal-open");

  //   try {
  //     $("#modalDevoluciones").modal({
  //       backdrop: true,
  //       keyboard: true,
  //       focus: true,
  //       show: true,
  //     });
  //     console.log("Modal devoluciones ejecutado");
  //   } catch (error) {
  //     console.error("Error con Bootstrap modal, usando fallback:", error);

  //     // Fallback manual
  //     $("#modalDevoluciones").show().addClass("show").css({
  //       display: "block",
  //       "padding-right": "15px",
  //     });

  //     $("body").addClass("modal-open").css("padding-right", "15px");

  //     if ($(".modal-backdrop").length === 0) {
  //       $('<div class="modal-backdrop fade show"></div>').appendTo("body");
  //     }
  //   }
  // });

  //modal exportar excel
  $("#botonExportar").on("click", function (e) {
    e.preventDefault();
    $("#modalSubirExcel").modal("show");
  });

  // Gestión de devoluciones
  $("#gestionarDevoluciones").on("click", function (e) {
    e.preventDefault();
    $("#modalDevoluciones").modal("show");
  });

  // Autocompletado y auto-llenado para código de producto en devoluciones
  $("#codigo_producto").on("input", function () {
    var term = $(this).val();

    // Autocompletar códigos después de 2 caracteres
    if (term.length >= 2) {
      $.ajax({
        url: window.buscarCodigosProductosUrl,
        data: { term: term },
        success: function (data) {
          var suggestions = "";
          data.forEach(function (producto) {
            suggestions +=
              '<option value="' +
              producto.codigo +
              '" data-descripcion="' +
              producto.descripcion +
              '">';
          });

          if ($("#codigos-productos-dev-datalist").length === 0) {
            $(
              '<datalist id="codigos-productos-dev-datalist"></datalist>'
            ).appendTo("body");
          }
          $("#codigos-productos-dev-datalist").html(suggestions);
          $("#codigo_producto").attr("list", "codigos-productos-dev-datalist");
        },
      });
    }

    // Buscar producto exacto después de 3 caracteres y auto-llenar descripción
    if (term.length >= 3) {
      $.ajax({
        url: window.buscarProductoPorCodigoUrl,
        data: { codigo: term },
        success: function (response) {
          if (
            response.success &&
            response.producto &&
            response.producto.descripcion
          ) {
            $("#descripcion_producto").val(response.producto.descripcion);
            console.log(
              "Producto encontrado para devolución:",
              response.producto.descripcion
            );
          } else {
            // Limpiar el campo si no se encuentra el producto
            $("#descripcion_producto").val("");

            console.log("Producto no encontrado para código:", term);
          }
        },
        error: function (xhr) {
          console.error(
            "Error al buscar producto para devolución:",
            xhr.responseText
          );
          $("#descripcion_producto").val("");
        },
      });
    } else {
      // Si el código es muy corto, limpiar el campo producto
      $("#descripcion_producto").val("");
    }
  });

  // Cuando se selecciona un código del autocompletado en devoluciones, llenar la descripción
  $("#codigo_producto").on("change", function () {
    var selectedCode = $(this).val();
    $("#codigos-productos-dev-datalist option").each(function () {
      if ($(this).val() === selectedCode) {
        $("#descripcion_producto").val($(this).data("descripcion"));
        return false;
      }
    });
  });

  // Limpiar descripción cuando se borra el código en devoluciones
  $("#codigo_producto").on("keyup", function () {
    if ($(this).val().length === 0) {
      $("#descripcion_producto").val("");
    }
  });

  // Manejar envío del formulario de devoluciones
  $("#guardarDevolucion").on("click", function () {
    var formData = new FormData(document.getElementById("formDevolucion"));
    $.ajax({
      url: window.guardarDevolucionUrl,
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
      },
      beforeSend: function () {
        $("#guardarDevolucion")
          .prop("disabled", true)
          .html('<i class="fa fa-spinner fa-spin mr-1"></i>Guardando...');
      },
      success: function (response) {
        if (response.success) {
          $.confirm({
            title: "Éxito",
            content: "Devolución guardada correctamente.",
            type: "green",
            buttons: {
              aceptar: {
                text: "Aceptar",
                btnClass: "btn-green",
                action: function () {
                  $("#modalDevoluciones").modal("hide");
                  document.getElementById("formDevolucion").reset();
                },
              },
            },
          });
        }
      },
      error: function (xhr) {
        var errorMessage = "Error al guardar la devolución";

        if (xhr.responseJSON && xhr.responseJSON.error) {
          errorMessage = xhr.responseJSON.error;
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        }

        $.confirm({
          title: "Error",
          content: errorMessage,
          type: "red",
          buttons: {
            entendido: {
              text: "Entendido",
              btnClass: "btn-red",
            },
          },
        });
      },
      complete: function () {
        $("#guardarDevolucion")
          .prop("disabled", false)
          .html('<i class="fa fa-save mr-1"></i>Guardar Devolución');
      },
    });
  });

  // Limpiar formulario cuando se cierra el modal de devoluciones
  $("#modalDevoluciones").on("hidden.bs.modal", function () {
    document.getElementById("formDevolucion").reset();
  });

  // Event listeners para cerrar el modal de devoluciones
  $(document).on(
    "click",
    '#modalDevoluciones .close, #modalDevoluciones [data-dismiss="modal"]',
    function () {
      console.log("Cerrando modal devoluciones...");
      try {
        $("#modalDevoluciones").modal("hide");
      } catch (error) {
        $("#modalDevoluciones").hide().removeClass("show").css({
          display: "none",
          "padding-right": "",
        });
        $(".modal-backdrop").remove();
        $("body").removeClass("modal-open").css("padding-right", "");
      }
    }
  );

  // Cerrar con Escape
  $(document).on("keyup", function (e) {
    if (e.key === "Escape" && $("#modalDevoluciones").is(":visible")) {
      try {
        $("#modalDevoluciones").modal("hide");
      } catch (error) {
        $("#modalDevoluciones").hide().removeClass("show").css({
          display: "none",
          "padding-right": "",
        });
        $(".modal-backdrop").remove();
        $("body").removeClass("modal-open").css("padding-right", "");
      }
    }
  });

  // Cerrar haciendo clic en el backdrop
  $(document).on("click", ".modal-backdrop, #modalDevoluciones", function (e) {
    if (e.target === this) {
      try {
        $("#modalDevoluciones").modal("hide");
      } catch (error) {
        $("#modalDevoluciones").hide().removeClass("show").css({
          display: "none",
          "padding-right": "",
        });
        $(".modal-backdrop").remove();
        $("body").removeClass("modal-open").css("padding-right", "");
      }
    }
  });

  // Asegurar que el modal se puede cerrar
  $("#modalDevoluciones").on("shown.bs.modal", function () {
    console.log("Modal de devoluciones mostrado correctamente");
  });

  $("#modalDevoluciones").on("hidden.bs.modal", function () {
    console.log("Modal de devoluciones cerrado correctamente");
    $(".modal-backdrop").remove();
    $("body").removeClass("modal-open").css("padding-right", "");
  });

  // Verificar que el modal existe
  if ($("#modalDevoluciones").length) {
    console.log("Modal de devoluciones encontrado en el DOM");
  } else {
    console.error("Modal de devoluciones NO encontrado en el DOM");
  }

  // Autocompletado y auto-llenado para código de producto en incidencias
  $("#codigo").on("input", function () {
    var term = $(this).val();

    // Autocompletar códigos después de 2 caracteres
    if (term.length >= 2) {
      $.ajax({
        url: window.buscarCodigosProductosUrl,
        data: { term: term },
        success: function (data) {
          var suggestions = "";
          data.forEach(function (producto) {
            suggestions +=
              '<option value="' +
              producto.codigo +
              '" data-descripcion="' +
              producto.descripcion +
              '">';
          });

          if ($("#codigos-productos-datalist").length === 0) {
            $('<datalist id="codigos-productos-datalist"></datalist>').appendTo(
              "body"
            );
          }
          $("#codigos-productos-datalist").html(suggestions);
          $("#codigo").attr("list", "codigos-productos-datalist");
        },
      });
    }

    // Buscar producto exacto después de 3 caracteres
    if (term.length >= 3) {
      $.ajax({
        url: window.buscarProductoPorCodigoUrl,
        data: { codigo: term },
        success: function (response) {
          if (response.success && response.producto) {
            // Si el backend devuelve el objeto producto, usar su descripcion
            if (response.producto.descripcion) {
              $("#producto").val(response.producto.descripcion);
              console.log(
                "Producto encontrado:",
                response.producto.descripcion
              );
            } else {
              $("#producto").val("");
              console.log(
                "Producto encontrado pero sin descripción:",
                response.producto
              );
            }
          } else {
            // Limpiar el campo si no se encuentra el producto
            $("#producto").val("");
            console.log("Producto no encontrado para código:", term);
          }
        },
        error: function (xhr) {
          console.error("Error al buscar producto:", xhr.responseText);
          $("#producto").val("");
        },
      });
    } else {
      // Si el código es muy corto, limpiar el campo producto
      $("#producto").val("");
    }
  });

  // Cuando se selecciona un código del autocompletado, llenar el producto
  $("#codigo").on("change", function () {
    var selectedCode = $(this).val();
    $("#codigos-productos-datalist option").each(function () {
      if ($(this).val() === selectedCode) {
        $("#producto").val($(this).data("descripcion"));
        return false;
      }
    });
  });

  // Limpiar producto cuando se borra el código
  $("#codigo").on("keyup", function () {
    if ($(this).val().length === 0) {
      $("#producto").val("");
    }
  });

  // Funcionalidad para manejo de archivos en incidencias
  $("#archivos_incidencia").on("change", function () {
    mostrarArchivosSeleccionados(this, "lista_archivos_incidencia");
  });

  // Funcionalidad para manejo de archivos en devoluciones
  $("#archivos_devolucion").on("change", function () {
    mostrarArchivosSeleccionados(this, "lista_archivos_devolucion");
  });

  // Funcionalidad para manejo de archivos del informe
  $("#archivos_informe").on("change", function () {
    mostrarArchivosSeleccionados(this, "lista_archivos_informe");
  });

  // Función para mostrar archivos seleccionados
  function mostrarArchivosSeleccionados(inputElement, listaElementId) {
    var archivos = inputElement.files;
    var listaContainer = document.getElementById(listaElementId);
    
    if (archivos.length === 0) {
      listaContainer.innerHTML = "";
      return;
    }

    var html = '<div class="alert alert-info"><strong>Archivos seleccionados:</strong><ul class="mb-0 mt-2">';
    
    for (var i = 0; i < archivos.length; i++) {
      var archivo = archivos[i];
      var tamaño = (archivo.size / (1024 * 1024)).toFixed(2); // Convertir a MB
      var iconoTipo = obtenerIconoArchivo(archivo.name);
      
      html += '<li class="mb-1">';
      html += '<i class="' + iconoTipo + ' mr-2"></i>';
      html += '<strong>' + archivo.name + '</strong> ';
      html += '<span class="badge badge-secondary">' + tamaño + ' MB</span>';
      html += '</li>';
    }
    
    html += '</ul></div>';
    listaContainer.innerHTML = html;
  }

  // Función para obtener el icono según el tipo de archivo
  function obtenerIconoArchivo(nombreArchivo) {
    var extension = nombreArchivo.split('.').pop().toLowerCase();
    
    switch (extension) {
      case 'pdf':
        return 'fa fa-file-pdf-o text-danger';
      case 'doc':
      case 'docx':
        return 'fa fa-file-word-o text-primary';
      case 'xls':
      case 'xlsx':
        return 'fa fa-file-excel-o text-success';
      case 'jpg':
      case 'jpeg':
      case 'png':
      case 'gif':
        return 'fa fa-file-image-o text-info';
      default:
        return 'fa fa-file-o text-muted';
    }
  }

  // Limpiar archivos cuando se resetean los formularios
  $("#modalIncidencias").on("hidden.bs.modal", function () {
    document.getElementById("formIncidencia").reset();
    document.getElementById("lista_archivos_incidencia").innerHTML = "";
  });

  $("#modalDevoluciones").on("hidden.bs.modal", function () {
    document.getElementById("formDevolucion").reset();
    document.getElementById("lista_archivos_devolucion").innerHTML = "";
    document.getElementById("lista_archivos_informe").innerHTML = "";
  });
});
