<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Define o caminho para a pasta de logs dentro da pasta de uploads
$log_dir = wp_upload_dir()['basedir'] . '/simple-log-viewer/logs/';

// Verifica se a pasta de logs existe, caso contrário, tenta criá-la
if ( ! file_exists( $log_dir ) ) {
    // Tenta criar a pasta de logs
    if ( ! wp_mkdir_p( $log_dir ) ) {
        die( 'Não foi possível criar a pasta de logs: ' . $log_dir );
    }
}

// Define o caminho completo para o arquivo de log
$log_file =  $log_dir . 'logs-viewer.log';

// Se o arquivo de log não existir, tenta criá-lo
if ( ! file_exists( $log_file ) ) {
    $handle = fopen( $log_file, 'w' ) or die( 'Não foi possível criar o arquivo de log: ' . $log_file );
    fclose( $handle );
}