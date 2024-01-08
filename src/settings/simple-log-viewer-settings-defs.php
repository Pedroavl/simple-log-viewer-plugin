<?php 
// Cria a página de configuração do plugin
function slv_log_viewer_settings_page() {
    ?>
    <div class="wrap">
        <h2><?php __('Configurações do  Simple Log Viewer', SLV_PLUGIN_TEXT_DOMAIN); ?></h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('slv-log-viewer-settings');
            do_settings_sections('slv-log-viewer-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Adiciona as opções de configuração
add_action('admin_init', 'slv_log_viewer_register_settings');

function slv_log_viewer_register_settings() {
    // Registra as configurações com sanitização
    register_setting('slv-log-viewer-settings', 'slv_log_viewer_clear_logs');
    add_settings_section('slv_log_viewer_main', __('Opções Principais', SLV_PLUGIN_TEXT_DOMAIN), 'slv_log_viewer_main_section_cb', 'slv-log-viewer-settings');
    add_settings_field('slv_log_viewer_clear_logs', __('Limpar Arquivo de Logs', SLV_PLUGIN_TEXT_DOMAIN), 'slv_log_viewer_clear_logs_field_cb', 'slv-log-viewer-settings', 'slv_log_viewer_main');
}

// Função de callback para a seção principal
function slv_log_viewer_main_section_cb() {
    echo '<p>' . esc_html__('Configure as opções principais do Simple Log Viewer.') . '</p>';
}

function slv_log_viewer_admin_notice() {
    //$clear_logs = get_option('slv_log_viewer_clear_logs');

    $error_message = '<div class="notice notice-warning settings-error is-dismissible"><p>' . esc_html__('Ative a opção de limpeza de logs para gerar um novo arquivo de logs', SLV_PLUGIN_TEXT_DOMAIN) . ' <a href="' . esc_url(admin_url('admin.php?page=slv-log-viewer-settings')) . '">' . esc_html__('saiba mais', SLV_PLUGIN_TEXT_DOMAIN) . '</a>.</p></div>';

    $log_file = SLV_PLUGIN_DIR . 'src/logs/logs-viewer.log';
    $log_content = file_get_contents($log_file);

    if (file_exists($log_file) && !empty($log_content)) {
        echo $error_message;
    }
}
add_action('admin_notices', 'slv_log_viewer_admin_notice');

// Função de callback para o campo "Limpar Arquivo de Logs"
function slv_log_viewer_clear_logs_field_cb() {
    $clear_logs = get_option('slv_log_viewer_clear_logs');

    ?>
    <label>
        <input type="checkbox" name="slv_log_viewer_clear_logs" <?php checked($clear_logs, 'on'); ?> />
        <?php __('Marque esta opção para limpar o arquivo de logs.', SLV_PLUGIN_TEXT_DOMAIN); ?>
    </label>
    <?php
}

function slv_log_viewer_shutdown_handler() {
    $clear_logs = get_option('slv_log_viewer_clear_logs', 'off');

    if ($clear_logs == 'on') {
        // Recria o arquivo de logs se a opção estiver marcada
        file_put_contents(SLV_PLUGIN_DIR . 'src/logs/logs-viewer.log', '');

        // Desmarca a opção após a recriação
        update_option('slv_log_viewer_clear_logs', 'off');
    }
}
register_shutdown_function('slv_log_viewer_shutdown_handler');