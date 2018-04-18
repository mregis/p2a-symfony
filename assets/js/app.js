/**
 * Created by Marcos Regis on 15/02/2018.
 */

// var $ = require('jquery');

global.$ = global.jQuery = $;

const dataTableTranslation = require('../localisation/dataTables.pt_BR.json');

$(document).ready(function () {
    $("#myModal").modal("show");
    $(".dataTable").DataTable(
        {
            "language": dataTableTranslation
        }
    );
});


