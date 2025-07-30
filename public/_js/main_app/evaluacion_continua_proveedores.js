$(document).ready(function () {
  var table = $("#table_evaluacion_continua").DataTable({
    paging: true,
    pageLength: 25,
    info: true,
    ordering: true,
    searching: true,
    orderCellsTop: true,
    fixedHeader: false,
    order: [[2, "desc"]],
    scrollX: true,
    dom: '<"top"f>rt<"bottom"lip><"clear">',
    columnDefs: [
      {
        targets: [0, 1],
        orderable: true,
        searchable: true,
      },
      {
        targets: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
        orderable: true,
        searchable: false,
      },
      {
        targets: [2, 3, 5, 6], // Ajusta aquí los índices de columnas
        orderable: true,
        searchable: false,
        render: function (data, type, row) {
          if (type === "sort" || type === "type" || type === "filter") {
            var text = $("<div>").html(data).text();
            var num = parseFloat(text.replace(/[^\d.-]/g, ""));
            return isNaN(num) ? 0 : num;
          }
          return data;
        },
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
  });

  // Función para actualizar totales basado en filas filtradas
  function actualizarTotales() {
    var filasVisibles = table.rows({ filter: "applied" }).data();
    var totalProveedores = filasVisibles.length;
    var totalKg = 0;

    filasVisibles.each(function (data, index) {
      var kgText = $(data[2]).text() || data[2];
      var kgValue = parseFloat(kgText.replace(/[^\d.-]/g, "")) || 0;
      totalKg += kgValue;
    });

    $("#total-proveedores").text(totalProveedores);
    $("#total-kg-general").text(
      new Intl.NumberFormat("es-ES", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(totalKg) + " kg"
    );
  }

  // Filtro de proveedor en tiempo real
  $("#filtro_proveedor").on("change", function () {
    var searchValue = this.value.trim();
    table.column(1).search(searchValue).draw();
    actualizarTotales();
  });

  // Filtro de ID proveedor
  $("#filtro_id_proveedor").on("keyup input", function () {
    var searchValue = this.value.trim();
    table.column(0).search(searchValue).draw();
    actualizarTotales();
  });

  // Limpiar filtros de tabla
  $("#limpiarFiltrosTabla").on("click", function () {
    $("#filtro_proveedor").val("");
    $("#filtro_id_proveedor").val("");
    table.search("").columns().search("").draw();
    actualizarTotales();
  });

  // Actualizar totales cuando se use el buscador general
  table.on("search.dt", function () {
    actualizarTotales();
  });

  // Actualizar totales al cargar
  actualizarTotales();

  // Funcionalidad para filtros por mes y año
  $("#aplicarFiltros").on("click", function () {
    var mes = $("#filtro_mes").val();
    var año = $("#filtro_año").val();
    var proveedor = $("#filtro_proveedor").val();
    var idProveedor = $("#filtro_id_proveedor").val();

    // Validar que al menos año esté seleccionado
    if (!año) {
      alert("Por favor seleccione un año");
      return;
    }

    // Construir URL con parámetros
    var url = new URL(window.location.href);

    // Limpiar parámetros existentes
    url.searchParams.delete("mes");
    url.searchParams.delete("año");
    url.searchParams.delete("proveedor");
    url.searchParams.delete("id_proveedor");

    // Agregar nuevos parámetros
    if (mes) {
      url.searchParams.set("mes", mes);
    }

    if (año) {
      url.searchParams.set("año", año);
    }

    if (proveedor) {
      url.searchParams.set("proveedor", proveedor);
    }

    if (idProveedor) {
      url.searchParams.set("id_proveedor", idProveedor);
    }

    // Mostrar mensaje de carga
    $("#aplicarFiltros").html(
      '<i class="fa fa-spinner fa-spin mr-1"></i>Aplicando...'
    );

    // Recargar página con nuevos filtros
    window.location.href = url.toString();
  });

  $("#limpiarFiltros").on("click", function () {
    // Mostrar mensaje de confirmación
    if (confirm("¿Está seguro que desea limpiar todos los filtros?")) {
      // Redireccionar sin parámetros de filtro
      var url = new URL(window.location.href);
      url.searchParams.delete("mes");
      url.searchParams.delete("año");
      url.searchParams.delete("proveedor");
      url.searchParams.delete("id_proveedor");

      // Mantener solo el año actual
      url.searchParams.set("año", new Date().getFullYear());

      $("#limpiarFiltros").html(
        '<i class="fa fa-spinner fa-spin mr-1"></i>Limpiando...'
      );
      window.location.href = url.toString();
    }
  });

  // Inicializar filtros desde URL
  var urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has("mes")) {
    $("#filtro_mes").val(urlParams.get("mes"));
  }
  if (urlParams.has("año")) {
    $("#filtro_año").val(urlParams.get("año"));
  }
  if (urlParams.has("proveedor")) {
    $("#filtro_proveedor").val(urlParams.get("proveedor"));
  }
  if (urlParams.has("id_proveedor")) {
    $("#filtro_id_proveedor").val(urlParams.get("id_proveedor"));
  }

  // Mostrar mensaje si hay filtros aplicados
  if (urlParams.has("proveedor") || urlParams.has("id_proveedor")) {
    var mensaje = "Filtros aplicados: ";
    var filtros = [];

    if (urlParams.has("proveedor")) {
      filtros.push("Proveedor: " + urlParams.get("proveedor"));
    }
    if (urlParams.has("id_proveedor")) {
      filtros.push("ID: " + urlParams.get("id_proveedor"));
    }

    mensaje += filtros.join(", ");

    // Mostrar mensaje temporal
    $("body").prepend(
      '<div class="alert alert-info alert-dismissible fade show" role="alert" style="position: fixed; top: 10px; right: 10px; z-index: 9999; max-width: 400px;">' +
        '<i class="fa fa-info-circle mr-2"></i>' +
        mensaje +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
        '<span aria-hidden="true">&times;</span>' +
        "</button>" +
        "</div>"
    );

    // Auto-ocultar después de 5 segundos
    setTimeout(function () {
      $(".alert").fadeOut();
    }, 5000);
  }
});
