<?php
/*
Plugin Name: Simple Log Viewer
Description: A simple plugin to log errors in real time in a metabox in the admin panel, too integrated with WP-CLI.
Version: 1.0.4
Author: Pedro Avelar
Author URI: https://pedroavelar.com.br
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: simple-log-viewer
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Define plugin constants
define('SLVPL_PLUGIN_VERSION', '1.0.3.2');
define('SLVPL_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define('SLVPL_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('SLVPL_PLUGIN_TEXT_DOMAIN', 'simple-log-viewer');

// Autoload classes
spl_autoload_register(function ($class_name) {
    $namespace_root = 'SLVPL\\';
    $namespace_prefix = 'SLVPL\\App\\';

    if (strpos($class_name, $namespace_root) === 0) {
        $relative_class = substr($class_name, strlen($namespace_root));
        $file_path = str_replace('\\', DIRECTORY_SEPARATOR, $relative_class);
        $file = SLVPL_PLUGIN_DIR . $file_path . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
});

class SLVPL_SimpleLogViewer {

    public function __construct() {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        $this->init_views();
        $this->init_controllers();
    }

    public function load_textdomain() {
        load_plugin_textdomain(SLVPL_PLUGIN_TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function enqueue_scripts() {
        wp_enqueue_script('slvplajaxloader', SLVPL_PLUGIN_URL . 'public/assets/js/index.js', ['jquery'], '1.0', true);
        $rest_url = esc_url_raw(rest_url());
        wp_localize_script('slvplajaxloader', 'ajax_object', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'rest_url' => $rest_url,
            'nonce' => wp_create_nonce('slvpl-nonce')
        ]);
    }

    private function init_controllers() {
        new \SLVPL\App\Controllers\LogController();
        new \SLVPL\App\Controllers\SettingsController();
        new \SLVPL\App\Controllers\MenuController();
        new \SLVPL\App\Controllers\WPCLIController();
    }

    private function init_views() {
        add_action('wp_dashboard_setup', function() {
            wp_add_dashboard_widget(
                'slvpl_log_viewer_dashboard_widget',
                __('Log Viewer', 'simple-log-viewer'),
                [\SLVPL\App\Views\LogView::class, 'display_dashboard_widget']
            );
        });
    }
}

new SLVPL_SimpleLogViewer();
