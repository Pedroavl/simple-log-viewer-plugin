<?php
    // Adiciona uma página de configuração do plugin
    add_action('admin_menu', 'slv_log_viewer_plugin_menu');

    function slv_log_viewer_plugin_menu() {
        add_menu_page(
            __('Configurações do Simple Log Viewer', SLV_PLUGIN_TEXT_DOMAIN),
            'Simple Log Viewer',
            'manage_options',
            'slv-log-viewer-settings',
            'slv_log_viewer_settings_page',
            'dashicons-welcome-write-blog'
        );
    }