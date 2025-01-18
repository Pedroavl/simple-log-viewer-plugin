<?php

namespace SLVPL\App\Controllers;

use SLVPL\App\Models\LogModel; // Import the LogModel class

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class MenuController {
    public function __construct() {
        add_action('admin_menu', [$this, 'addMenuPage']);
    }

    public function addMenuPage() {
        // Main Menu
        add_menu_page(
            __('Simple Log Viewer Settings', 'simple-log-viewer'),
            'Simple Log Viewer',
            'manage_options',
            'slvpl-log-viewer-settings',
            [$this, 'renderSettingsPage'],
            'dashicons-welcome-write-blog'
        );
    
        // Submenu: Error Logs
        add_submenu_page(
            'slvpl-log-viewer-settings',
            __('Error Logs', 'simple-log-viewer'),
            'Error Logs',
            'manage_options',
            'slvpl-error-logs',
            [$this, 'renderErrorLogsPage']
        );
    
        // Submenu: Error Analysis
        add_submenu_page(
            'slvpl-log-viewer-settings',
            __('Error Analysis', 'simple-log-viewer'),
            'Error Analysis',
            'manage_options',
            'slvpl-error-analysis',
            [$this, 'renderErrorAnalysisPage']
        );
    }
    

    public function renderSettingsPage() {
        // Call static method SettingsController for render settings page
        SettingsController::renderSettingsPage();
    }

    public function renderErrorLogsPage() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Error Logs', 'simple-log-viewer') . '</h1>';
    
        // Create an instance of the LogModel class
        $logModel = new \SLVPL\App\Models\LogModel();
    
        // Fetch the latest 100 logs
        $logs = $logModel->get_latest_errors(100);
    
        if (!empty($logs)) {
            $groupedBySource = [];
    
            // Process each log
            foreach ($logs as $log) {
                if (preg_match('/Erro: \[(.*?)\] (.*?) - (.*?):(\d+)/', $log, $matches)) {
                    $errorType = $matches[1];
                    $errorMessage = $matches[2];
                    $filePath = $matches[3];
                    $lineNumber = $matches[4];
    
                    // Determine the source (plugin or theme)
                    if (strpos($filePath, '/wp-content/plugins/') !== false) {
                        preg_match('/\/wp-content\/plugins\/([^\/]+)/', $filePath, $sourceMatches);
                        $source = 'Plugin: ' . $sourceMatches[1];
                    } elseif (strpos($filePath, '/wp-content/themes/') !== false) {
                        preg_match('/\/wp-content\/themes\/([^\/]+)/', $filePath, $sourceMatches);
                        $source = 'Theme: ' . $sourceMatches[1];
                    } else {
                        $source = 'Core/Other';
                    }
    
                    // Group by source
                    if (!isset($groupedBySource[$source])) {
                        $groupedBySource[$source] = [];
                    }
    
                    $key = $errorType . '|' . $filePath . ':' . $lineNumber;
                    if (!isset($groupedBySource[$source][$key])) {
                        $groupedBySource[$source][$key] = [
                            'type' => $errorType,
                            'message' => $errorMessage,
                            'file' => $filePath,
                            'line' => $lineNumber,
                            'count' => 0,
                        ];
                    }
                    $groupedBySource[$source][$key]['count']++;
                }
            }
    
            // Display grouped errors
            foreach ($groupedBySource as $source => $errors) {
                echo '<h2>' . esc_html($source) . '</h2>';
                echo '<table style="width: 100%; border-collapse: collapse;">';
                echo '<thead>';
                echo '<tr>';
                echo '<th style="border: 1px solid #ddd; padding: 8px;">Error Type</th>';
                echo '<th style="border: 1px solid #ddd; padding: 8px;">Message</th>';
                echo '<th style="border: 1px solid #ddd; padding: 8px;">File</th>';
                echo '<th style="border: 1px solid #ddd; padding: 8px;">Line</th>';
                echo '<th style="border: 1px solid #ddd; padding: 8px;">Count</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
    
                foreach ($errors as $error) {
                    echo '<tr>';
                    echo '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($error['type']) . '</td>';
                    echo '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($error['message']) . '</td>';
                    echo '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($error['file']) . '</td>';
                    echo '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($error['line']) . '</td>';
                    echo '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($error['count']) . '</td>';
                    echo '</tr>';
                }
    
                echo '</tbody>';
                echo '</table>';
            }
        } else {
            echo '<p>' . esc_html__('No logs found.', 'simple-log-viewer') . '</p>';
        }
    
        echo '</div>';
    }

    public function renderErrorAnalysisPage() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Error Analysis', 'simple-log-viewer') . '</h1>';
    
        // Create an instance of the LogModel class
        $logModel = new \SLVPL\App\Models\LogModel();
    
        // Fetch the latest 500 logs for analysis
        $logs = $logModel->get_latest_errors(500);
    
        if (!empty($logs)) {
            $detailedStats = [];
            $errorDistribution = [];
            $timePeriods = ['Last Week' => 7, 'Last Month' => 30, 'Last Year' => 365];
            $errorDates = [];
    
            // Process each log
            foreach ($logs as $log) {
                if (preg_match('/Erro: \[(.*?)\] (.*?) - (.*?):(\d+)/', $log, $matches)) {
                    $errorType = $matches[1];
                    $errorMessage = $matches[2];
                    $filePath = $matches[3];
                    $errorTime = date('Y-m-d'); // Assuming logs have a timestamp; replace with actual parsing if available.
    
                    // Determine the source
                    $source = 'Core/Other';
                    if (strpos($filePath, '/wp-content/plugins/') !== false) {
                        preg_match('/\/wp-content\/plugins\/([^\/]+)/', $filePath, $sourceMatches);
                        $source = 'Plugin: ' . $sourceMatches[1];
                    } elseif (strpos($filePath, '/wp-content/themes/') !== false) {
                        preg_match('/\/wp-content\/themes\/([^\/]+)/', $filePath, $sourceMatches);
                        $source = 'Theme: ' . $sourceMatches[1];
                    }
    
                    // Initialize source stats
                    if (!isset($detailedStats[$source])) {
                        $detailedStats[$source] = [
                            'errors' => [],
                            'firstOccurrence' => $errorTime,
                            'lastOccurrence' => $errorTime,
                            'total' => 0,
                        ];
                    }
    
                    // Track error details
                    $key = $errorType . '|' . $errorMessage;
                    if (!isset($detailedStats[$source]['errors'][$key])) {
                        $detailedStats[$source]['errors'][$key] = [
                            'type' => $errorType,
                            'message' => $errorMessage,
                            'count' => 0,
                        ];
                    }
                    $detailedStats[$source]['errors'][$key]['count']++;
                    $detailedStats[$source]['total']++;
                    $detailedStats[$source]['lastOccurrence'] = $errorTime;
    
                    // Count error distribution
                    $errorDistribution[$source] = ($errorDistribution[$source] ?? 0) + 1;
    
                    // Track errors by date
                    if (!isset($errorDates[$errorTime])) {
                        $errorDates[$errorTime] = 0;
                    }
                    $errorDates[$errorTime]++;
                }
            }
    
            // Calculate total errors for percentages
            $totalErrors = array_sum($errorDistribution);
    
            // Display error distribution and percentages
            echo '<h2>' . esc_html__('Error Distribution and Statistics', 'simple-log-viewer') . '</h2>';
            echo '<ul>';
            foreach ($errorDistribution as $source => $count) {
                $percentage = ($count / $totalErrors) * 100;
                echo '<li>' . esc_html($source) . ': ' . esc_html($count) . ' errors (' . number_format($percentage, 2) . '%)</li>';
            }
            echo '</ul>';
    
            // Display stats by time periods
            echo '<h3>' . esc_html__('Error Statistics by Time', 'simple-log-viewer') . '</h3>';
            echo '<ul>';
            foreach ($timePeriods as $label => $days) {
                $count = 0;
                foreach ($logs as $log) {
                    $logDate = date('Y-m-d'); // Replace with actual log date parsing
                    if (strtotime($logDate) >= strtotime('-' . $days . ' days')) {
                        $count++;
                    }
                }
                echo '<li>' . esc_html($label) . ': ' . esc_html($count) . ' errors</li>';
            }
            echo '</ul>';
    
            // Display errors by week (date grouping)
            echo '<h3>' . esc_html__('Errors by Week', 'simple-log-viewer') . '</h3>';
            ksort($errorDates);
            echo '<ul>';
            foreach ($errorDates as $date => $count) {
                echo '<li>' . esc_html($date) . ': ' . esc_html($count) . ' errors</li>';
            }
            echo '</ul>';
    
            // Display detailed stats
            foreach ($detailedStats as $source => $data) {
                echo '<h3>' . esc_html($source) . '</h3>';
                echo '<p>';
                echo esc_html__('First Occurrence:', 'simple-log-viewer') . ' ' . esc_html($data['firstOccurrence']) . '<br>';
                echo esc_html__('Last Occurrence:', 'simple-log-viewer') . ' ' . esc_html($data['lastOccurrence']) . '<br>';
                echo esc_html__('Total Errors:', 'simple-log-viewer') . ' ' . esc_html($data['total']);
                echo '</p>';
    
                // List individual errors for this source
                echo '<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">';
                echo '<thead>';
                echo '<tr>';
                echo '<th style="border: 1px solid #ddd; padding: 8px;">Error Type</th>';
                echo '<th style="border: 1px solid #ddd; padding: 8px;">Message</th>';
                echo '<th style="border: 1px solid #ddd; padding: 8px;">Count</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
    
                foreach ($data['errors'] as $error) {
                    echo '<tr>';
                    echo '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($error['type']) . '</td>';
                    echo '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($error['message']) . '</td>';
                    echo '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($error['count']) . '</td>';
                    echo '</tr>';
                }
    
                echo '</tbody>';
                echo '</table>';
            }
        } else {
            echo '<p>' . esc_html__('No logs found.', 'simple-log-viewer') . '</p>';
        }
    
        echo '</div>';
    }
     
     

    
    
}