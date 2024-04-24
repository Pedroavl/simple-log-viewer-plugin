jQuery(document).ready(function ($) {
    function updateLogs() {
        var numLinhasSelecionado = $('#num_linhas_select').val();

        // Envia o número inicial de linhas se ainda não estiver definido
        if (!numLinhasSelecionado) {
            numLinhasSelecionado = 1000; // Ou qualquer valor padrão
        }

        $.ajax({
            url: ajax_object.rest_url + 'simplelogviewer/v1/errors', // Adiciona a parte específica do endpoint REST
            type: 'GET',
            data: { num_linhas: numLinhasSelecionado },
            success: function (results) {
                
                var logsWithLineBreaks = results.join('<br/><br/>');
                
                $('#slv-log-viewer').html(logsWithLineBreaks);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('Error: ' + errorThrown);
            }
        });
    }

    // Adiciona um evento de clique ao botão
    $('#check-logs-button').on('click', function () {

        var numLinhasSelecionado = $('#num_linhas_select').val();

        // Chama a função de verificação manual de logs quando o botão é clicado
        $.ajax({
            url: ajax_object.ajaxurl,
            type: 'POST',
            data: {
                action: 'slvpl_manual_log_check',
                num_linhas: numLinhasSelecionado,
                nonce: ajax_object.nonce
            },
            success: function (results) {

                var successMessage = results.data.message;

                // Atualiza os logs após a verificação manual
                var logsWithLineBreaks = results.data.logs.join('<br /><br />');
        
                $('#slv-log-viewer').html(logsWithLineBreaks);
    
                $('#slv-success-message').html(successMessage).delay(3000).fadeOut();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('Error: ' + errorThrown);
            }
        });
    }); 
});