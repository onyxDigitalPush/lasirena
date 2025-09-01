$(document).on("click", ".open-modal", function () {
  let url = $(this).data("url");
  console.log(url);
  $.ajax({
    url: url,
    type: "GET",
    success: function (user) {
      console.log(user);
      $("#userId").val(user.id);
      $("#userName").val(user.name);
      $("#userEmail").val(user.email);
      console.log("Tipo de usuario recibido:", user.type_user);
      // unset previous selections
      $(".edit-type").prop('checked', false);
      // Preferimos type_user_multi (array), si no existe usamos type_user
      var types = user.type_user_multi || user.type_user || [];
      // Normalizar a array
      if (!Array.isArray(types)) {
        types = [types];
      }
      types.forEach(function(t) {
        $("#edit_type_" + t).prop('checked', true);
      });
      $("#userModal").modal("show");
    },
  });
});
