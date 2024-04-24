<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function slvpl_log_viewer_metabox() {
    wp_add_dashboard_widget(
        'slvpl_log_viewer_dashboard_widget',
        __('Logs de erros WordPress', 'simple-log-viewer'),
        'slvpl_log_viewer_dashboard_widget_function',
    );
}
add_action('wp_dashboard_setup', 'slvpl_log_viewer_metabox');
