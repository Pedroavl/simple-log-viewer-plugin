<?php
/**
 * Class LogController
 *
 * @package SLVPL\App\Controllers
 */

namespace SLVPL\App\Controllers;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class LogController {

    private $logModel;
    private $log_file;

    public function __construct() {
        require_once SLVPL_PLUGIN_DIR . 'App/Models/LogModel.php';
        require_once SLVPL_PLUGIN_DIR . 'App/Views/LogView.php';

        $this->logModel = new \SLVPL\App\Models\LogModel();

        $upload_dir = wp_upload_dir();
        $this->log_file = $upload_dir['basedir'] . '/simple-log-viewer/logs/logs-viewer.log';

        add_action('rest_api_init', [$this, 'register_rest_routes']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('wp_ajax_slvpl_manual_log_check', [$this, 'manual_log_check']);
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);

        $this->initialize_logs();
        
        set_error_handler([$this, 'errorHandler']);
    }

    private function initialize_logs() {
        $this->logModel->create_logs_directory();
        $this->logModel->create_log_file();
    }

    public function register_rest_routes() {
        register_rest_route('simplelogviewer/v1', '/errors', array(
            'methods' => 'GET',
            'callback' => [$this, 'get_latest_errors'],
            'permission_callback' => [$this, 'check_logged_in_and_admin_user']
        ));
    }

    public function get_latest_errors($request) {
        $num_linhas = $request->get_param('num_linhas');
        return $this->logModel->get_latest_errors($num_linhas);
    }

    public function check_logged_in_and_admin_user() {
        return is_user_logged_in() && current_user_can('manage_options');
    }

    public function enqueue_styles() {
        wp_register_style('admin-css', false);
        wp_enqueue_style('admin-css');
        $custom_css = "
        #slv-log-viewer {
            overflow: auto;
            font-family: monospace;
            white-space: pre-wrap;
            font-size: 1rem;
        }
        #dashboard-widgets #slvpl_log_viewer_dashboard_widget {
            width: 100% !important;
            height: auto !important;
            color: red;
            font-weight: 600;
        }";
        wp_add_inline_style('admin-css', $custom_css);
    }

    public function manual_log_check() {
        check_ajax_referer('slvpl-nonce', 'nonce');
        $num_linhas = isset($_POST['num_linhas']) ? absint($_POST['num_linhas']) : 1000;
        $logs = $this->logModel->get_latest_errors($num_linhas);
        $response = array('message' => __('Manual log check completed.', 'simple-log-viewer'), 'logs' => $logs);
        wp_send_json_success($response);
    }

    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'slvpl_log_viewer_dashboard_widget',
            __('WordPress error logs', 'simple-log-viewer'),
            [SLVPL\App\Views\LogView::class, 'display_dashboard_widget']
        );
    }

    public function logError($message) {
        $message .= "\n";
        error_log($message, 3, $this->log_file);
    }

    public function errorHandler($errno, $errstr, $errfile, $errline) {
        $message = "Erro: [$errno] $errstr - $errfile:$errline\n";
        $this->logError($message);
    }
}
