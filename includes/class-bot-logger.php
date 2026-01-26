<?php
if (!defined('ABSPATH')) {
    exit;
}

class GEO_Bot_Logger {

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'geo_bot_visits';
    }

    public function log($bot_info) {
        global $wpdb;

        $wpdb->insert(
            $this->table_name,
            [
                'visit_date' => current_time('mysql'),
                'bot_name' => $bot_info['bot_name'],
                'bot_category' => $bot_info['bot_category'],
                'user_agent' => $bot_info['user_agent'],
                'ip_address' => $bot_info['ip_address'],
                'url_visited' => $bot_info['url_visited'],
                'http_status' => http_response_code() ?: 200,
                'response_time' => $this->get_response_time(),
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%d', '%f']
        );

        return $wpdb->insert_id;
    }

    private function get_response_time() {
        if (defined('WP_START_TIMESTAMP')) {
            return microtime(true) - WP_START_TIMESTAMP;
        }
        return 0;
    }

    public function get_visits($args = []) {
        global $wpdb;

        $defaults = [
            'start_date' => date('Y-m-d', strtotime('-30 days')),
            'end_date' => date('Y-m-d'),
            'bot_name' => '',
            'bot_category' => '',
            'limit' => 100,
            'offset' => 0,
            'orderby' => 'visit_date',
            'order' => 'DESC',
        ];

        $args = wp_parse_args($args, $defaults);

        $where = ['1=1'];
        $values = [];

        if ($args['start_date']) {
            $where[] = 'visit_date >= %s';
            $values[] = $args['start_date'] . ' 00:00:00';
        }

        if ($args['end_date']) {
            $where[] = 'visit_date <= %s';
            $values[] = $args['end_date'] . ' 23:59:59';
        }

        if ($args['bot_name']) {
            $where[] = 'bot_name = %s';
            $values[] = $args['bot_name'];
        }

        if ($args['bot_category']) {
            $where[] = 'bot_category = %s';
            $values[] = $args['bot_category'];
        }

        $allowed_orderby = ['visit_date', 'bot_name', 'bot_category', 'url_visited'];
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'visit_date';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT * FROM {$this->table_name} WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY $orderby $order";
        $sql .= " LIMIT %d OFFSET %d";

        $values[] = $args['limit'];
        $values[] = $args['offset'];

        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }

        return $wpdb->get_results($sql);
    }

    public function get_stats($start_date, $end_date) {
        global $wpdb;

        $stats = [];

        $stats['total'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE visit_date BETWEEN %s AND %s",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ));

        $stats['by_category'] = $wpdb->get_results($wpdb->prepare(
            "SELECT bot_category, COUNT(*) as count 
             FROM {$this->table_name} 
             WHERE visit_date BETWEEN %s AND %s 
             GROUP BY bot_category 
             ORDER BY count DESC",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ), OBJECT_K);

        $stats['by_bot'] = $wpdb->get_results($wpdb->prepare(
            "SELECT bot_name, bot_category, COUNT(*) as count 
             FROM {$this->table_name} 
             WHERE visit_date BETWEEN %s AND %s 
             GROUP BY bot_name, bot_category 
             ORDER BY count DESC 
             LIMIT 20",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ));

        $stats['by_day'] = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(visit_date) as day, bot_category, COUNT(*) as count 
             FROM {$this->table_name} 
             WHERE visit_date BETWEEN %s AND %s 
             GROUP BY DATE(visit_date), bot_category 
             ORDER BY day ASC",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ));

        $stats['top_urls'] = $wpdb->get_results($wpdb->prepare(
            "SELECT url_visited, COUNT(*) as count 
             FROM {$this->table_name} 
             WHERE visit_date BETWEEN %s AND %s 
             GROUP BY url_visited 
             ORDER BY count DESC 
             LIMIT 10",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ));

        return $stats;
    }

    public function get_available_months() {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT 
                DATE_FORMAT(visit_date, '%Y-%m') as month_year,
                DATE_FORMAT(visit_date, '%M %Y') as label,
                COUNT(*) as count
             FROM {$this->table_name} 
             GROUP BY DATE_FORMAT(visit_date, '%Y-%m')
             ORDER BY month_year DESC"
        );
    }

    public function get_database_size() {
        global $wpdb;

        $size = $wpdb->get_row(
            "SELECT 
                COUNT(*) as total_rows,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) as size_mb
             FROM information_schema.TABLES 
             WHERE table_schema = DATABASE() 
             AND table_name = '{$this->table_name}'"
        );

        return $size;
    }

    public function purge_month($year, $month) {
        global $wpdb;

        $start_date = "$year-$month-01 00:00:00";
        $end_date = date('Y-m-t 23:59:59', strtotime($start_date));

        return $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE visit_date BETWEEN %s AND %s",
            $start_date,
            $end_date
        ));
    }
}
