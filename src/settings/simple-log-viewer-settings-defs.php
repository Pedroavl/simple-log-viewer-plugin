<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Cria a página de configuração do plugin
function slvpl_log_viewer_settings_page() {
    if ( isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
        ?>
        <div id="message" class="updated notice is-dismissible"><p><?php echo esc_html(__('Alterações feitas com sucesso!', 'simple-log-viewer')); ?></p></div>
        <?php
    }
    ?>
    <div class="wrap">
        <h2><?php echo esc_html(__('Configurações do  Simple Log Viewer', 'simple-log-viewer')); ?></h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('slvpl-log-viewer-settings');
            do_settings_sections('slvpl-log-viewer-settings');
            submit_button();
            ?>
            <?php wp_nonce_field( 'slvpl_save_settings', 'slvpl_settings_nonce' ); ?>
        </form>
    </div>
    <?php
}

// Adiciona as opções de configuração
add_action('admin_init', 'slvpl_log_viewer_register_settings');

function slvpl_log_viewer_register_settings() {
    // Registra as configurações com sanitização
    register_setting('slvpl-log-viewer-settings', 'slvpl_log_viewer_clear_logs');
    add_settings_section('slvpl_log_viewer_main', __('Opções Principais', 'simple-log-viewer'), 'slvpl_log_viewer_main_section_cb', 'slvpl-log-viewer-settings');
    add_settings_field('slvpl_log_viewer_clear_logs', __('Limpar Arquivo de Logs', 'simple-log-viewer'), 'slvpl_log_viewer_clear_logs_field_cb', 'slvpl-log-viewer-settings', 'slvpl_log_viewer_main');
    add_settings_section('slvpl_log_viewer_debug', __('Depuração', 'simple-log-viewer'), 'slvpl_log_viewer_debug_section_cb', 'slvpl-log-viewer-settings');
    add_settings_field('slvpl_log_viewer_enable_debug', __('Ativar WP_DEBUG', 'simple-log-viewer'), 'slvpl_log_viewer_enable_debug_callback', 'slvpl-log-viewer-settings', 'slvpl_log_viewer_debug');
}

// Função de callback para a seção principal
function slvpl_log_viewer_main_section_cb() {
    ?>
        <p><?php echo esc_html__('Configure as opções principais do Simple Log Viewer.', 'simple-log-viewer'); ?></p>
    <?php
}

function slvpl_log_viewer_admin_notice() {
    //$clear_logs = get_option('slv_log_viewer_clear_logs');

    $log_file = SLVPL_UPLOADS_LOGS_DIR . 'logs-viewer.log';
    $log_content = file_get_contents($log_file);

    if (file_exists($log_file) && !empty($log_content)) {
        ?>
            <div class="notice notice-warning settings-error is-dismissible"><p><?php echo esc_html__('Ative a opção de limpeza de logs para gerar um novo arquivo de logs', 'simple-log-viewer'); ?> <a href="<?php echo esc_url(admin_url('admin.php?page=slvpl-log-viewer-settings')); ?>"> <?php echo esc_html__('saiba mais', 'simple-log-viewer'); ?></a>.</p></div>
        <?php
    }
}
add_action('admin_notices', 'slvpl_log_viewer_admin_notice');

// Função de callback para o campo "Limpar Arquivo de Logs"
function slvpl_log_viewer_clear_logs_field_cb() {
    ?>
    <label>
        <input type="submit" class="button button-primary" name="slvpl_log_viewer_clear_logs" value="<?php esc_attr_e('Limpar', 'simple-log-viewer'); ?>" />
    </label>
    <?php
}

function slvpl_log_viewer_shutdown_handler() {
    if (isset($_POST['slvpl_log_viewer_clear_logs'])) {
        // Recria o arquivo de logs se o botão for pressionado
        file_put_contents(SLVPL_UPLOADS_LOGS_DIR . 'logs-viewer.log', '');
    }
}
register_shutdown_function('slvpl_log_viewer_shutdown_handler');

function slvpl_enable_wp_debug() {
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
function slvpl_log_viewer_enable_debug_callback() {
    $is_debug_enabled = get_option('slvpl_enable_debug'); // Obtenha o estado salvo do checkbox
    ?>
    <input type="checkbox" name="slvpl_enable_debug" value="1" <?php checked( $is_debug_enabled, true ); ?>>
    <?php
}

// Função para desativar o WP_DEBUG
function slvpl_disable_wp_debug() {
    $wp_config_path = ABSPATH . 'wp-config.php'; // Caminho do arquivo wp-config.php

    if (file_exists($wp_config_path)) {
        $wp_config_content = file_get_contents($wp_config_path); // Obtém o conteúdo do arquivo wp-config.php

        // Remove a definição de WP_DEBUG
        $wp_config_content = preg_replace('/\bdefine\s*\(\s*[\'"]WP_DEBUG[\'"]\s*,\s*(true|false)\s*\);/', "define( 'WP_DEBUG', false );", $wp_config_content);

        // Remove a definição de WP_DEBUG_DISPLAY
        $wp_config_content = preg_replace('/\bdefine\s*\(\s*[\'"]WP_DEBUG_DISPLAY[\'"]\s*,\s*(true|false)\s*\);/', "", $wp_config_content);

        // Grava o conteúdo atualizado no arquivo wp-config.php
        file_put_contents($wp_config_path, $wp_config_content);
    }
}

// Função que verifica se o botão de salvar foi clicado e ativa o WP_DEBUG
function slvpl_log_viewer_save_settings() {
    if (isset($_POST['slvpl_settings_nonce']) && wp_verify_nonce( $_POST['slvpl_settings_nonce'], 'slvpl_save_settings' )) {
        $enable_debug = isset($_POST['slvpl_enable_debug']) ? true : false;
        update_option('slvpl_enable_debug', $enable_debug); // Salva o estado do checkbox na opção do WordPress

        if ($enable_debug && current_user_can('manage_options')) { // Verifica se o usuário tem a capacidade de gerenciar opções
            slvpl_enable_wp_debug();
        } else {
            // Desabilita o WP_DEBUG se o checkbox não estiver marcado
            slvpl_disable_wp_debug();
        }
    }
}

// Função de callback para a seção de depuração
function slvpl_log_viewer_debug_section_cb() {
    ?>
        <p><?php echo esc_html(__('Use esta seção para ativar o WP_DEBUG.', 'simple-log-viewer')); ?></p>
    <?php
}
    
// Callback para salvar as configurações
add_action('admin_init', 'slvpl_log_viewer_save_settings');
    
// Registrar configurações e campos de opções
add_action('admin_init', 'slvpl_log_viewer_register_settings');