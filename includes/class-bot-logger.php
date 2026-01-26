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

        $result = $wpdb->insert(
            $this->table_name,
            [
                'visit_date' => current_time('mysql'),
                'bot_name' => sanitize_text_field($bot_info['bot_name']),
                'bot_category' => sanitize_key($bot_info['bot_category']),
                'user_agent' => sanitize_text_field($bot_info['user_agent']),
                'ip_address' => sanitize_text_field($bot_info['ip_address']),
                'url_visited' => esc_url_raw($bot_info['url_visited']),
                'http_status' => http_response_code() ?: 200,
                'response_time' => $this->get_response_time(),
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%d', '%f']
        );

        if ($result === false) {
            return 0;
        }

        return $wpdb->insert_id;
    }

    private function get_response_time() {
        if (defined('WP_START_TIMESTAMP')) {
            return microtime(true) - WP_START_TIMESTAMP;
        }
        return 0;
    }

    private function build_where_clause($args) {
        $where = ['1=1'];
        $values = [];

        if (!empty($args['start_date'])) {
            $where[] = 'visit_date >= %s';
            $values[] = sanitize_text_field($args['start_date']) . ' 00:00:00';
        }

        if (!empty($args['end_date'])) {
            $where[] = 'visit_date <= %s';
            $values[] = sanitize_text_field($args['end_date']) . ' 23:59:59';
        }

        if (!empty($args['bot_name'])) {
            $where[] = 'bot_name = %s';
            $values[] = sanitize_text_field($args['bot_name']);
        }

        if (!empty($args['bot_category'])) {
            $where[] = 'bot_category = %s';
            $values[] = sanitize_key($args['bot_category']);
        }

        return [
            'clause' => implode(' AND ', $where),
            'values' => $values,
        ];
    }

    public function get_visits($args = []) {
        global $wpdb;

        $defaults = [
            'start_date' => gmdate('Y-m-d', strtotime('-30 days')),
            'end_date' => gmdate('Y-m-d'),
            'bot_name' => '',
            'bot_category' => '',
            'limit' => 100,
            'offset' => 0,
            'orderby' => 'visit_date',
            'order' => 'DESC',
        ];

        $args = wp_parse_args($args, $defaults);
        $args['limit'] = min(absint($args['limit']), 10000);
        $args['offset'] = absint($args['offset']);

        $where_data = $this->build_where_clause($args);

        $allowed_orderby = ['visit_date', 'bot_name', 'bot_category', 'url_visited'];
        $orderby = in_array($args['orderby'], $allowed_orderby, true) ? $args['orderby'] : 'visit_date';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        $table = esc_sql($this->table_name);
        $sql = "SELECT * FROM `$table` WHERE " . $where_data['clause'];
        $sql .= " ORDER BY `$orderby` $order";
        $sql .= ' LIMIT %d OFFSET %d';

        $values = $where_data['values'];
        $values[] = $args['limit'];
        $values[] = $args['offset'];

        return $wpdb->get_results($wpdb->prepare($sql, $values));
    }

    public function get_stats($start_date, $end_date) {
        global $wpdb;

        $start_date = sanitize_text_field($start_date);
        $end_date = sanitize_text_field($end_date);
        $table = esc_sql($this->table_name);

        $stats = [];

        $stats['total'] = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM `$table` WHERE visit_date BETWEEN %s AND %s",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ));

        $stats['by_category'] = $wpdb->get_results($wpdb->prepare(
            "SELECT bot_category, COUNT(*) as count 
             FROM `$table` 
             WHERE visit_date BETWEEN %s AND %s 
             GROUP BY bot_category 
             ORDER BY count DESC",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ), OBJECT_K);

        $stats['by_bot'] = $wpdb->get_results($wpdb->prepare(
            "SELECT bot_name, bot_category, COUNT(*) as count 
             FROM `$table` 
             WHERE visit_date BETWEEN %s AND %s 
             GROUP BY bot_name, bot_category 
             ORDER BY count DESC 
             LIMIT 20",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ));

        $stats['by_day'] = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(visit_date) as day, bot_category, COUNT(*) as count 
             FROM `$table` 
             WHERE visit_date BETWEEN %s AND %s 
             GROUP BY DATE(visit_date), bot_category 
             ORDER BY day ASC",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ));

        $stats['top_urls'] = $wpdb->get_results($wpdb->prepare(
            "SELECT url_visited, COUNT(*) as count 
             FROM `$table` 
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
        $table = esc_sql($this->table_name);

        return $wpdb->get_results(
            "SELECT 
                DATE_FORMAT(visit_date, '%Y-%m') as month_year,
                DATE_FORMAT(visit_date, '%M %Y') as label,
                COUNT(*) as count
             FROM `$table` 
             GROUP BY DATE_FORMAT(visit_date, '%Y-%m')
             ORDER BY month_year DESC"
        );
    }

    public function get_database_size() {
        global $wpdb;
        $table = esc_sql($this->table_name);

        $total_rows = (int) $wpdb->get_var("SELECT COUNT(*) FROM `$table`");

        $size_result = $wpdb->get_row($wpdb->prepare(
            "SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) as size_mb
             FROM information_schema.TABLES 
             WHERE table_schema = %s 
             AND table_name = %s",
            DB_NAME,
            $this->table_name
        ));

        return (object) [
            'total_rows' => $total_rows,
            'size_mb' => $size_result ? $size_result->size_mb : 0,
        ];
    }

    public function purge_month($year, $month) {
        global $wpdb;

        $year = absint($year);
        $month = str_pad(absint($month), 2, '0', STR_PAD_LEFT);

        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
            return 0;
        }

        $start_date = "$year-$month-01 00:00:00";
        $end_date = gmdate('Y-m-t 23:59:59', strtotime($start_date));
        $table = esc_sql($this->table_name);

        return $wpdb->query($wpdb->prepare(
            "DELETE FROM `$table` WHERE visit_date BETWEEN %s AND %s",
            $start_date,
            $end_date
        ));
    }

    public function get_count($args = []) {
        global $wpdb;

        $defaults = [
            'start_date' => '',
            'end_date' => '',
            'bot_name' => '',
            'bot_category' => '',
        ];

        $args = wp_parse_args($args, $defaults);
        $table = esc_sql($this->table_name);
        $where_data = $this->build_where_clause($args);

        $sql = "SELECT COUNT(*) FROM `$table` WHERE " . $where_data['clause'];

        if (!empty($where_data['values'])) {
            $sql = $wpdb->prepare($sql, $where_data['values']);
        }

        return (int) $wpdb->get_var($sql);
    }
}
