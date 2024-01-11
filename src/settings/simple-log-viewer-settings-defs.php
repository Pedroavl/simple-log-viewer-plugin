<?php 
// Cria a página de configuração do plugin
function slv_log_viewer_settings_page() {
    if ( isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
        echo '<div id="message" class="updated notice is-dismissible"><p>'.__('Alterações feitas com sucesso!', SLV_PLUGIN_TEXT_DOMAIN).'</p></div>';
    }
    ?>
    <div class="wrap">
        <h2><?php _e('Configurações do  Simple Log Viewer', SLV_PLUGIN_TEXT_DOMAIN); ?></h2>
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
    add_settings_section('slv_log_viewer_debug', __('Depuração', SLV_PLUGIN_TEXT_DOMAIN), 'slv_log_viewer_debug_section_cb', 'slv-log-viewer-settings');
    add_settings_field('slv_log_viewer_enable_debug', __('Ativar WP_DEBUG', SLV_PLUGIN_TEXT_DOMAIN), 'slv_log_viewer_enable_debug_callback', 'slv-log-viewer-settings', 'slv_log_viewer_debug');
}

// Função de callback para a seção principal
function slv_log_viewer_main_section_cb() {
    echo '<p>' . esc_html__('Configure as opções principais do Simple Log Viewer.', SLV_PLUGIN_TEXT_DOMAIN) . '</p>';
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
    ?>
    <label>
        <input type="submit" class="button button-primary" name="slv_log_viewer_clear_logs" value="<?php esc_attr_e('Limpar', SLV_PLUGIN_TEXT_DOMAIN); ?>" />
    </label>
    <?php
}

function slv_log_viewer_shutdown_handler() {
    if (isset($_POST['slv_log_viewer_clear_logs'])) {
        // Recria o arquivo de logs se o botão for pressionado
        file_put_contents(SLV_PLUGIN_DIR . 'src/logs/logs-viewer.log', '');
    }
}
register_shutdown_function('slv_log_viewer_shutdown_handler');

function slv_enable_wp_debug() {
    $wp_config_path = ABSPATH . 'wp-config.php'; // Caminho do arquivo wp-config.php
    
    if (file_exists($wp_config_path)) {
        $wp_config_content = file_get_contents($wp_config_path); // Obtém o conteúdo do arquivo wp-config.php
    
        // Verifica se a constante WP_DEBUG já está definida
        if (preg_match('/\bdefine\s*\(\s*[\'"]WP_DEBUG[\'"]\s*,\s*(true|false)\s*\);/', $wp_config_content, $matches)) {
            // A constante WP_DEBUG já está definida, vamos verificar se é definida como false e substituir para true
            if ($matches[1] === 'false') {
                $wp_config_content = preg_replace('/\bdefine\s*\(\s*[\'"]WP_DEBUG[\'"]\s*,\s*false\s*\);/', "define( 'WP_DEBUG', true );", $wp_config_content);
            }
        } else {
            // A constante WP_DEBUG não existe, vamos adicioná-la após a tag <?php apenas se não estiver presente
            $wp_config_content = preg_replace('/<\?php/', "<?php\ndefine( 'WP_DEBUG', true );", $wp_config_content, 1);
        }

        // Adiciona a constante WP_DEBUG_DISPLAY se não existir
        if (!preg_match('/\bdefine\s*\(\s*[\'"]WP_DEBUG_DISPLAY[\'"]/', $wp_config_content)) {
            $wp_config_content = preg_replace('/<\?php/', "<?php\ndefine( 'WP_DEBUG_DISPLAY', false );", $wp_config_content, 1);
        }

        // Adiciona a constante WP_DISABLE_FATAL_ERROR_HANDLER se não existir
        if (!preg_match('/\bdefine\s*\(\s*[\'"]WP_DISABLE_FATAL_ERROR_HANDLER[\'"]/', $wp_config_content)) {
            $wp_config_content = preg_replace('/<\?php/', "<?php\ndefine( 'WP_DISABLE_FATAL_ERROR_HANDLER', true );", $wp_config_content, 1);
        }
    
        // Grava o conteúdo atualizado no arquivo wp-config.php
        file_put_contents($wp_config_path, $wp_config_content);
    }
}


// Função para exibir campo de configuração no painel de administração para ativar o WP_DEBUG
function slv_log_viewer_enable_debug_callback() {
    $is_debug_enabled = defined('WP_DEBUG') && WP_DEBUG;
    echo '<input type="checkbox" name="slv_enable_debug" value="1" ' . checked( $is_debug_enabled, true, false ) . '>';
}

// Função que verifica se o botão de salvar foi clicado e ativa o WP_DEBUG
function slv_log_viewer_save_settings() {
    if (isset($_POST['slv_enable_debug'])) {
        slv_enable_wp_debug();
    }
}

// Função de callback para a seção de depuração
function slv_log_viewer_debug_section_cb() {
    echo '<p>' . __('Use esta seção para ativar o WP_DEBUG.', SLV_PLUGIN_TEXT_DOMAIN) . '</p>';
}
    
// Callback para salvar as configurações
add_action('admin_init', 'slv_log_viewer_save_settings');
    
// Registrar configurações e campos de opções
add_action('admin_init', 'slv_log_viewer_register_settings');