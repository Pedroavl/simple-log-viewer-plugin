<?php
/**
 * Class MenuController
 *
 * @package SLVPL\App\Controllers
 */

namespace SLVPL\App\Controllers;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class MenuController {

    public function __construct() {
        add_action('admin_menu', [$this, 'addMenuPage']);
    }

    public function addMenuPage() {
        add_menu_page(
            __('Simple Log Viewer Settings', 'simple-log-viewer'),
            'Simple Log Viewer',
            'manage_options',
            'slvpl-log-viewer-settings',
            [$this, 'renderSettingsPage'],
            'dashicons-welcome-write-blog'
        );
    }

    public function renderSettingsPage() {
        // Call static method SettingsController for render settings page
        SettingsController::renderSettingsPage();
    }
}
