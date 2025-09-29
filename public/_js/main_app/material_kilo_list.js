$(document).ready(function () {
    // Debug URLs
    console.log('URL de edición:', window.materialKiloEditUrl);
    console.log('URL de actualización:', window.materialKiloUpdateUrl);
    
    // Variables para manejar timeouts de búsqueda
    var searchTimeouts = {};
    
    // Configurar eventos de búsqueda en los inputs de filtro
    $('#table_material_kilo thead tr:eq(1) th').each(function (i) {
        var input = $(this).find('input');
        if (input.length) {
            var columnName = getColumnName(i);
            
            input.on('keyup change', function() {
                var $this = $(this);
                var searchValue = $this.val().trim();
                
                // Agregar clase visual mientras se está escribiendo
                $this.addClass('searching');
                
                // Limpiar timeout anterior si existe
                if (searchTimeouts[columnName]) {
                    clearTimeout(searchTimeouts[columnName]);
                }
                
                // Configurar nuevo timeout para evitar demasiadas peticiones
                searchTimeouts[columnName] = setTimeout(function() {
                    $this.removeClass('searching');
                    
                    if (searchValue) {
                        $this.addClass('has-value');
                    } else {
                        $this.removeClass('has-value');
                    }
                    
                    performServerSearch();
                }, 500); // Esperar 500ms después de que el usuario deje de escribir
            });
        }
    });
    
    // Función para obtener el nombre de la columna según su índice
    function getColumnName(columnIndex) {
        var columnNames = {
            0: 'codigo_material',
            1: 'proveedor_id', 
            2: 'nombre_proveedor',
            3: 'nombre_material',
            8: 'mes'
        };
        return columnNames[columnIndex] || null;
    }
    
    // Función para realizar búsqueda en el servidor
    function performServerSearch() {
        // Mostrar indicador de carga
        showLoadingOverlay();
        
        var params = new URLSearchParams(window.location.search);
        
        // Obtener valores de búsqueda de los inputs (iterar por TH para mantener índices de columna)
        var searches = {};
        $('#table_material_kilo thead tr:eq(1) th').each(function(i) {
            var input = $(this).find('input');
            if (!input.length) {
                return; // continuar si no hay input en esta TH
            }

            var columnName = getColumnName(i);
            var value = input.val().trim();

            if (columnName && value) {
                searches[columnName] = value;
                params.set(columnName, value);

                // Debug temporal para el campo mes
                if (columnName === 'mes') {
                    console.log('Enviando filtro mes:', {
                        columnName: columnName,
                        value: value,
                        type: typeof value,
                        isNumeric: !isNaN(value) && !isNaN(parseFloat(value))
                    });
                }
            } else if (columnName) {
                params.delete(columnName);
            }
        });
        
        // Actualizar URL y recargar página con nuevos parámetros
        var newUrl = window.location.pathname + '?' + params.toString();
        window.location.href = newUrl;
    }
    
    // Función para mostrar overlay de carga
    function showLoadingOverlay() {
        var overlay = '<div class="loading-overlay">' +
                     '<div class="text-center">' +
                     '<div class="loading-spinner"></div>' +
                     '<p class="mt-2">Buscando...</p>' +
                     '</div>' +
                     '</div>';
        $('body').append(overlay);
    }
    
    // Función para actualizar el estado de los filtros
    function updateFilterStatus() {
        var activeFilters = 0;
        $('#table_material_kilo thead tr:eq(1) th input').each(function() {
            if ($(this).val().trim() !== '') {
                activeFilters++;
            }
        });
        
        var clearButton = $('#clearFilters');
        if (activeFilters > 0) {
            clearButton.removeClass('btn-outline-secondary').addClass('btn-warning');
            clearButton.find('i').removeClass('fa-eraser').addClass('fa-filter');
            clearButton.html('<i class="fa fa-filter mr-1"></i>Limpiar Filtros (' + activeFilters + ')');
        } else {
            clearButton.removeClass('btn-warning').addClass('btn-outline-secondary');
            clearButton.find('i').removeClass('fa-filter').addClass('fa-eraser');
            clearButton.html('<i class="fa fa-eraser mr-1"></i>Limpiar Filtros');
        }
    }
    
    // Función para inicializar filtros basado en URL params
    function initializeFiltersFromUrl() {
        var params = new URLSearchParams(window.location.search);
        
        // Inicializar inputs leyendo por TH para respetar los índices de columna
        $('#table_material_kilo thead tr:eq(1) th').each(function(i) {
            var input = $(this).find('input');
            if (!input.length) {
                return;
            }

            var columnName = getColumnName(i);
            if (columnName && params.has(columnName)) {
                input.val(params.get(columnName)).addClass('has-value');
            }
        });
        
        updateFilterStatus();
    }
    
    // Funcionalidad para limpiar todos los filtros
    $('#clearFilters').on('click', function() {
        showLoadingOverlay();
        
        // Crear URL solo con parámetros que no sean de búsqueda
        var params = new URLSearchParams(window.location.search);
        var searchParams = ['codigo_material', 'proveedor_id', 'nombre_proveedor', 'nombre_material', 'mes'];
        
        searchParams.forEach(function(param) {
            params.delete(param);
        });
        
        // Redirigir sin parámetros de búsqueda
        var newUrl = window.location.pathname;
        if (params.toString()) {
            newUrl += '?' + params.toString();
        }
        
        window.location.href = newUrl;
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
    
    // Inicializar filtros al cargar la página
    initializeFiltersFromUrl();
});