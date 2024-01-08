<?php
/*
Plugin Name: Simple Log Viewer
Description: Um simples plugin para registrar erros em tempo real em uma metabox no painel administrativo.
Version: 1.0.0
Author: Pedro Avelar
Author URI: https://pedroavelar.com.br
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: simple-log-viewer
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define('SLV_PLUGIN_PREFIX', 'slv_');
define('SLV_PLUGIN_VERSION', '1.0.0');
define('SLV_PLUGIN_DIR', plugin_dir_path( dirname(__FILE__) ) . 'simple-log-viewer/');
define('SLV_PLUGIN_TEXT_DOMAIN', 'simple-log-viewer');

if ( ! defined( 'SLV_PLUGIN_BASE' ) ) {
    define( 'SLV_PLUGIN_BASE', plugin_basename( __FILE__ ) );
}

// Carrega o domínio de texto do plugin
add_action('plugins_loaded', 'slv_load_textdomain');
function slv_load_textdomain() {
    load_plugin_textdomain(SLV_PLUGIN_TEXT_DOMAIN, false, dirname(SLV_PLUGIN_BASE) . '/languages/');
}

require_once SLV_PLUGIN_DIR . 'src/simple-log-viewer-create-log-file.php';
require_once SLV_PLUGIN_DIR . 'src/simple-log-show-logs.php';
require_once SLV_PLUGIN_DIR . 'src/simple-log-show-metabox.php';

require_once SLV_PLUGIN_DIR . 'src/pages/simple-create-log-viewer-page.php';

require_once SLV_PLUGIN_DIR . 'src/logs/simple-logs-viewer-log.php';

require_once SLV_PLUGIN_DIR . 'src/settings/simple-log-viewer-settings-defs.php';
