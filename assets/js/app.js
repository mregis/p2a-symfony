/**
 * Created by Marcos Regis on 15/02/2018.
 */

global.$ = global.jQuery = $;

// ### For Datatables
import pdfMake from "pdfmake/build/pdfmake";
import pdfFonts from "pdfmake/build/vfs_fonts";
pdfMake.vfs = pdfFonts.pdfMake.vfs;

const JSZip = require('jszip');
window.JSZip = JSZip;

const dataTableTranslation = require('../localisation/dataTables.pt_BR.json');

// ##### Global vars
// DataTables
global.pag_table = null;
global.oTable = null;
// Modals texts
global.$closeText = 'close',
    global.$failTitle = 'fail.title',
    global.$failMessage = 'fail.message',
    global.$activeText = 'active',
    global.$inactiveText = 'inactive',
    global.$workinText = 'working';

// Status Change Modal vars
global.$statusChangePath = null,
    global.$modalBodyHTML = null,
    global.$modalTitleText = null;

// Delete Modal vars
global.$deletePath = null,
    global.$deleteModalBodyHTML = null,
    global.$deleteModalTitleText = null;

// Default DataTables
global.$dataTableOptions = {"dom": "<'row'<'col-10'r>>" +
                                    "<'row'<'col-5'l><'col-7 text-right'f>>" +
                                    "<'row'<'col-sm-12'B>>" +
                                    "<'row'<'col-sm-12't>>" +
                                    "<'row'<'col-5'i><'col-7'p>>",
                            "buttons": [
                                {extend: "copy", text: "<i class='fas fa-copy'></i> Copiar", 'page': 'all'},
                                {extend: "print", text: "<i class='fas fa-print'></i> Imprimir", 'page': 'all'},
                                {extend: "excelHtml5", text: "<i class='fas fa-th-list'></i> Excel HTML5 Export"},
                                {extend: "pdfHtml5", text: "<i class='fas fa-save'></i> Salvar PDF ", title: "Arquivo"}
                            ],
                            "language": dataTableTranslation }
// Paginated DataTables
global.$fnCreatedRow = null,
    global.$fnRowCallback = null;
    global.$paginatedDataTablesSource = './wtf',
    global.$domDTPaginated = "<'row'<'col-10'r>>" +
        "<'row'<'col-5'l><'col-7 text-right'f>>" +
        "<'row'<'col-sm-12'B>>" +
        "<'row'<'col-sm-12't>>" +
        "<'row'<'col-5'i><'col-7'p>>",
    global.$paginatedDataTablesColumns = null;

// Autocomplete Fields
global.$BloodhoundRemotePath = "./wtf",
    global.$BloodhoundPrefetchPath = "./wtf",
    global.$BloodhoundIdentifyFunction = null,
    global.$typeaheadDisplay = "name",
    global.$typeaheadName = "name",
    global.$BloodhoundRemote = $BloodhoundRemotePath,
    global.$typeaheadTransformFunction = null;
// jQuery Typeahead
global.$typeaheadInput = typeof $typeaheadInput == 'undefined' ? "#remote :input:first" : $typeaheadInput,
    global.$typeahedRemoteUrl = typeof $typeahedRemoteUrl == 'undefined' ? "./wtf" : $typeahedRemoteUrl,
    global.$typeaheadTemplate = typeof $typeaheadTemplate == 'undefined' ? "{{display}} <small style='color:#999;'>{{group}}</small>" : $typeaheadTemplate,
    global.$typeaheadSource = typeof $typeaheadSource == 'undefined' ? null : $typeaheadSource,
    global.$typeaheadCallback = typeof $typeaheadCallback == 'undefined' ? null : $typeaheadCallback,
    global.$typeaheadEmptyTemplate = typeof $typeaheadEmptyTemplate == 'undefined' ? null : $typeaheadEmptyTemplate,
    global.$typeaheadBackdrop = typeof $typeaheadBackdrop == 'undefined' ? null : $typeaheadBackdrop;


$(document).ready(function () {
    var myModal = $("#myModal").modal("show");
    oTable = $(".dataTable").DataTable($dataTableOptions);
    // Custom Sort for Dates in dd/mm/YYYY format
    jQuery.extend( jQuery.fn.dataTableExt.oSort, {
        "customDateFormat-pre": function ( date ) {
            date = date.replace(/[^\d\/]/g, '');
            if ( ! date ) {
                return 0;
            }

            var matches = date.match(/(\d{2})\/(\d{2})\/(\d{4})/);
            if (matches.length > 3) {
                var year = matches[3];
                var month = matches[2];
                var day = matches[1];
                return (year + month + day) * 1;
            } else {
                return 0;
            }
        },
        "customDateFormat-asc": function ( a, b ) {
            return ((a < b) ? -1 : ((a > b) ? 1 : 0));
        },

        "customDateFormat-desc": function ( a, b ) {
            return ((a < b) ? 1 : ((a > b) ? -1 : 0));
        }
    });

    $('#change-status-Modal').on('hide.bs.modal', function (event) {
        var statusModal = $(this);
        statusModal.find('.modal-body').html($modalBodyHTML);
        statusModal.find('.modal-title').text($modalTitleText);
        statusModal.find('.modal-header').removeClass('bg-gradient-success bg-gradient-warning')
            .addClass('bg-gradient-warning');
        statusModal.find('.resend-btn').show().off('click');
    });

    $('#change-status-Modal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('cadastroid');
        var token = button.data('tokenid');
        var recipient = button.data('cadastro');
        var statusModal = $(this);
        statusModal.find('.modal-body-placeholder').text(recipient);
        statusModal.find('.resend-btn').click(function() {
            statusModal.find('.modal-body').text($workinText);
            statusModal.find('.resend-btn').hide();
            statusModal.find('.cancel-btn').hide();
            $.post($statusChangePath.replace('_id', id), {_method: 'PUT', _token: token},
                function(response){
                    statusModal.find('.modal-header').removeClass('bg-gradient-warning')
                        .addClass('bg-gradient-' + response.status);
                    statusModal.find('.modal-title').text(response.title);
                    statusModal.find('.modal-body').text(response.message);
                    statusModal.find('.cancel-btn').text($closeText).show();
                    if (response.status == 'success') {
                        button.toggleClass("fa-plus-circle btn-success")
                            .toggleClass("fa-minus-circle btn-danger");
                        if (pag_table) {
                            pag_table.draw('full-hold');
                        } else {
                            $('span.cadastro[data-cadastroid="'+id+'"]')
                                .toggleClass("text-danger")
                                .text(function() {
                                    return ($(this).text() == $activeText ? $inactiveText : $activeText);
                                });
                        }

                    }
                },
                'json'
            ).fail(function(){
                    statusModal.find('.modal-header').removeClass('bg-gradient-danger')
                        .addClass('bg-gradient-danger');
                    statusModal.find('.modal-title').text($failTitle);
                    statusModal.find('.modal-body').text($failMessage);
                    statusModal.find('.cancel-btn').text($closeText).show();
                });
        });
    });


    $('#delete-Modal').on('hide.bs.modal', function (event) {
        var deleteModal = $(this);
        deleteModal.find('.modal-body').html($deleteModalBodyHTML);
        deleteModal.find('.modal-title').text($deleteModalTitleText);
        deleteModal.find('.modal-header').removeClass('bg-gradient-success bg-gradient-danger')
            .addClass('bg-gradient-danger');
        deleteModal.find('.delete-btn').show().off('click');
    });

    $('#delete-Modal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('cadastroid');
        var token = button.data('tokenid');
        var recipient = button.data('cadastro');
        var deleteModal = $(this);
        deleteModal.find('.modal-body-placeholder').text(recipient);
        deleteModal.find('.delete-btn').click(function() {
            deleteModal.find('.modal-body').text($workinText);
            deleteModal.find('.delete-btn').hide();
            deleteModal.find('.cancel-btn').hide();
            $.post($deletePath.replace('_id', id), {_method: 'DELETE', _token: token},
                function(response){
                    deleteModal.find('.modal-header').removeClass('bg-gradient-danger')
                        .addClass('bg-gradient-' + response.status);
                    deleteModal.find('.modal-title').text(response.title);
                    deleteModal.find('.modal-body').text(response.message);
                    deleteModal.find('.cancel-btn').text($closeText).show();
                    if (response.status == 'success') {
                        if (pag_table) {
                            pag_table.draw('full-hold');
                        } else {
                            $('span.cadastro[data-cadastroid="'+id+'"]')
                                .parents('tr').remove();
                        }

                    }
                },
                'json'
            ).fail(function(){
                    deleteModal.find('.modal-header').removeClass('bg-gradient-danger')
                        .addClass('bg-gradient-danger');
                    deleteModal.find('.modal-title').text($failTitle);
                    deleteModal.find('.modal-body').text($failMessage);
                    deleteModal.find('.cancel-btn').text($closeText).show();
                });
        });
    });

    jQuery('[data-input-mask="cnpj"]').mask("#0.000.000/0000-00",{placeholder: "__.___.___/____-__"});
    jQuery('[data-input-mask="cpf"]').mask("000.000.000-00",{placeholder: "___.___.___-__"});
    jQuery('[data-input-mask="cep"]').mask("00000-000", {placeholder: "_____-___"});
    jQuery('[data-input-mask="tel"]').mask('(00) 0000-00009', {placeholder: "(__) _____-____"});
    jQuery('[data-input-mask="int"]').mask("###########0", {reverse: true});
    jQuery('[data-input-mask="dvAgenciaBradesco"]').mask("X", {reverse: true,
            translation: {"X": {pattern: /[0-9P]/, optional: false, recursive: false}}
            }
        );
    jQuery('[data-input-mask="moeda"]').mask('#.##0,00', {reverse: true});
    jQuery('[data-input-mask="peso"]').mask('#.##0,000', {reverse: true});


    jQuery('[type="file"]').parents("form").on("submit", function(e){
        $('#MyProcessModal').modal('show');
    });

    pag_table = jQuery('[data-role="paginatedDataTable"]').DataTable({
        "dom": $domDTPaginated,
        "buttons": [
            {extend: "print", text: "<i class='fas fa-print'></i> Imprimir", 'page': 'all'},
            {extend: "copy", text: "<i class='fas fa-copy'></i> Copiar", 'page': 'all'},
            {extend: "excelHtml5", text: "<i class='fas fa-th-list'></i> Excel HTML5 Export"},
            {extend: "pdfHtml5", text: "<i class='fas fa-save'></i> Salvar PDF ", title: "Arquivo"}
        ],
        "processing": true,
        "searchDelay": 200,
        "language": dataTableTranslation,
        "serverSide": true,
        "createdRow": $fnCreatedRow,
        "rowCallback": $fnRowCallback,
        ajax: {
            url: $paginatedDataTablesSource,
            type: 'POST',
        },
        "columns": $paginatedDataTablesColumns,
        "initComplete": function(settings, json) {
            jQuery('.dataTables_filter input').unbind();
            jQuery('.dataTables_filter input').on('keyup', function (e) {
                // If the length is 4 or more characters, or the user pressed ENTER, search
                if (this.value.length > 3 || e.keyCode == 13 || e.which == 13) {
                    // Call the API search function
                    pag_table.search(this.value).draw();
                }
                // Ensure we clear the search if they backspace far enough
                if (this.value == "") {
                    pag_table.search("").draw();
                }

                return;
            });
        }
    });

    jQuery.typeahead({
        input: $typeaheadInput,
        minLength: 1,
        maxItem: 20,
        order: "asc",
        dynamic: true,
        delay: 500,
        backdrop: $typeaheadBackdrop,
        template: $typeaheadTemplate,
        emptyTemplate: $typeaheadEmptyTemplate,
        source: $typeaheadSource,
        callback: $typeaheadCallback,
        debug: false,
        accent: true
    });
});


