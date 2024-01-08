<?php
    function slv_log_viewer_error($message) {
        $log_file = SLV_PLUGIN_DIR . 'src/logs/logs-viewer.log';
        $message .= "\n";
        error_log($message, 3, $log_file);
    }

    function slv_log_viewer_error_handler($errno, $errstr, $errfile, $errline) {
        $message = "Erro: [$errno] $errstr - $errfile:$errline" . "\n";
        slv_log_viewer_error($message);
    }
    set_error_handler("slv_log_viewer_error_handler");
