//filtros tabla
$(document).ready(function () {
  // Inicializa DataTable con Bootstrap 4
  var table = $("#table_proveedores").DataTable({
    orderCellsTop: true,
    fixedHeader: true,
  });

  // Aplica los filtros por columna (solo para las columnas con input)
  $("#table_proveedores thead tr:eq(1) th").each(function (i) {
    $("input", this).on("keyup change", function () {
      if (table.column(i).search() !== this.value) {
        table.column(i).search(this.value).draw();
      }
    });
  });
});


$(document).on("click", ".open-modal", function () {
  let url = $(this).data("url");
  console.log(url);
  $.ajax({
    url: url,
    type: "GET",
    success: function (proveedor) {
      console.log(proveedor);
      $("#codigo_proveedor_old").val(proveedor.id_proveedor);
      $("#codigo_proveedor_edit").val(proveedor.id_proveedor);
      $("#nombre_proveedor_edit").val(proveedor.nombre_proveedor);
      $("#familia_edit").val(proveedor.familia || "");
      $("#subfamilia_edit").val(proveedor.subfamilia || "");
      $("#userModal").modal("show");
    },
  });
});
