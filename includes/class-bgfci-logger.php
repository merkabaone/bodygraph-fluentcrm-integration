<?php
/**
 * BGFCI_Logger - Modular logger for BodyGraph FluentCRM Integration
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class BGFCI_Logger {
    /**
     * Log a message to the plugin log file (or error_log fallback)
     *
     * @param string $message
     * @param string $level info|debug|warning|error
     */
    public static function log($message, $level = 'info') {
        $allowed_levels = ['info', 'debug', 'warning', 'error'];
        $level = strtolower($level);
        if (!in_array($level, $allowed_levels, true)) {
            $level = 'info';
        }
        $debug_enabled = (bool) get_option('bgfci_debug_logs', 0);
        if (!$debug_enabled && $level !== 'info') {
            return;
        }
        $log_dir = plugin_dir_path(__DIR__ . '/../bodygraph-fluentcrm-integration.php');
        $log_file = $log_dir . 'bgfci.log';
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[$timestamp][" . strtoupper($level) . "] $message\n";
        $written = false;
        if (is_writable($log_dir) || (!file_exists($log_file) && is_writable($log_dir))) {
            $fp = @fopen($log_file, 'a');
            if ($fp) {
                if (flock($fp, LOCK_EX)) {
                    fwrite($fp, $entry);
                    flock($fp, LOCK_UN);
                    $written = true;
                }
                fclose($fp);
            }
        }
        if (!$written) {
            error_log('[BGFCI][' . strtoupper($level) . "] $message");
        }
    }
}
