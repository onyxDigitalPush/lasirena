<!doctype html>
<html lang="es">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" >
        <style>
            .custom-file-input.is-invalid ~ .custom-file-label::after {
                background-color: #dc3545;
                color: #ffffff;
            }
            .custom-file-label::after { 
                content: "Abrir..." !important;
            }
        </style>
        <title>Nestlé Health Science</title>
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js" ></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" ></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" ></script>
        <!-- <script src="js/jquery-3.4.1.min.js"> </script> -->
        <script src="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?>/_js/xlsx.full.min.js"></script>
        <script src="<?php AntiXSS::show($GLOBALS['HTTP_RESOURCES_WEB_ROOT']); ?>/_js/create_emailing_form.js" ></script>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 mt-2">
                    <div class="card  ">
                        <div class="card-header  text-white bg-secondary">
                            <h5>Nestlé Health Science Emailing Form</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-3 col-12">
                                    <div class="form-check form-check-inline  ">
                                        <input class="form-check-input " type="radio" name="excelType" id="excelTypeOptifastFormInv" value="NHS_OPTIFAST_FORM_INV">
                                        <label class="form-check-label" for="excelTypeOptifastFormInv">NHS_OPTIFAST_FORM_INV</label>
                                    </div>
                                    <div class="invalid-feedback">
                                        You have to choose a file type.
                                    </div>								  
                                </div>
                                <div class="col-sm-6 col-12">
                                    <div class="custom-file w-100 ">
                                        <input type="file" class="custom-file-input form-control " id="excelFile" name="excelFile">
                                        <label class="custom-file-label" for="excelFile" >Archivo excel...</label>
                                        <div class="invalid-feedback">
                                            You have to choose a file.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-2 col-12">
                                    <button id="excelParse" type="button" class="btn btn-primary" >
                                        <span>Load </span>
                                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrow-right-circle-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-8.354 2.646a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L9.793 7.5H5a.5.5 0 0 0 0 1h4.793l-2.147 2.146z"/>
                                        </svg>

                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row d-none" id="rowData" >
                <div class="col-12 mt-2">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" id="excel-tab" data-toggle="tab" href="#excel" role="tab" aria-controls="excel" aria-selected="true">Destinatarios <span id="num_destinatarios"></span></a>
                                </li>
                            </ul>
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="excel" role="tabpanel" aria-labelledby="home-tab">
                                    <div class="overflow-auto" id="excelData"></div>

                                </div>
                            </div>
                            <div class="overflow-auto" id="clusterData"></div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row d-none" id="rowError">
                <div class="col-12 mt-2">
                    <div class="card">
                        <div class="card-body">

                        </div>
                    </div>
                </div>
            </div>
            <div class="row d-none" id="rowSendData">
                <div class="col-12 mt-2">
                    <div class="card">
                        <div class="card-body">
                            <button type="button" class="btn btn-primary" id="send_data">Insertar destinatarios</button>
                            <p id="recipients_response"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row d-none" id="rowSendEmail">
                <div class="col-12 mt-2">
                    <div class="card">
                        <div class="card-body">
                            <button type="button" class="btn btn-primary" id="send_email">Enviar a Cola</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>