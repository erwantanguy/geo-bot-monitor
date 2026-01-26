<?php
/**
 * Plugin Name: GEO Bot Monitor
 * Description: Surveillance des visites de robots SEO et GEO/AI avec exports et comparaison de périodes
 * Version: 1.0.1
 * Author: Erwan Tanguy
 * Text Domain: geo-bot-monitor
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('GEO_BOT_MONITOR_VERSION', '1.0.1');
define('GEO_BOT_MONITOR_PATH', plugin_dir_path(__FILE__));
define('GEO_BOT_MONITOR_URL', plugin_dir_url(__FILE__));

require_once GEO_BOT_MONITOR_PATH . 'includes/bot-signatures.php';
require_once GEO_BOT_MONITOR_PATH . 'includes/class-bot-detector.php';
require_once GEO_BOT_MONITOR_PATH . 'includes/class-bot-logger.php';
require_once GEO_BOT_MONITOR_PATH . 'includes/class-bot-dashboard.php';
require_once GEO_BOT_MONITOR_PATH . 'includes/class-bot-exporter.php';
require_once GEO_BOT_MONITOR_PATH . 'includes/class-bot-api.php';
require_once GEO_BOT_MONITOR_PATH . 'includes/class-bot-settings.php';

register_activation_hook(__FILE__, 'geo_bot_monitor_activate');
register_deactivation_hook(__FILE__, 'geo_bot_monitor_deactivate');
register_uninstall_hook(__FILE__, 'geo_bot_monitor_uninstall');

function geo_bot_monitor_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'geo_bot_visits';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        visit_date DATETIME NOT NULL,
        bot_name VARCHAR(100) NOT NULL,
        bot_category VARCHAR(20) NOT NULL,
        user_agent TEXT,
        ip_address VARCHAR(45),
        url_visited TEXT,
        http_status SMALLINT DEFAULT 200,
        response_time FLOAT DEFAULT 0,
        INDEX idx_date (visit_date),
        INDEX idx_bot (bot_name),
        INDEX idx_category (bot_category),
        INDEX idx_date_category (visit_date, bot_category)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    add_option('geo_bot_monitor_db_version', GEO_BOT_MONITOR_VERSION);
}

function geo_bot_monitor_deactivate() {
}

function geo_bot_monitor_uninstall() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'geo_bot_visits';
    $wpdb->query("DROP TABLE IF EXISTS `$table_name`");
    delete_option('geo_bot_monitor_db_version');
    delete_option('geo_bot_monitor_api_key');
}

new GEO_Bot_API();
new GEO_Bot_Settings();

add_action('init', function() {
    if (is_admin()) {
        return;
    }

    $detector = new GEO_Bot_Detector();
    $bot_info = $detector->detect();
    
    if ($bot_info) {
        $logger = new GEO_Bot_Logger();
        $logger->log($bot_info);
    }
});

add_action('admin_menu', function() {
    add_menu_page(
        __('Bot Monitor', 'geo-bot-monitor'),
        __('Bot Monitor', 'geo-bot-monitor'),
        'manage_options',
        'geo-bot-monitor',
        'geo_bot_render_dashboard',
        'dashicons-visibility',
        30
    );

    add_submenu_page(
        'geo-bot-monitor',
        __('Tableau de bord', 'geo-bot-monitor'),
        __('Tableau de bord', 'geo-bot-monitor'),
        'manage_options',
        'geo-bot-monitor',
        'geo_bot_render_dashboard'
    );

    add_submenu_page(
        'geo-bot-monitor',
        __('Comparer les périodes', 'geo-bot-monitor'),
        __('Comparer', 'geo-bot-monitor'),
        'manage_options',
        'geo-bot-compare',
        'geo_bot_render_compare'
    );

    add_submenu_page(
        'geo-bot-monitor',
        __('Exporter', 'geo-bot-monitor'),
        __('Exporter', 'geo-bot-monitor'),
        'manage_options',
        'geo-bot-export',
        'geo_bot_render_export'
    );

    add_submenu_page(
        'geo-bot-monitor',
        __('Maintenance', 'geo-bot-monitor'),
        __('Maintenance', 'geo-bot-monitor'),
        'manage_options',
        'geo-bot-maintenance',
        'geo_bot_render_maintenance'
    );

    add_submenu_page(
        'geo-bot-monitor',
        __('Paramètres API', 'geo-bot-monitor'),
        __('API', 'geo-bot-monitor'),
        'manage_options',
        'geo-bot-settings',
        'geo_bot_render_settings'
    );
});

add_action('admin_enqueue_scripts', function($hook) {
    if (strpos($hook, 'geo-bot') === false) {
        return;
    }

    wp_enqueue_style(
        'geo-bot-admin',
        GEO_BOT_MONITOR_URL . 'assets/css/admin.css',
        [],
        GEO_BOT_MONITOR_VERSION
    );

    wp_enqueue_script(
        'chart-js',
        'https://cdn.jsdelivr.net/npm/chart.js',
        [],
        '4.4.1',
        true
    );

    wp_enqueue_script(
        'geo-bot-admin',
        GEO_BOT_MONITOR_URL . 'assets/js/admin.js',
        ['chart-js', 'jquery'],
        GEO_BOT_MONITOR_VERSION,
        true
    );
});

function geo_bot_render_dashboard() {
    $dashboard = new GEO_Bot_Dashboard();
    $dashboard->render();
}

function geo_bot_render_compare() {
    $dashboard = new GEO_Bot_Dashboard();
    $dashboard->render_compare();
}

function geo_bot_render_export() {
    $exporter = new GEO_Bot_Exporter();
    $exporter->render_page();
}

function geo_bot_render_maintenance() {
    $dashboard = new GEO_Bot_Dashboard();
    $dashboard->render_maintenance();
}

function geo_bot_render_settings() {
    $settings = new GEO_Bot_Settings();
    $settings->render();
}

add_action('wp_ajax_geo_bot_export', function() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Accès refusé', 'geo-bot-monitor'));
    }

    check_ajax_referer('geo_bot_export', 'nonce');

    $exporter = new GEO_Bot_Exporter();
    $allowed_formats = ['csv', 'pdf', 'markdown'];
    $format = isset($_POST['format']) ? sanitize_text_field(wp_unslash($_POST['format'])) : 'csv';
    
    if (!in_array($format, $allowed_formats, true)) {
        $format = 'csv';
    }

    $month = isset($_POST['month']) ? sanitize_text_field(wp_unslash($_POST['month'])) : '';
    $year = isset($_POST['year']) ? absint($_POST['year']) : (int) gmdate('Y');

    $exporter->export($format, $month, $year);
});

add_action('wp_ajax_geo_bot_purge', function() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Accès refusé', 'geo-bot-monitor'));
    }

    check_ajax_referer('geo_bot_purge', 'nonce');

    $months = isset($_POST['months']) ? array_map('sanitize_text_field', wp_unslash($_POST['months'])) : [];
    
    if (empty($months)) {
        wp_send_json_error(['message' => __('Aucun mois sélectionné', 'geo-bot-monitor')]);
    }

    $logger = new GEO_Bot_Logger();
    $deleted = 0;

    foreach ($months as $month_year) {
        if (!preg_match('/^\d{4}-\d{2}$/', $month_year)) {
            continue;
        }

        $parts = explode('-', $month_year);
        if (count($parts) !== 2) {
            continue;
        }

        $year = absint($parts[0]);
        $month = absint($parts[1]);

        $deleted += $logger->purge_month($year, $month);
    }

    wp_send_json_success([
        'message' => sprintf(
            /* translators: %d: number of deleted records */
            __('%d enregistrements supprimés', 'geo-bot-monitor'),
            $deleted
        ),
        'deleted' => $deleted,
    ]);
});

add_action('wp_ajax_geo_bot_get_comparison', function() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Accès refusé', 'geo-bot-monitor'));
    }

    check_ajax_referer('geo_bot_compare', 'nonce');

    $period1_start = isset($_POST['period1_start']) ? sanitize_text_field(wp_unslash($_POST['period1_start'])) : '';
    $period1_end = isset($_POST['period1_end']) ? sanitize_text_field(wp_unslash($_POST['period1_end'])) : '';
    $period2_start = isset($_POST['period2_start']) ? sanitize_text_field(wp_unslash($_POST['period2_start'])) : '';
    $period2_end = isset($_POST['period2_end']) ? sanitize_text_field(wp_unslash($_POST['period2_end'])) : '';

    $dashboard = new GEO_Bot_Dashboard();
    $comparison = $dashboard->get_comparison_data($period1_start, $period1_end, $period2_start, $period2_end);

    wp_send_json_success($comparison);
});

add_action('wp_ajax_geo_bot_generate_api_key', function() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Accès refusé', 'geo-bot-monitor'));
    }

    check_ajax_referer('geo_bot_generate_key', 'nonce');

    $new_key = 'gbm_' . wp_generate_password(48, false, false);
    
    wp_send_json_success(['key' => $new_key]);
});
