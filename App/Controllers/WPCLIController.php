<?php
/**
 * Class WPCLIController
 *
 * @package SLVPL\App\Controllers
 * 
 * Display the latest errors from the log file.
*
* ## OPTIONS
*
* [--num_linhas=<num_linhas>]
* : The number of lines to display.
* ---
* default: 1000
* options:
*   - 1
*   - 5
*   - 250
*   - 500
*   - 1000
*
* ## EXAMPLES
*
*     wp slvpl logs-erros --num_linhas=100
* @when after_wp_load
*/

namespace SLVPL\App\Controllers;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

namespace SLVPL\App\Controllers;

use WP_CLI;

class WPCLIController {

    private $logModel;
    private $log_file;

    public function __construct() {
        require_once SLVPL_PLUGIN_DIR . 'App/Models/LogModel.php';
        $this->logModel = new \SLVPL\App\Models\LogModel();
        
        $upload_dir = wp_upload_dir();
        $this->log_file = $upload_dir['basedir'] . '/simple-log-viewer/logs/logs-viewer.log';

        if (defined('WP_CLI') && WP_CLI) {
            WP_CLI::add_command('slvpl logs-erros', [$this, 'analyze_logs']);
        }
    }

    public function analyze_logs($args, $assoc_args) {
        $num_linhas = isset($assoc_args['num_linhas']) ? intval($assoc_args['num_linhas']) : 1000;
        $num_linhas = min(max($num_linhas, 1), 1000); // Ensure it's between 1 and 1000

        $logs = $this->logModel->get_latest_errors($num_linhas);

        if (empty($logs)) {
            WP_CLI::success("No logs found.");
        } else {
            foreach ($logs as $log) {
                if (strpos($log, 'Fatal error') !== false) {
                    WP_CLI::warning($log);
                    WP_CLI::line("");
                } else {
                    WP_CLI::warning($log);
                    WP_CLI::line("");
                }
            }
        }
    }
}