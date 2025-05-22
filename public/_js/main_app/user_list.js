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
      $("#userType").val(user.type_user);
      $("#userModal").modal("show");
    },
  });
});
