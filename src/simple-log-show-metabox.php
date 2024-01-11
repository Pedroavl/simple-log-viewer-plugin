<?php
function slv_log_viewer_metabox() {
    wp_add_dashboard_widget(
        'slv_log_viewer_dashboard_widget',
        __('Logs de erros WordPress', SLV_PLUGIN_TEXT_DOMAIN),
        'slv_log_viewer_dashboard_widget_function',
    );
}
add_action('wp_dashboard_setup', 'slv_log_viewer_metabox');
