<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    function slvpl_get_latest_errors($request) {
        $num_linhas = $request->get_param('num_linhas'); // Obtém o número de linhas da solicitação
        $log_file = SLVPL_UPLOADS_LOGS_DIR . 'logs-viewer.log';
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
            'callback' => 'slvpl_get_latest_errors',
            'permission_callback' => 'slvpl_check_logged_in_and_admin_user' // Verifica se o usuário está logado e é administrador
        ));
    });

    // Função de callback para verificar se o usuário está logado e é um administrador
    function slvpl_check_logged_in_and_admin_user() {
        return is_user_logged_in() && current_user_can('manage_options'); // Retorna true se o usuário estiver logado e for um administrador
    } 

    function slvpl_log_viewer_admin_styles() {
        wp_register_style('admin-css', false);
        wp_enqueue_style('admin-css');

        $custom_css = "
        #slv-log-viewer {
            overflow: auto;
            font-family: monospace;
            white-space: pre-wrap;
            font-size: 1rem;
        }
        #dashboard-widgets #slvpl_log_viewer_dashboard_widget {
            width: 100% !important;
            height: auto !important;
            color: red;
            font-weight: 600;
        }";
        wp_add_inline_style('admin-css', $custom_css);
    }
    add_action('admin_enqueue_scripts', 'slvpl_log_viewer_admin_styles');
    
    function slvpl_log_viewer_dashboard_widget_function() {
        if (!defined('WP_DEBUG') || WP_DEBUG === false) {
            echo '<div class="notice notice-warning settings-error is-dismissible"><p><strong>' . esc_html__('Ative o WP_DEBUG somente para fins de resolução de erros. O WP_DEBUG do WordPress está localizado no arquivo wp-config.php ative-o para ver os erros', 'simple-log-viewer') . '</strong>' . ' <a href="' . esc_url(admin_url('admin.php?page=slvpl-log-viewer-settings')) . '">' . '  ' . esc_html__('saiba mais', 'simple-log-viewer') .'</a>.</p></div>';
        }
    
        $num_linhas = isset($_POST['num_linhas']) ? absint($_POST['num_linhas']) : 1000;
        $num_linhas = in_array($num_linhas, array(1, 5, 250, 500, 1000, 1500)) ? $num_linhas : 1000;
    
        ?>
        <form method="post" action="">
            <label for="num_linhas_select" style="color: #000000!important"><?php echo esc_html__('Selecione o número de linhas:', 'simple-log-viewer'); ?></label><br/>
            <select style="width: 100%; margin-bottom: 12px;" id="num_linhas_select" name="num_linhas" onchange="this.form.submit()">
                <?php
                $options = array(1, 5, 250, 500, 1000, 1500);
                foreach ($options as $option) {
                    ?>
                    <option value="<?php echo esc_attr($option); ?> "<?php echo selected($num_linhas, $option, false); ?>><?php echo esc_html($option); ?></option>
                    <?php
                }
                ?>
            </select>
        </form>
    
        <?php
    
        // Exemplo de caminho do arquivo de log
        $log_file = SLVPL_UPLOADS_LOGS_DIR . 'logs-viewer.log';
    
        // Certifica de que o arquivo de log existe antes de tentar lê-lo
        if (file_exists($log_file)) {
            $log_content = file_get_contents($log_file);
    
            // Certifica de que o conteúdo do log está seguro antes de exibi-lo
            $log_content = esc_html($log_content);
    
            // Verifica se o log está vazio
            if (empty($log_content)) {
                ?>
                <p><?php echo esc_html__('Não há erros no log.', 'simple-log-viewer'); ?></p>
                <?php
            } else {
                // Exibe o conteúdo do log com base no número de linhas selecionado
                $logs = explode("\n", $log_content);
                $logs = array_filter($logs, function ($log) {
                    return trim($log) !== '';
                });
                $logs = array_slice($logs, -$num_linhas); // Corta os logs com base no número de linhas selecionado
                
                ?>

                <p id="slv-success-message" style="color: green;"></p>
                
                <div id="slv-log-viewer" style="margin-bottom: 20px;"><?php echo wp_kses_post(implode("<br /><br />", $logs)); ?></div>

                <?php
    
                submit_button(esc_html__('Verificar Logs', 'simple-log-viewer'), 'primary', 'check-logs-button', false);
            }
        } else {
            ?>
                <p><?php echo esc_html__('Arquivo de log não encontrado.', 'simple-log-viewer'); ?></p>
            <?php
        }
    }
    

    // Adiciona um endpoint para verificar os logs manualmente
    function slvpl_manual_log_check() {
        check_ajax_referer('slvpl-nonce', 'nonce');

        $num_linhas = isset($_POST['num_linhas']) ? absint($_POST['num_linhas']) : 1000; // Defina o número de linhas desejado

        // Obtém os logs manualmente
        $logs = slvpl_get_latest_errors_manual($num_linhas);

        // Exemplo: Retorna uma mensagem indicando que a verificação manual foi concluída
        $response = array('message' => __('Verificação manual de logs concluída.', 'simple-log-viewer'), 'logs' => $logs);

        wp_send_json_success($response);
    }
    add_action('wp_ajax_slvpl_manual_log_check', 'slvpl_manual_log_check');

    // Função para obter os logs manualmente
    function slvpl_get_latest_errors_manual($num_linhas) {
        $log_file = SLVPL_UPLOADS_LOGS_DIR . 'logs-viewer.log';
        $logs = file_get_contents($log_file);
        $logs = explode("\n", $logs);
        $logs = array_filter($logs, function ($log) {
            return trim($log) !== '';
        });
        $logs = array_slice($logs, -$num_linhas); // Corta os logs com base no número de linhas selecionado
        return $logs;
    }

