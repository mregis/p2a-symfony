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

    // Paginated DataTables

    var pag_table = jQuery('[data-role="paginatedDataTable"]').DataTable({
                "language": dataTableTranslation,
                "serverSide": true,
                "createdRow": $fnCreatedRow,
                "rowCallback": $fnRowCallback,
                ajax: {
                    url: $paginatedDataTablesSource,
                    type: 'POST',
                },
                "columns": $paginatedDataTablesColumns
            });
});


