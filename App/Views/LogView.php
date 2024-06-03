<?php

/**
 * Class LogView
 * @package SLVPL\App\Views
 */

namespace SLVPL\App\Views;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class LogView {

    public static function display_dashboard_widget() {
        if (!defined('WP_DEBUG') || WP_DEBUG === false) {
            echo '<div class="notice notice-warning settings-error is-dismissible"><p><strong>' . esc_html__('Enable WP_DEBUG for error resolution purposes only. WordPress WP_DEBUG is located in the wp-config.php file, activate it to see errors', 'simple-log-viewer') . '</strong>' . ' <a href="' . esc_url(admin_url('admin.php?page=slvpl-log-viewer-settings')) . '">' . '  ' . esc_html__('know more', 'simple-log-viewer') .'</a>.</p></div>';
        }

        $num_linhas = isset($_POST['num_linhas']) ? (int) $_POST['num_linhas'] : 1000;
        $num_linhas = in_array($num_linhas, array(1, 5, 250, 500, 1000, 1500)) ? $num_linhas : 1000;
        ?>
        <form method="post" action="">
            <label for="num_linhas_select" style="color: #000000!important"><?php echo esc_html__('Select the number of lines:', 'simple-log-viewer'); ?></label><br/>
            <select style="width: 100%; margin-bottom: 12px;" id="num_linhas_select" name="num_linhas" onchange="this.form.submit()">
                <?php
                $options = array(1, 5, 250, 500, 1000, 1500);
                foreach ($options as $option) {
                    ?>
                    <option value="<?php echo esc_attr($option); ?> "<?php echo selected($num_linhas, $option, false); ?>><?php echo esc_html($option); ?></option>
                    <?php
                }
                ?>
            </select>
        </form>

        <?php
        $logModel = new \SLVPL\App\Models\LogModel();
        $log_content = $logModel->get_log_content();
        $log_content = esc_html($log_content);
        

        if (empty($log_content)) {
            ?>
            <p><?php echo esc_html__('There are no errors in the log.', 'simple-log-viewer'); ?></p>
            <?php
        } else {
            $logs = explode("\n", $log_content);
            $logs = array_filter($logs, function ($log) {
                return trim($log) !== '';
            });
            $logs = array_slice($logs, -$num_linhas);
            ?>

            <p id="slv-success-message" style="color: green;"></p>

            <div id="slv-log-viewer" style="margin-bottom: 20px;"><?php echo wp_kses_post(implode("<br /><br />", $logs)); ?></div>

            <?php
            submit_button(esc_html__('Check Logs', 'simple-log-viewer'), 'primary', 'check-logs-button', false);
        }
    }
}