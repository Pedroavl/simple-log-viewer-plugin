<?php 
/*
Plugin Name: Pedro Avelar - Log Viewer
Description: Plugin para registrar erros em tempo real em uma metabox no painel administrativo.
Version: 0.0.1
Author: Pedro Avelar
Author URI: https://pedroavelar.com.br
*/

$log_file = plugin_dir_path( __FILE__ ) . 'logs-viewer.log';

if ( ! file_exists( $log_file ) ) {
    $handle = fopen( $log_file, 'w' ) or die( 'Não foi possível criar o arquivo de log: ' . $log_file );
    fclose($handle);
}

function pwp_log_viewer_dashboard_widget_function() {
    if ( ! defined( 'WP_DEBUG' ) || WP_DEBUG === false ) {
        echo '<div class="error fade"><p><strong>Caso esteja em ambiente de Produção é recomendado que mantenha o WP_DEBUG desativado. Em ambiente de testes ative o WP_DEBUG do WordPress localizado no arquivo wp-config.php para ver os erros</strong></p></div>';
        echo 'Ative o WP_DEBUG do WordPress localizado no arquivo wp-config.php para ver os erros';
        return;
    }

    $log_file = plugin_dir_path( __FILE__ ) . 'logs-viewer.log';
    $logs = file_get_contents( $log_file );

    // Adicione um ID ou classe ao elemento <pre> para estilizá-lo
    echo '<pre id="pwp-log-viewer">' . esc_html( $logs ) . '</pre>';
}

function pwp_log_viewer_admin_styles() {
    // Registra uma folha de estilo vazia
    wp_register_style( 'admin-css', false );
    wp_enqueue_style( 'admin-css' );

    // Adicione o CSS em linha
    $custom_css = "
        #pwp-log-viewer {
            overflow: auto;
            font-family: monospace;
            white-space: pre-wrap;
            font-size: 1rem;
        }
        #dashboard-widgets #pwp_log_viewer_dashboard_widget {
            width: 100% !important;
            height: auto !important;
            color: red;
            font-weight: 600;
        }";
    wp_add_inline_style( 'admin-css', $custom_css );
}
add_action( 'admin_enqueue_scripts', 'pwp_log_viewer_admin_styles' );

function pwp_log_viewer_metabox() {
    wp_add_dashboard_widget(
        'pwp_log_viewer_dashboard_widget', // Widget slug.
        'Logs de erros', // Título.
        'pwp_log_viewer_dashboard_widget_function' // Função de callback de exibição.
    );
}
add_action( 'wp_dashboard_setup', 'pwp_log_viewer_metabox' );

function pwp_log_viewer_error( $message ) {
    $log_file = plugin_dir_path( __FILE__ ) . 'logs-viewer.log';
    $message .= "\n"; // Adiciona uma quebra de linha ao final da mensagem
    error_log( $message, 3, $log_file );
}

function pwp_log_viewer_error_handler( $errno, $errstr, $errfile, $errline ) {
    $message = "Erro: [$errno] $errstr - $errfile:$errline" . "\n"; // Adiciona uma quebra de linha ao final da mensagem
    pwp_log_viewer_error( $message );
}
set_error_handler( "pwp_log_viewer_error_handler" );