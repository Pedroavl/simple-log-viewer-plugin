<?php

$log_file =  SLV_PLUGIN_DIR . 'src/logs/logs-viewer.log';

if (!file_exists($log_file)) {
    $handle = fopen($log_file, 'w') or die('Não foi possível criar o arquivo de log: ' . $log_file);
    fclose($handle);
}