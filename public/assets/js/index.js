jQuery(document).ready(function ($) {
    function updateLogs() {
        var numLinhasSelecionado = $('#num_linhas_select').val();

        // Send the initial number lines if not have defined
        if (!numLinhasSelecionado) {
            numLinhasSelecionado = 1000; // default value
        }

        $.ajax({
            url: ajax_object.rest_url + 'simplelogviewer/v1/errors', // REST endpoint
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

    // Add a click button event
    $('#check-logs-button').on('click', function () {

        var numLinhasSelecionado = $('#num_linhas_select').val();

        // Tell the function check manual logs when the clicked button
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

                // Update logs after the manual check
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