<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    function slvpl_log_viewer_error($message) {
        $log_file = SLVPL_UPLOADS_LOGS_DIR . 'logs-viewer.log';
        $message .= "\n";
        error_log($message, 3, $log_file);
    }

    function slvpl_log_viewer_error_handler($errno, $errstr, $errfile, $errline) {
        $message = "Erro: [$errno] $errstr - $errfile:$errline" . "\n";
        slvpl_log_viewer_error($message);
    }
    set_error_handler("slvpl_log_viewer_error_handler");
