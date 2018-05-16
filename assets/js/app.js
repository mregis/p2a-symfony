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
        var modal = $(this);
        modal.find('.modal-body').html($modalBodyHTML);
        modal.find('.modal-title').text($modalTitleText);
        modal.find('.modal-header').removeClass('bg-gradient-success bg-gradient-danger')
            .addClass('bg-gradient-warning');
        modal.find('.resend-btn').show();
    });

    $('#change-status-Modal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('cadastroid');
        var token = button.data('tokenid');
        var recipient = button.data('cadastro');
        var modal = $(this);
        modal.find('.modal-body-placeholder').text(recipient);
        modal.find('.resend-btn').click(function() {
            modal.find('.modal-body').text($workinText);
            modal.find('.resend-btn').hide();
            modal.find('.cancel-btn').hide();
            $.post($statusChangePath.replace('_id', id), {_method: 'PUT', _token: token},
                function(response){
                    modal.find('.modal-header').removeClass('bg-gradient-warning')
                        .addClass('bg-gradient-' + response.status);
                    modal.find('.modal-title').text(response.title);
                    modal.find('.modal-body').text(response.message);
                    modal.find('.cancel-btn').text($closeText).show();
                    if (response.status == 'success') {
                        button.toggleClass("fa-plus-circle btn-success")
                            .toggleClass("fa-minus-circle btn-danger");
                        $('span.cadastro[data-cadastroid="'+id+'"]')
                            .toggleClass("text-danger")
//                            .toggleClass("text-success")
                            .text(function() {
                                return ($(this).text() == $activeText ? $inactiveText : $activeText);
                            });
                    }
                },
                'json'
            );
        });
    });

    $(".cnpj").mask("#0.000.000/0000-00",{placeholder: "__.___.___/____-__"});
    $(".cpf").mask("000.000.000-00",{placeholder: "___.___.___-__"});
    $(".cep").mask("00000-000", {placeholder: "_____-___"});
    $(".tel").mask('(00) 0000-00009', {placeholder: "(__) _____-____"});
    $(".int").mask("###########0", {reverse: true});
});


