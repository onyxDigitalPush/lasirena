///////////////////////////////////////////////////////////////
//
// PLUGIN DE JQUERY PARA LA VALIDACIÓN ESTANDAR DE FORMULARIOS
///////////////////////////////////////////////////////////////

(function ($) {
    //Adaptación del plugin a la opción de multi idioma

    //Idioma del plugin por defecto Castellano
    var idioma = 'es';
    var textos = ['Formato de e-mail inv&aacute;lido', 'Num&eacute;rico', 'La URL introducida no es v&aacute;lida', 'Obligatorio', 'Formato de NIF/NIE inv&aacute;lido', 'No permitido', 'N&uacute;mero de tarjeta incorrecto'];

    function valida_campos_anidados(input)
    {
        // Modificación para formularios anidados
        var parent_name = input.attr('data-validation-parent');
        if (parent_name != undefined)
        {
            if ($("input[id$='" + parent_name + "']").is(':checked') === false)
            {
                return true;
            }
        }

        return false;
    }

    if ($('#jq_validacion_formulario').length)
    {
        //Se ha identificado correctamente el script en el DOM actual

        //Recuperamos el src (query string) de la llamada al propio script
        var qs = $('#jq_validacion_formulario').attr('src').match(/\w+=\w+/g);
        //Emulación de la variable $_GET de PHP
        var _GET = [];

        //Recogemos las dimensiones del query string
        var t, i = qs.length;
        while (i--) {
            //t[0] nombre del parametro y t[1] su valor
            t = qs[i].split("=");

            //Asignación de variable a modo de php
            _GET[t[0]] = t[1];

        }

        //Inicialización del array de textos del plugin segun el idioma pasado
        switch (_GET['idioma'])
        {
            case 'ca': //Catalán
                idioma = _GET['idioma'];
				textos = ['Format de correu incorrecte', 'Num&egrave;ric', 'La URL introdu&iuml;da no es v&agrave;lida', 'Obligatori', 'Format de NIF/NIE incorrecte', 'No perm&egrave;s', 'N&uacute;mero de targeta incorrecte'];
                break;
            case 'en': //Inglés
                idioma = _GET['idioma'];
                textos = ['Invalid email format', 'Numerical', 'The URL you entered is invalid', 'Required', 'Invalid NIF/NIE format', 'Not allowed', 'Wrong card number'];
                break;
            case 'fr': //Francés
                idioma = _GET['idioma'];
                textos = ['Format e-mail invalide', 'Num&eacute;rique', "L'URL que vous avez entr&eacute; n'est pas valide", 'Obligatoire', 'Format NIF/NIE invalide', 'Pas permis', 'Le numéro de carte incorrecte'];
                break;
            case 'de': //Alemán
                idioma = _GET['idioma'];
                textos = ['Format ung&uuml;ltige E-Mail', 'Numerisch', "Die eingegebene URL ist ung&uuml;ltig", 'Pflichtfeld', 'Format ung&uuml;ltige NIF/NIE', 'Nicht erlaubt', 'Die Anzahl der fehlerhaften Karte'];
                break;
            case 'pt': //Portugués
                idioma = _GET['idioma'];
                textos = ['Formato de e-mail inv&acute;lido', 'Num&eacute;rico', 'La URL introdu&iuml;da no es v&agrave;lida', 'Obrigat&oacute;rio', 'Formato de NIF/NIE inv&acute;lido', 'N&atilde;o &eacute; permitido', 'N&uacute;mero do cart&atilde;o errado'];
                break;				
        }
    }

    // OBJETO DE VALIDACIÓN DE FORMULARIO (SINGLETON)
    var Validacion_formulario = function () {
        var opciones = {// Objeto privado
            // Validación de e-mail
            email: {
                verifica: function (input) {

                    if ($.trim(input.val()))
                        return valida_patron(input.val(), "^[a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[@][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[.][a-zA-Z]{2,4}$");
                    return true;
                },
                alerta: textos[0]
            },
            // Validación numerico
            numerico: {
                verifica: function (input) {

                    if (input.val())
                        return $.isNumeric(input.val());
                    return true;
                },
                alerta: textos[1]
            },
            // Validación de URL
            url: {
                verifica: function (input) {

                    if ($.trim(input.val()))
                        return valida_patron(input.val(), "^https?://(.+\.)+.{2,4}(/.*)?$");
                    return true;
                },
                alerta: textos[2]
            },
            // Validación de campos obligatorios
            obligatorio: {
                verifica: function (input) {
                    if (input.is(':disabled'))
                        return true;

                    // Saltaremos la validación si no se ha seleccionado el parent
                    if (valida_campos_anidados(input) === true)
                        return true;

                    if ($.trim(input.val()))
                        return true;
                    else
                        return false;
                },
                alerta: textos[3]
            },
            // Validación de campos check obligatorios
            check_obligatorio: {
                verifica: function (input) {
                    if (input.is(':disabled'))
                        return true;

                    // Saltaremos la validación si no se ha seleccionado el parent
                    if (valida_campos_anidados(input) === true)
                        return true;

                    if (input.is(':checked'))
                        return true;
                    else
                        return false;
                },
                alerta: textos[3]
            },
            // Validación de campos check mínimo uno obligatorio
            checkbox_min_uno: {
                verifica: function (input) {
                    if (input.is(':disabled'))
                        return true;

                    // Saltaremos la validación si no se ha seleccionado el parent
                    if (valida_campos_anidados(input) === true)
                        return true;

                    var checkbox_type = input.attr('data-validation-group-name');

                    var response = false;

                    $("input[data-validation-group-name^='" + checkbox_type + "']").each(function () {
                        if ($(this).is(':checked'))
                            response = true;
                    });

                    return response;
                },
                alerta: textos[3]
            },
            // Validación de campos radio obligatorios
            radio_obligatorio: {
                verifica: function (input) {
                    if (input.is(':disabled'))
                        return true;

                    // Saltaremos la validación si no se ha seleccionado el parent
                    if (valida_campos_anidados(input) === true)
                        return true;

                    return $('[name="' + input.attr('name') + '"]').is(':checked');
                },
                alerta: textos[3]
            },
            // Validación de dni
            dni: {
                verifica: function (input) {
                    if ($.trim(input.val()))
                        return valida_patron(input.val(), "^(([a-zA-Z][0-9]{8})|([0-9]{8}[a-zA-Z])|([a-zA-Z][0-9]{7}[a-zA-Z])|([a-zA-Z][0-9]{8}[a-zA-Z]))$");
                    return true;
                },
                alerta: textos[4]
            },
            // Filtrado de código malicioso
            securizado: {
                verifica: function (input) {
                    if ($.trim(input.val()))
                    {
                        re = /((\\%3C)|<)[^\\n]+((\\%3E)|>)/;
                        str = input.val();
                        str = str.toLowerCase();
                        m = re.exec(str);
                        if (m != null)
                        {
                            return false;
                        }

                        return true;
                    }
                    return true;
                },
                alerta: textos[5]
            },
            // Filtrado de código malicioso
            visa: {
                verifica: function (input) {
                    if ($.trim(input.val()))
                    {
                        return luhn_validation(input.val());
                    }
                    return true;
                },
                alerta: textos[6]
            }
        }

        // Método privado de validación de patrones mediante expresiones regulares
        var valida_patron = function (valor, patron) {
            var regex = new RegExp(patron, "i");
            return regex.test(valor);
        }

        //Método encargado de la validación con el algoritmo de Luhn
        var luhn_validation = (function (args) {
            return function (number) {
                var
                        len = number.length,
                        bit = 1,
                        sum = 0,
                        val;

                while (len) {
                    val = parseInt(number.charAt(--len), 10);
                    sum += (bit ^= 1) ? args[val] : val;
                }

                return sum && sum % 10 === 0;
            };
        }([0, 2, 4, 6, 8, 1, 3, 5, 7, 9]));

        // Metodo validación que permite anadir nuevas validaciones personalizadas desde el formulario sin necesidad de modificar el Plugin (ver ejemplos en documentacion de Framework Novaigrup)
        return {
            incluye_opcion: function (nombre, opcion) {
                opciones[nombre] = opcion;
            },
            retorna_opcion: function (nombre) {
                return opciones[nombre];
            }
        }
    }

    // OBJETO DE FORMULARIO (representa una instancia de formulario en el DOM)
    var global_campos = undefined;
    var Form = function (form) {
        var campos = [];

        // Iteramos sobre todos los campos del formulario que tengan el atributo "validacion" usando el metodo "each" de jQuery.
        // Por cada campo localizado creamos una instancia del objeto "Campo"
        form.find("[data-validacion]").each(function () {
            var campo = $(this);
            if (campo.attr('data-validacion') !== undefined) {
                campos.push(new Campo(campo));
            }
        });
        global_campos = campos;
    }

    // METODOS DEL OBJETO FORM (metodo de herencia de prototipo)
    Form.prototype = {
        valida: function () {
            for (campo in global_campos)
            {
                if ($.isNumeric(campo))
                {
                    global_campos[campo].setChanged();
                    global_campos[campo].valida();
                }
            }
        },
        verifica_validez: function () {
            for (campo in global_campos) {
                if ($.isNumeric(campo) && !global_campos[campo].valido) {

                    global_campos[campo].campo.focus(); // Establecemos el foco en el primer campo que tiene error para que el usuario pueda solventarlo
                    return false; // Siempre que detectemos un campo inválido retornaremos "false"
                }
            }
            return true;
        }
    }

    // OBJETO DE CAMPO
    var Campo = function (campo) {
        this.campo = campo;
        this.valido = false;
        this.control_evento("change"); // Cada campo es validado cuando se ejecuta el evento "change"
        this.control_evento("keyup");
        this.control_evento("keypress");
    }

    // MÉTODOS DEL OBJETO CAMPO (método de herencia de prototipo)

    Campo.prototype = {
        // Método para capturar los diferentes tipos de eventos generados en el objeto campo
        control_evento: function (event) {
            var obj = this;

            var delay = (function () {
                var timer = 0;
                return function (callback, ms) {
                    clearTimeout(timer);
                    timer = setTimeout(callback, ms);
                };
            })();

            if (event == "change") {
                obj.campo.bind("change", function () {
                    delay(function () {
                        obj.setChanged();//Marcamos un flag para evitar la validación la primera vez que escribimos
                        return obj.valida();
                    }, 750);
                });
            }

            if (event == "keyup") {
                obj.campo.bind("keyup", function (e) {
                    if (obj.campo.changed === true) //Comprobamos que no sea la primera vez que interactuamos con el campo
                    {
                        if (e.which != 9)
                        {
                            delay(function () {
                                return obj.valida();
                            }, 750);
                        }
                    }
                });
            }

            if (event == "keypress") {
                obj.campo.bind("keypress", function (e) {
                    campo = obj.campo;
                    tipos = (campo.attr("data-validacion")) ? campo.attr("data-validacion").split(" ") : '';
                    for (var tipo in tipos) {
                        if ($.isNumeric(tipo) && tipos[tipo] == 'securizado')
                        {
                            if (e.which == 39 || e.which == 60 || e.which == 62)
                            {
                                return false;
                            }
                        }
                    }
                });
            }
        },
        setChanged: function () {
            this.campo.changed = true;
        },
        // Método que ejecuta la validación del campo
        valida: function () {
            var obj = this, // Creamos una referencia interna al objeto campo
                    campo = obj.campo, // El actual input o textarea del objeto
                    css_error = "error_validacion_form",
                    html_error = $(document.createElement("span")).addClass(css_error),
                    tipos = (campo.attr("data-validacion")) ? campo.attr("data-validacion").split(" ") : '', // A un campo se le puede asignar diferentes tipos de validación separados por espacios
                    contenedor = campo.parent(),
                    errores = [];

            campo.next(".error_validacion_form").remove(); // Si ya hemos alertado de un error, lo eliminamos antes de proceder a una nueva validación
            contenedor.find(".error_validacion_form").remove();

            /*    
             if ( campo.attr( 'data-validacion' ) === 'radio_obligatorio' )
             {
             $( '[name="' + campo.attr( 'name' ) + '"]' ).next( ".error_validacion_form" ).remove();
             }
             */

            // Iteramos por cada tipo de validación (opciones de validación)
            for (var tipo in tipos) {
                if ($.isNumeric(tipo) && tipos[tipo] != '')
                {
                    var opcion = $.Validacion_formulario.retorna_opcion(tipos[tipo]); // Extraemos la opción de validación de el objeto Validacion_formulario
                    if (!opcion.verifica(campo)) {
                        contenedor.addClass("error");

                        if (!campo.attr('data-validacion-hide-error'))
                        {
                            if (!campo.attr('data-validacion-text'))
                            {
                                errores.push(opcion.alerta);
                            } else
                            {
                                errores.push(campo.attr('data-validacion-text'));
                            }
                        }

                    }
                }

            }

            if (errores.length) { // Si existen errores
                /*
                 obj.campo.unbind( "keyup" ); // Eliminamos el actual manejador de evento
                 obj.control_evento( "keyup" ); // Capturamos el evento keyup del campo para notificar lo más rápidamente posible al usuario si el error has sido corregido*/
                campo.after(html_error.empty()); // Borramos los errores existentes, si existen. 
                for (error in errores)
                {
                    if ($.isNumeric(error))
                    {
                        html_error.append(errores[error]);
                        contenedor.append(html_error);
                    }
                }

                obj.valido = false;
            } else // Si no hay errores
            {
                html_error.remove();
                contenedor.removeClass("error");
                contenedor.find(".error_validacion_form").remove();

                if (campo.attr('data-validacion') === 'radio_obligatorio')
                {
                    if ($('[name="' + campo.attr('name') + '"]').is(':checked'))
                    {
                        $('[name="' + campo.attr('name') + '"]').parent().removeClass("error");
                        $('[name="' + campo.attr('name') + '"]').parent().find(".error_validacion_form").remove();
                    }
                }

                obj.valido = true;
            }
        }
    }


    // EXTENDEMOS JQUERY PARA QUE EL PLUGIN SEA ACCESIBLE DESDE EL OBJETO jQuery (extensión del prototipo)

    $.extend($.fn, {
        // Método de creación de una nueva instancia de validación asociada al objeto Form
        validacion: function () {
            var validador = new Form($(this));
            $.data(document.body, 'validador', validador);

            // Controlamos el evento "submit" del formulario instanciado
            $(this).bind("submit", function (e) {
                validador.valida();
                if (!validador.verifica_validez()) {
                    e.preventDefault();
                }
            });
        },
        valida: function () {
            var validador = $.data(document.body, 'validador'); // Guardamos los datos del objeto jQuery con el método $.data. Con esto podemos volver a llamar a la misma instancia en una nueva validación, evitando así crear una nueva instancia cada vez
            validador.valida();
            return validador.verifica_validez();
        },
        retorna_errores: function () {
            for (campo in global_campos) {
                if (!global_campos[campo].valido)
                {
                    console.log(global_campos[campo].campo.attr('name') + ' ' + global_campos[campo].campo.val());
                }
            }
        }
    });

    // Una nueva instancia de nuestro objeto en el namespace de jQuery
    $.Validacion_formulario = new Validacion_formulario();
})(jQuery); // Al pasar jQuery a la función podemos hacer uso de $ sin generar conflictos
