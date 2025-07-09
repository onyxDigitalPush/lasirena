$(document).ready(function () {
    // Debug URLs
    console.log('URL de edición:', window.materialKiloEditUrl);
    console.log('URL de actualización:', window.materialKiloUpdateUrl);
    
    var table = $('#table_material_kilo').DataTable({
        paging: false,       // 🚫 Desactiva paginación de DataTables
        info: false,         // 🚫 Desactiva resumen tipo "Mostrando X de Y"
        ordering: false,     // (opcional) Desactiva ordenamiento si no lo usas
        searching: false,    // 🚫 Desactiva el buscador general (usamos por columna)
        orderCellsTop: true,
        fixedHeader: true
    });

    // Aplica los filtros de las celdas del segundo thead (por columna)
    $('#table_material_kilo thead tr:eq(1) th').each(function (i) {
        var input = $(this).find('input');
        if (input.length) {
            input.on('keyup change', function () {
                if (table.column(i).search() !== this.value) {
                    table
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        }
    });

    // Funcionalidad de click en las filas para editar material
    $('#table_material_kilo tbody').on('click', 'tr.material-row', function(e) {
        // Evitar abrir el modal si se hace click en el botón de eliminar
        if ($(e.target).closest('button, form').length) {
            return;
        }
        
        var id = $(this).data('id');
        if (id) {
            loadMaterialData(id);
        }
    });

    // Función para cargar datos del material en el modal
    function loadMaterialData(id) {
        var url = window.materialKiloEditUrl.replace(':id', id);
        console.log('URL generada:', url); // Debug
        console.log('Base URL:', window.baseUrl); // Debug
        
        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                console.log('Respuesta del servidor:', data); // Debug
                if (data.success) {
                    var material = data.material;
                    
                    // Llenar los campos del modal
                    $('#material_kilo_id').val(material.id);
                    $('#codigo_material').val(material.codigo_material);
                    $('#nombre_material').val(material.nombre_material);
                    $('#nombre_proveedor').val(material.nombre_proveedor);
                    $('#factor_conversion').val(material.factor_conversion);
                    $('#ctd_emdev').val(material.ctd_emdev);
                    $('#valor_emdev').val(material.valor_emdev);
                    $('#mes').val(material.mes);
                    $('#total_kg').val(material.total_kg + ' KG');
                    
                    // Mostrar el modal
                    $('#editMaterialModal').modal('show');
                } else {
                    alert('Error al cargar los datos del material');
                }
            },
            error: function(xhr, status, error) {
                console.log('Error AJAX:', xhr.status, error); // Debug
                console.log('Response Text:', xhr.responseText); // Debug
                alert('Error al conectar con el servidor: ' + xhr.status + ' - ' + error);
            }
        });
    }

    // Manejar el envío del formulario de edición
    $('#editMaterialForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: window.materialKiloUpdateUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#editMaterialModal').modal('hide');
                    
                    // Mostrar mensaje de éxito
                    var alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        response.message +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">' +
                        '<span aria-hidden="true">&times;</span>' +
                        '</button>' +
                        '</div>';
                    
                    // Insertar el alert después del title_content
                    $('.page-title-wrapper').after(alertHtml);
                    
                    // Auto-cerrar después de 3 segundos
                    setTimeout(function() {
                        $('.alert-success').fadeOut();
                    }, 3000);
                    
                    // Recargar la página para mostrar los cambios
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    alert('Error al actualizar el material: ' + (response.message || 'Error desconocido'));
                }
            },
            error: function(xhr) {
                var errorMessage = 'Error al actualizar el material';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
            }
        });
    });
});
