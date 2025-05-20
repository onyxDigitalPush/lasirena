

$(document).ready(function () {
    if (!have_redemption) {
        $('#send_redemption_data').hide();
    }

    let json_sales = [];
    $(".redemption_check").change(function () {

        let product_id = this.id;
        if (this.checked) {
            /*json_sales[product_id] = {
                'product_id': product_id,
                'sales': 2
            };*/
            json_sales.push({
                'product_id': product_id,
                'sales': $("#sales_" + product_id).val()
            });
            $('#confirm_button').prop('disabled', false);

            $('#str_sales').val(JSON.stringify(json_sales));

        } else {
            for (let i = 0; i < json_sales.length; i++) {

                if (json_sales[i].product_id == product_id) {
                    json_sales.splice(i, 1);
                    $('#str_sales').val(JSON.stringify(json_sales));
                }

            }

            if (json_sales.length <= 0) {
                $('#confirm_button').prop('disabled', true);
            }

        }
    });
    $(".sales_input").change(function () {
        let product_id = this.id.replace('sales_', '');
        for (sales of json_sales) {
            if (sales.product_id == product_id) {
                sales.sales = $(this).val();
                $('#str_sales').val(JSON.stringify(json_sales));
            }
        }

    });



    $(document).on('click', '#confirm_button', function (e) {

        var form_excel = $("#form_send_redemption_mail");
        form_excel.validacion();

        e.preventDefault();
        if (!form_excel.valida()) {
            return false;
        } else {
            $.confirm({
                title: 'Enviar Emails de redención',
                content: 'Estas seguro que quieres enviar los emails?',
                buttons: {
                    cancelar: {
                        text: 'Cancelar',
                        btnClass: 'btn-red',
                    },
                    confirmar: {
                        text: 'Enviar',
                        btnClass: 'btn-dark',
                        action: function () {
                            e.preventDefault();
                            $("#form_send_redemption_mail").submit();
                        }
                    }
                }
            });
        }


    });
});