<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    // Adiciona uma página de configuração do plugin
    add_action('admin_menu', 'slvpl_log_viewer_plugin_menu');

    function slvpl_log_viewer_plugin_menu() {
        add_menu_page(
            __('Configurações do Simple Log Viewer', 'simple-log-viewer'),
            'Simple Log Viewer',
            'manage_options',
            'slvpl-log-viewer-settings',
            'slvpl_log_viewer_settings_page',
            'dashicons-welcome-write-blog'
        );
    }