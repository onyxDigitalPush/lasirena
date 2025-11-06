// Inicializar DataTable con búsqueda exacta
$(document).ready(function() {
  if ($.fn.DataTable.isDataTable('#shippingReferenceTbl')) {
    $('#shippingReferenceTbl').DataTable().destroy();
  }
  
  var table = $('#shippingReferenceTbl').DataTable({
    "language": {
      "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
    },
    "pageLength": 25,
    "order": [[0, 'asc']],
    "searching": false // Desactivar búsqueda por defecto de DataTables
  });
  
  // Crear input de búsqueda personalizado
  if ($('.dataTables_filter input').length === 0) {
    $('.dataTables_filter').html('<label>Buscar (Coincidencia Exacta): <input type="search" class="form-control form-control-sm" placeholder="" aria-controls="shippingReferenceTbl"></label>');
  }
  
  // Búsqueda personalizada con coincidencia exacta
  $('.dataTables_filter input').on('keyup change', function() {
    var searchTerm = $(this).val().trim();
    
    // Limpiar filtros personalizados anteriores
    $.fn.dataTable.ext.search.pop();
    
    if (searchTerm === '') {
      // Si no hay búsqueda, mostrar todo
      table.draw();
      return;
    }
    
    // Agregar filtro personalizado para coincidencia exacta
    $.fn.dataTable.ext.search.push(
      function(settings, searchData, index, rowData, counter) {
        // Solo aplicar a esta tabla específica
        if (settings.nTable.id !== 'shippingReferenceTbl') {
          return true;
        }
        
        // Buscar coincidencia exacta en cualquier columna
        // searchData[0] = Codigo Material
        // searchData[1] = Descripcion Material  
        // searchData[2] = Factor Conversion
        for (var i = 0; i < searchData.length; i++) {
          // Limpiar el texto de HTML, badges y espacios
          var cellText = searchData[i].replace(/<[^>]+>/g, '').trim();
          if (cellText === searchTerm) {
            return true;
          }
        }
        
        return false;
      }
    );
    
    table.draw();
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
      $("#proveedor_id").val(material.proveedor_id);
      $("#codigo").val(material.codigo);
      $("#descripcion").val(material.descripcion);
      $("#jerarquia").val(material.jerarquia);
      $("#factor_conversion").val(material.factor_conversion);
      $("#userModal").modal("show");
    },
  });
});
