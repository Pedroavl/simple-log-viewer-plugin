<?php
/*
Plugin Name: Simple Log Viewer
Description: A simple plugin to log errors in real time in a metabox in the admin panel.
Version: 1.0.3
Author: Pedro Avelar
Author URI: https://pedroavelar.com.br
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: simple-log-viewer
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Obtém o diretório de upload do WordPress
$upload_dir = wp_upload_dir();

// Constrói o caminho completo para o diretório de logs
$log_dir_path = $upload_dir['basedir'] . '/simple-log-viewer/logs/';

define('SLVPL_PLUGIN_PREFIX', 'slvpl_');
define('SLVPL_PLUGIN_VERSION', '1.0.3');
define('SLVPL_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define('SLVPL_PLUGIN_TEXT_DOMAIN', 'simple-log-viewer');
define('SLVPL_UPLOADS_LOGS_DIR', $log_dir_path);


if ( ! defined( 'SLVPL_PLUGIN_BASE' ) ) {
    define( 'SLVPL_PLUGIN_BASE', plugin_basename( __FILE__ ) );
}

// Carrega o domínio de texto do plugin
add_action('plugins_loaded', 'slvpl_load_textdomain');
function slvpl_load_textdomain() {
    load_plugin_textdomain(SLVPL_PLUGIN_TEXT_DOMAIN, false, dirname(SLVPL_PLUGIN_BASE) . '/languages/');
}


function slvpl_enqueue_scripts() {
    wp_enqueue_script( 'slvplajaxloader',  plugin_dir_url(__FILE__) . 'src/assets/js/index.js', array( 'jquery' ), '1.0', true);

    $rest_url = esc_url_raw( rest_url() );

    wp_localize_script( 'slvplajaxloader', 'ajax_object', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'rest_url' => $rest_url,
        'nonce' => wp_create_nonce('slvpl-nonce')
    ));
}
add_action('admin_enqueue_scripts', 'slvpl_enqueue_scripts');


require_once SLVPL_PLUGIN_DIR . 'src/simple-log-viewer-create-log-file.php';
require_once SLVPL_PLUGIN_DIR . 'src/simple-log-show-logs.php';
require_once SLVPL_PLUGIN_DIR . 'src/simple-log-show-metabox.php';

require_once SLVPL_PLUGIN_DIR . 'src/pages/simple-create-log-viewer-page.php';

require_once SLVPL_PLUGIN_DIR . 'src/logs/simple-logs-viewer-log.php';

require_once SLVPL_PLUGIN_DIR . 'src/settings/simple-log-viewer-settings-defs.php';
