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
      $("#userModal").modal("show");
    },
  });
});
