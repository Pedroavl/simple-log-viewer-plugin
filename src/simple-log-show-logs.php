<?php
    function slv_get_latest_errors($request) {
        $num_linhas = $request->get_param('num_linhas'); // Obtém o número de linhas da solicitação
        $log_file = SLV_PLUGIN_DIR . 'src/logs/logs-viewer.log';
        $logs = file_get_contents($log_file);
        $logs = explode("\n", $logs);
        $logs = array_filter($logs, function ($log) {
            return trim($log) !== '';
        });
        $logs = array_slice($logs, -$num_linhas); // Corta os logs com base no número de linhas selecionado
        return $logs;
    }

    // Adiciona endpoint REST para obter logs
    add_action('rest_api_init', function () {
        register_rest_route('simplelogviewer/v1', '/errors', array(
            'methods' => 'GET',
            'callback' => 'slv_get_latest_errors'
        ));
    });

    function slv_log_viewer_admin_styles() {
        wp_register_style('admin-css', false);
        wp_enqueue_style('admin-css');

        $custom_css = "
        #slv-log-viewer {
            overflow: auto;
            font-family: monospace;
            white-space: pre-wrap;
            font-size: 1rem;
        }
        #dashboard-widgets #slv_log_viewer_dashboard_widget {
            width: 100% !important;
            height: auto !important;
            color: red;
            font-weight: 600;
        }";
        wp_add_inline_style('admin-css', $custom_css);
    }
    add_action('admin_enqueue_scripts', 'slv_log_viewer_admin_styles');
    
    function slv_log_viewer_dashboard_widget_function() {
        if (!defined('WP_DEBUG') || WP_DEBUG === false) {
            $is_wp_debug_activate = '<div class="notice notice-warning settings-error is-dismissible"><p><strong>' . __('Ative o WP_DEBUG somente para fins de resolução de erros. O WP_DEBUG do WordPress está localizado no arquivo wp-config.php ative-o para ver os erros', SLV_PLUGIN_TEXT_DOMAIN) . '</strong>' . ' <a href="' . esc_url(admin_url('admin.php?page=slv-log-viewer-settings')) . '">' . esc_html__('saiba mais', SLV_PLUGIN_TEXT_DOMAIN) .'</a>.</p>';
            $is_wp_debug_activate .= '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . __('Fechar aviso', SLV_PLUGIN_TEXT_DOMAIN) . '</span></button></div>';
            echo $is_wp_debug_activate;
        }
    
        $num_linhas = isset($_POST['num_linhas']) ? absint($_POST['num_linhas']) : 1000;
        $num_linhas = in_array($num_linhas, array(1, 5, 250, 500, 1000, 1500)) ? $num_linhas : 1000;
    
        ?>
        <form method="post" action="">
            <label for="num_linhas_select" style="color: #000000!important"><?php print_r( __('Selecione o número de linhas:', SLV_PLUGIN_TEXT_DOMAIN));  ?></label><br/>
            <select style="width: 100%; margin-bottom: 12px;" id="num_linhas_select" name="num_linhas" onchange="this.form.submit()">
                <?php
                $options = array(1, 5, 250, 500, 1000, 1500);
                foreach ($options as $option) {
                    echo '<option value="' . esc_attr($option) . '"' . selected($num_linhas, $option, false) . '>' . esc_html($option) . '</option>';
                }
                ?>
            </select>
        </form>
    
        <?php
    
        // Exemplo de caminho do arquivo de log
        $log_file = SLV_PLUGIN_DIR . 'src/logs/logs-viewer.log';
    
        // Certifica de que o arquivo de log existe antes de tentar lê-lo
        if (file_exists($log_file)) {
            $log_content = file_get_contents($log_file);
    
            // Certifica de que o conteúdo do log está seguro antes de exibi-lo
            $log_content = esc_html($log_content);
    
            // Verifica se o log está vazio
            if (empty($log_content)) {
                echo '<p>' . __('Não há erros no log.', SLV_PLUGIN_TEXT_DOMAIN) . '</p>';
            } else {
                // Exibe o conteúdo do log com base no número de linhas selecionado
                $logs = explode("\n", $log_content);
                $logs = array_filter($logs, function ($log) {
                    return trim($log) !== '';
                });
                $logs = array_slice($logs, -$num_linhas); // Corta os logs com base no número de linhas selecionado

                $successMessage = '<p id="slv-success-message" style="color: green;"></p>';
                
                echo $successMessage;

                echo '<div id="slv-log-viewer" style="margin-bottom: 20px;">' . implode("<br /><br />", $logs) . '</div>';

                submit_button(__('Verificar Logs', SLV_PLUGIN_TEXT_DOMAIN), 'primary', 'check-logs-button', false);
            }
        } else {
            __('Arquivo de log não encontrado.', SLV_PLUGIN_TEXT_DOMAIN);
        }
    }
    
    // Adiciona scripts JavaScript para a atualização de logs em tempo real
    function slv_log_viewer_scripts() {
        ?>
        <script>
                jQuery(document).ready(function ($) {
                    function updateLogs() {
                        var numLinhasSelecionado = $('#num_linhas_select').val();

                        // Envia o número inicial de linhas se ainda não estiver definido
                        if (!numLinhasSelecionado) {
                            numLinhasSelecionado = 1000; // Ou qualquer valor padrão
                        }

                        $.ajax({
                            url: '<?php echo esc_url(rest_url('simplelogviewer/v1/errors')); ?>',
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
                            url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                            type: 'POST',
                            data: { 
                                action: 'slv_manual_log_check',
                                num_linhas: numLinhasSelecionado,
                                security: '<?php echo wp_create_nonce("slv-nonce"); ?>'
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
        </script>
        <?php
    }
    add_action('admin_footer', 'slv_log_viewer_scripts');

    // Adiciona um endpoint para verificar os logs manualmente
    function slv_manual_log_check() {
        check_ajax_referer('slv-nonce', 'security');

        $num_linhas = isset($_POST['num_linhas']) ? absint($_POST['num_linhas']) : 1000; // Defina o número de linhas desejado

        // Obtém os logs manualmente
        $logs = slv_get_latest_errors_manual($num_linhas);

        // Exemplo: Retorna uma mensagem indicando que a verificação manual foi concluída
        $response = array('message' => __('Verificação manual de logs concluída.', SLV_PLUGIN_TEXT_DOMAIN), 'logs' => $logs);

        wp_send_json_success($response);
    }
    add_action('wp_ajax_slv_manual_log_check', 'slv_manual_log_check');

    // Função para obter os logs manualmente
    function slv_get_latest_errors_manual($num_linhas) {
        $log_file = SLV_PLUGIN_DIR . 'src/logs/logs-viewer.log';
        $logs = file_get_contents($log_file);
        $logs = explode("\n", $logs);
        $logs = array_filter($logs, function ($log) {
            return trim($log) !== '';
        });
        $logs = array_slice($logs, -$num_linhas); // Corta os logs com base no número de linhas selecionado
        return $logs;
    }

