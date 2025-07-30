$(document).ready(function () {
  // Debug URLs
  console.log("URL de edición:", window.materialKiloEditUrl);
  console.log("URL de actualización:", window.materialKiloUpdateUrl);
  console.log("aca");
  var table = $("#table_material_kilo").DataTable({
    paging: false, // 🚫 Desactiva paginación de DataTables
    info: false, // 🚫 Desactiva resumen tipo "Mostrando X de Y"
    ordering: false, //  ordenamiento
    searching: true, // ✅ Activa búsqueda para filtros por columna
    dom: "t", // Solo muestra la tabla (sin buscador general)
    order: [],
    orderCellsTop: true,
    fixedHeader: true,
    language: {
      search: "Buscar:",
      searchPlaceholder: "Buscar...",
      emptyTable: "No hay datos disponibles",
      zeroRecords: "No se encontraron registros que coincidan",
    },
    columnDefs: [
      { targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10], orderable: true },
    ],
  });

  // Aplica los filtros de las celdas del segundo thead (por columna)
  $("#table_material_kilo thead tr:eq(1) th").each(function (i) {
    var input = $(this).find("input");
    if (input.length) {
      // Configurar evento con debounce para mejor rendimiento
      let timeout;
      input.on("keyup change clear", function () {
        var that = this;
        clearTimeout(timeout);
        timeout = setTimeout(function () {
          if (table.column(i).search() !== that.value) {
            table.column(i).search(that.value).draw();

            // Actualizar estado de filtros
            updateFilterStatus();
          }
        }, 300); // Esperar 300ms después de que el usuario deje de escribir
      });

      // Limpiar filtro al hacer click en el input vacío
      input.on("click", function () {
        if (this.value === "") {
          table.column(i).search("").draw();
          updateFilterStatus();
        }
      });
    }
  });

  // Función para actualizar el estado de los filtros
  function updateFilterStatus() {
    var activeFilters = 0;
    $("#table_material_kilo thead tr:eq(1) th input").each(function () {
      if ($(this).val().trim() !== "") {
        activeFilters++;
      }
    });

    var clearButton = $("#clearFilters");
    if (activeFilters > 0) {
      clearButton.removeClass("btn-outline-secondary").addClass("btn-warning");
      clearButton.find("i").removeClass("fa-eraser").addClass("fa-filter");
      clearButton.html(
        '<i class="fa fa-filter mr-1"></i>Limpiar Filtros (' +
          activeFilters +
          ")"
      );
    } else {
      clearButton.removeClass("btn-warning").addClass("btn-outline-secondary");
      clearButton.find("i").removeClass("fa-filter").addClass("fa-eraser");
      clearButton.html('<i class="fa fa-eraser mr-1"></i>Limpiar Filtros');
    }
  }

  // Funcionalidad para limpiar todos los filtros
  $("#clearFilters").on("click", function () {
    // Limpiar todos los inputs de filtro
    $("#table_material_kilo thead tr:eq(1) th input").val("");

    // Limpiar todas las búsquedas por columna y redibujar
    table.search("").columns().search("").draw();

    // Actualizar estado
    updateFilterStatus();

    // Mostrar notificación
    var clearButton = $(this);
    clearButton
      .addClass("btn-success")
      .removeClass("btn-outline-secondary btn-warning");
    var originalHtml = '<i class="fa fa-eraser mr-1"></i>Limpiar Filtros';
    clearButton.html('<i class="fa fa-check mr-1"></i>Filtros Limpiados');

    setTimeout(() => {
      clearButton.removeClass("btn-success").addClass("btn-outline-secondary");
      clearButton.html(originalHtml);
    }, 1500);
  });

  // Funcionalidad de click en las filas para editar material
  $("#table_material_kilo tbody").on("click", "tr.material-row", function (e) {
    // Evitar abrir el modal si se hace click en el botón de eliminar
    if ($(e.target).closest("button, form").length) {
      return;
    }

    var id = $(this).data("id");
    if (id) {
      loadMaterialData(id);
    }
  });

  // Función para cargar datos del material en el modal
  function loadMaterialData(id) {
    var url = window.materialKiloEditUrl.replace(":id", id);
    console.log("URL generada:", url); // Debug
    console.log("Base URL:", window.baseUrl); // Debug

    $.ajax({
      url: url,
      type: "GET",
      success: function (data) {
        console.log("Respuesta del servidor:", data); // Debug
        if (data.success) {
          var material = data.material;
          console.log(material);

          // Llenar los campos del modal
          $("#material_kilo_id").val(material.id);
          $("#codigo_material").val(material.codigo_material);
          $("#nombre_material").val(material.nombre_material);
          $("#nombre_proveedor").val(material.nombre_proveedor);
          $("#factor_conversion").val(material.factor_conversion);
          $("#ctd_emdev").val(material.ctd_emdev);
          $("#valor_emdev").val(material.valor_emdev);
          $("#mes").val(material.mes);
          $("#total_kg").val(material.total_kg + " KG");

          // Mostrar el modal
          $("#editMaterialModal").modal("show");
        } else {
          alert("Error al cargar los datos del material");
        }
      },
      error: function (xhr, status, error) {
        console.log("Error AJAX:", xhr.status, error); // Debug
        console.log("Response Text:", xhr.responseText); // Debug
        alert(
          "Error al conectar con el servidor: " + xhr.status + " - " + error
        );
      },
    });
  }

  // Manejar el envío del formulario de edición
  $("#editMaterialForm").on("submit", function (e) {
    e.preventDefault();

    var formData = new FormData(this);

    $.ajax({
      url: window.materialKiloUpdateUrl,
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.success) {
          $("#editMaterialModal").modal("hide");

          // Mostrar mensaje de éxito
          var alertHtml =
            '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
            response.message +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">' +
            '<span aria-hidden="true">&times;</span>' +
            "</button>" +
            "</div>";

          // Insertar el alert después del title_content
          $(".page-title-wrapper").after(alertHtml);

          // Auto-cerrar después de 3 segundos
          setTimeout(function () {
            $(".alert-success").fadeOut();
          }, 3000);

          // Recargar la página para mostrar los cambios
          setTimeout(function () {
            location.reload();
          }, 1000);
        } else {
          alert(
            "Error al actualizar el material: " +
              (response.message || "Error desconocido")
          );
        }
      },
      error: function (xhr) {
        var errorMessage = "Error al actualizar el material";
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        }
        alert(errorMessage);
      },
    });
  });
});
