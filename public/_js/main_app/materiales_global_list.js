//Configuración de tabla
$(document).ready(function () {
  // Inicializa DataTable con Bootstrap 4
  // Deshabilitamos la búsqueda y paginación de DataTables para usar la de Laravel
  var table = $("#table_materiales_global").DataTable({
    orderCellsTop: true,
    fixedHeader: true,
    searching: false, // Deshabilitar búsqueda de DataTables
    paging: false, // Deshabilitar paginación de DataTables (usamos la de Laravel)
    info: false, // Deshabilitar información de registros
    order: [[2, 'asc'], [0, 'asc']] // Ordenar por código de proveedor y luego por código de material
  });
});


$(document).on("click", ".open-modal", function () {
  let url = $(this).data("url");
  console.log(url);
  $.ajax({
    url: url,
    type: "GET",
    success: function (material) {
      console.log(material);
      $("#id").val(material.id);
      $("#codigo").val(material.codigo);
      $("#descripcion").val(material.descripcion);
      $("#jerarquia").val(material.jerarquia);
      $("#factor_conversion").val(material.factor_conversion);
      $("#proveedor_id").val(material.proveedor_id);
      
      // Mostrar nombre del proveedor (si está disponible)
      if (material.proveedor && material.proveedor.nombre_proveedor) {
        $("#proveedor_nombre_edit").val(material.proveedor_id + " - " + material.proveedor.nombre_proveedor);
      } else {
        // Si no está disponible, intentar obtenerlo de la tabla
        var proveedorNombre = $(this).closest('tr').find('td:eq(3)').text().trim();
        if (proveedorNombre) {
          $("#proveedor_nombre_edit").val(material.proveedor_id + " - " + proveedorNombre);
        } else {
          $("#proveedor_nombre_edit").val("Proveedor " + material.proveedor_id);
        }
      }
      
      $("#userModal").modal("show");
    }.bind(this),
    error: function(xhr, status, error) {
      console.error("Error al cargar el material:", error);
      alert("Error al cargar los datos del material");
    }
  });
});
