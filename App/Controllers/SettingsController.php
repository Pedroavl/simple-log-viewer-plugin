<?php
/**
 * Class SettingsController
 *
 * @package SLVPL\App\Controllers
 */

namespace SLVPL\App\Controllers;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class SettingsController {

    private $log_dir;
    private $log_file;
    
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->log_dir = $upload_dir['basedir'] . '/simple-log-viewer/logs/';
        $this->log_file = $this->log_dir . 'logs-viewer.log';

        $this->logModel = new \SLVPL\App\Models\LogModel();

        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_notices', [$this, 'adminNotice']);
        add_action('admin_init', [$this, 'saveSettings']);
    }

    public function registerSettings() {
        register_setting('slvpl-log-viewer-settings', 'slvpl_log_viewer_clear_logs');
        add_settings_section('slvpl_log_viewer_main', __('Main Options', 'simple-log-viewer'), [$this, 'mainSectionCallback'], 'slvpl-log-viewer-settings');
        add_settings_field('slvpl_log_viewer_clear_logs', __('Clear Log File', 'simple-log-viewer'), [$this, 'clearLogsFieldCallback'], 'slvpl-log-viewer-settings', 'slvpl_log_viewer_main');
        add_settings_section('slvpl_log_viewer_debug', __('Debug', 'simple-log-viewer'), [$this, 'debugSectionCallback'], 'slvpl-log-viewer-settings');
        add_settings_field('slvpl_log_viewer_enable_debug', __('Enable WP_DEBUG', 'simple-log-viewer'), [$this, 'enableDebugCallback'], 'slvpl-log-viewer-settings', 'slvpl_log_viewer_debug');
    }

    public function mainSectionCallback() {
        echo '<p>' . esc_html__('Configure the main options of Simple Log Viewer.', 'simple-log-viewer') . '</p>';
    }

    public function debugSectionCallback() {
        echo '<p>' . esc_html__('Use this section to enable WP_DEBUG.', 'simple-log-viewer') . '</p>';
    }

    public function clearLogsFieldCallback() {
        echo '<label><input type="submit" class="button button-primary" name="slvpl_log_viewer_clear_logs" value="' . esc_attr__('To clean', 'simple-log-viewer') . '" /></label>';
    }

    public function enableDebugCallback() {
        $is_debug_enabled = get_option('slvpl_enable_debug');
        echo '<input type="checkbox" name="slvpl_enable_debug" value="1" ' . checked($is_debug_enabled, true, false) . '>';
    }

    public function adminNotice() {
        echo '<div class="notice notice-warning is-dismissible is-dismissible"><p><strong>' . esc_html__('For the use WP-CLI, is necessary the run command wp slvpl logs-erros [--num_linhas=<num_linhas>] - (Simple Log Viewer)', 'simple-log-viewer') .'</strong></p></div>';
        if (file_exists($this->log_file) && !empty(file_get_contents($this->log_file))) {
            echo '<div class="notice notice-warning settings-error is-dismissible"><p>' . esc_html__('Enable the log cleaning option to generate a new log file', 'simple-log-viewer') . ' <a href="' . esc_url(admin_url('admin.php?page=slvpl-log-viewer-settings')) . '"> ' . esc_html__('know more', 'simple-log-viewer') . '</a>.</p></div>';
        }
    }

    public function saveSettings() {
        if (isset($_POST['slvpl_settings_nonce']) && wp_verify_nonce($_POST['slvpl_settings_nonce'], 'slvpl_save_settings')) {
            $enable_debug = isset($_POST['slvpl_enable_debug']) ? true : false;
            update_option('slvpl_enable_debug', $enable_debug);

            if ($enable_debug && current_user_can('manage_options')) {
                $this->enableWpDebug();
            } else {
                $this->disableWpDebug();
            }

            if (isset($_POST['slvpl_log_viewer_clear_logs'])) {
                $this->clearLogFiles();
            }
        }
    }

    private function clearLogFiles() {
        $log_file = $this->log_dir . 'logs-viewer.log';
        if (file_exists($log_file)) {
            unlink($log_file);
        }
        // Regenerate the log file
        $this->logModel->create_log_file();
    }

    public function enableWpDebug() {
        $wp_config_path = ABSPATH . 'wp-config.php';

        if (file_exists($wp_config_path)) {
            $wp_config_content = file_get_contents($wp_config_path);

            if (preg_match('/\bdefine\s*\(\s*[\'"]WP_DEBUG[\'"]\s*,\s*(true|false)\s*\);/', $wp_config_content, $matches)) {
                if ($matches[1] === 'false') {
                    $wp_config_content = preg_replace('/\bdefine\s*\(\s*[\'"]WP_DEBUG[\'"]\s*,\s*false\s*\);/', "define( 'WP_DEBUG', true );", $wp_config_content);
                }
            } else {
                $wp_config_content = preg_replace('/<\?php/', "<?php\ndefine( 'WP_DEBUG', true );", $wp_config_content, 1);
            }

            if (!preg_match('/\bdefine\s*\(\s*[\'"]WP_DEBUG_DISPLAY[\'"]/', $wp_config_content)) {
                $wp_config_content = preg_replace('/<\?php/', "<?php\ndefine( 'WP_DEBUG_DISPLAY', false );", $wp_config_content, 1);
            }

            if (!preg_match('/\bdefine\s*\(\s*[\'"]WP_DISABLE_FATAL_ERROR_HANDLER[\'"]/', $wp_config_content)) {
                $wp_config_content = preg_replace('/<\?php/', "<?php\ndefine( 'WP_DISABLE_FATAL_ERROR_HANDLER', true );", $wp_config_content, 1);
            }

            file_put_contents($wp_config_path, $wp_config_content);
        }
    }

    public function disableWpDebug() {
        $wp_config_path = ABSPATH . 'wp-config.php';

        if (file_exists($wp_config_path)) {
            $wp_config_content = file_get_contents($wp_config_path);

            $wp_config_content = preg_replace('/\bdefine\s*\(\s*[\'"]WP_DEBUG[\'"]\s*,\s*(true|false)\s*\);/', "define( 'WP_DEBUG', false );", $wp_config_content);
            $wp_config_content = preg_replace('/\bdefine\s*\(\s*[\'"]WP_DEBUG_DISPLAY[\'"]\s*,\s*(true|false)\s*\);/', "", $wp_config_content);

            file_put_contents($wp_config_path, $wp_config_content);
        }
    }

    public static function renderSettingsPage() {
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
            echo '<div id="message" class="updated notice is-dismissible"><p>' . esc_html(__('Changes made successfully!', 'simple-log-viewer')) . '</p></div>';
        }
        ?>
        <div class="wrap">
            <h2><?php echo esc_html(__('Simple Log Viewer Settings', 'simple-log-viewer')); ?></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('slvpl-log-viewer-settings');
                do_settings_sections('slvpl-log-viewer-settings');
                submit_button();
                wp_nonce_field('slvpl_save_settings', 'slvpl_settings_nonce');
                ?>
            </form>
        </div>
        <?php
    }
}