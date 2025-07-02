$(document).on("click", ".open-modal", function () {
  let url = $(this).data("url");
  console.log(url);
  $.ajax({
    url: url,
    type: "GET",
    success: function (material) {
      console.log(material);
        $("#id").val(material.id);
      $("#proveedor_id").val(material.proveedor_id);
      $("#codigo").val(material.codigo);
      $("#descripcion").val(material.descripcion);
      $("#jerarquia").val(material.jerarquia);
      $("#factor_conversion").val(material.factor_conversion);
      $("#userModal").modal("show");
    },
  });
});
