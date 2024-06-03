<?php

/**
 * Class LogModel
 * @package SLVPL\App\Models
 */

namespace SLVPL\App\Models;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class LogModel {

    private $log_dir;
    private $log_file;

    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->log_dir = $upload_dir['basedir'] . '/simple-log-viewer/logs/';
        $this->log_file = $this->log_dir . 'logs-viewer.log';
    }

    public function create_logs_directory() {
        if ( ! file_exists( $this->log_dir ) ) {
            if ( ! wp_mkdir_p( $this->log_dir ) ) {
                die( 'Unable to create logs folder: ' . $this->log_dir );
            }
        }
    }

    public function create_log_file() {
        if ( ! file_exists( $this->log_file ) ) {
            $handle = fopen( $this->log_file, 'w' ) or die( 'Unable to create log file: ' . $this->log_file );
            fclose( $handle );
        }
    }

    public function get_latest_errors($num_linhas) {
        if (!file_exists($this->log_file)) {
            return array();
        }

        $logs = file_get_contents($this->log_file);
        $logs = explode("\n", $logs);
        $logs = array_filter($logs, function ($log) {
            return trim($log) !== '';
        });
        return array_slice($logs, -$num_linhas);
    }

    public function get_log_content() {
        if (file_exists($this->log_file)) {
            return file_get_contents($this->log_file);
        }
        return '';
    }
}