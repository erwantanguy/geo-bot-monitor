<?php
if (!defined('ABSPATH')) {
    exit;
}

class GEO_Bot_API {

    private $namespace = 'geo-bot-monitor/v1';
    private $logger;

    public function __construct() {
        $this->logger = new GEO_Bot_Logger();
        add_action('rest_api_init', [$this, 'register_routes']);
        add_action('rest_api_init', [$this, 'add_cors_headers']);
    }

    public function add_cors_headers() {
        remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
        add_filter('rest_pre_serve_request', function($value) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Authorization, Content-Type, X-GEO-Bot-API-Key');
            header('Access-Control-Allow-Credentials: true');
            return $value;
        });
    }

    public function register_routes() {
        register_rest_route($this->namespace, '/stats', [
            'methods' => 'GET',
            'callback' => [$this, 'get_stats'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'start_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d', strtotime('-30 days')),
                ],
                'end_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d'),
                ],
            ],
        ]);

        register_rest_route($this->namespace, '/visits', [
            'methods' => 'GET',
            'callback' => [$this, 'get_visits'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'start_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d', strtotime('-30 days')),
                ],
                'end_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'default' => date('Y-m-d'),
                ],
                'bot_name' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'bot_category' => [
                    'type' => 'string',
                    'enum' => ['', 'seo', 'geo_ai', 'social', 'other'],
                    'default' => '',
                ],
                'page' => [
                    'type' => 'integer',
                    'default' => 1,
                    'minimum' => 1,
                ],
                'per_page' => [
                    'type' => 'integer',
                    'default' => 100,
                    'minimum' => 1,
                    'maximum' => 1000,
                ],
            ],
        ]);

        register_rest_route($this->namespace, '/compare', [
            'methods' => 'GET',
            'callback' => [$this, 'get_comparison'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'period1_start' => [
                    'type' => 'string',
                    'format' => 'date',
                    'required' => true,
                ],
                'period1_end' => [
                    'type' => 'string',
                    'format' => 'date',
                    'required' => true,
                ],
                'period2_start' => [
                    'type' => 'string',
                    'format' => 'date',
                    'required' => true,
                ],
                'period2_end' => [
                    'type' => 'string',
                    'format' => 'date',
                    'required' => true,
                ],
            ],
        ]);

        register_rest_route($this->namespace, '/bots', [
            'methods' => 'GET',
            'callback' => [$this, 'get_bots_list'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route($this->namespace, '/categories', [
            'methods' => 'GET',
            'callback' => [$this, 'get_categories'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route($this->namespace, '/database', [
            'methods' => 'GET',
            'callback' => [$this, 'get_database_info'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route($this->namespace, '/months', [
            'methods' => 'GET',
            'callback' => [$this, 'get_available_months'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route($this->namespace, '/export', [
            'methods' => 'GET',
            'callback' => [$this, 'export_data'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'format' => [
                    'type' => 'string',
                    'enum' => ['json', 'csv'],
                    'default' => 'json',
                ],
                'start_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'required' => true,
                ],
                'end_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'required' => true,
                ],
            ],
        ]);

        register_rest_route($this->namespace, '/ping', [
            'methods' => 'GET',
            'callback' => [$this, 'ping'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route($this->namespace, '/auth', [
            'methods' => 'POST',
            'callback' => [$this, 'authenticate'],
            'permission_callback' => '__return_true',
            'args' => [
                'api_key' => [
                    'type' => 'string',
                    'required' => true,
                ],
            ],
        ]);
    }

    public function check_permission($request) {
        $api_key = $request->get_header('X-GEO-Bot-API-Key');
        
        if (!$api_key) {
            $api_key = $request->get_param('api_key');
        }

        $stored_key = get_option('geo_bot_monitor_api_key');

        if (!$stored_key) {
            return new WP_Error(
                'api_not_configured',
                __('API key not configured. Please set up the API key in Bot Monitor settings.', 'geo-bot-monitor'),
                ['status' => 503]
            );
        }

        if (!$api_key || !hash_equals($stored_key, $api_key)) {
            return new WP_Error(
                'unauthorized',
                __('Invalid or missing API key', 'geo-bot-monitor'),
                ['status' => 401]
            );
        }

        return true;
    }

    public function ping($request) {
        return rest_ensure_response([
            'status' => 'ok',
            'plugin' => 'GEO Bot Monitor',
            'version' => GEO_BOT_MONITOR_VERSION,
            'site' => get_bloginfo('name'),
            'url' => home_url(),
            'timezone' => wp_timezone_string(),
            'timestamp' => current_time('c'),
        ]);
    }

    public function authenticate($request) {
        $api_key = $request->get_param('api_key');
        $stored_key = get_option('geo_bot_monitor_api_key');

        if (!$stored_key) {
            return new WP_Error(
                'api_not_configured',
                __('API key not configured', 'geo-bot-monitor'),
                ['status' => 503]
            );
        }

        if (hash_equals($stored_key, $api_key)) {
            return rest_ensure_response([
                'authenticated' => true,
                'site' => get_bloginfo('name'),
                'url' => home_url(),
            ]);
        }

        return new WP_Error(
            'invalid_key',
            __('Invalid API key', 'geo-bot-monitor'),
            ['status' => 401]
        );
    }

    public function get_stats($request) {
        $start_date = $request->get_param('start_date');
        $end_date = $request->get_param('end_date');

        $stats = $this->logger->get_stats($start_date, $end_date);
        $category_labels = geo_bot_get_category_labels();

        $by_category = [];
        foreach ($stats['by_category'] as $cat => $data) {
            $by_category[] = [
                'category' => $cat,
                'label' => $category_labels[$cat] ?? $cat,
                'count' => (int) $data->count,
            ];
        }

        $by_bot = [];
        foreach ($stats['by_bot'] as $bot) {
            $by_bot[] = [
                'bot_name' => $bot->bot_name,
                'category' => $bot->bot_category,
                'category_label' => $category_labels[$bot->bot_category] ?? $bot->bot_category,
                'count' => (int) $bot->count,
            ];
        }

        $by_day = [];
        foreach ($stats['by_day'] as $day) {
            $by_day[] = [
                'date' => $day->day,
                'category' => $day->bot_category,
                'count' => (int) $day->count,
            ];
        }

        $top_urls = [];
        foreach ($stats['top_urls'] as $url) {
            $top_urls[] = [
                'url' => $url->url_visited,
                'count' => (int) $url->count,
            ];
        }

        return rest_ensure_response([
            'period' => [
                'start' => $start_date,
                'end' => $end_date,
            ],
            'total_visits' => (int) $stats['total'],
            'by_category' => $by_category,
            'by_bot' => $by_bot,
            'by_day' => $by_day,
            'top_urls' => $top_urls,
        ]);
    }

    public function get_visits($request) {
        $page = $request->get_param('page');
        $per_page = $request->get_param('per_page');
        $offset = ($page - 1) * $per_page;

        $args = [
            'start_date' => $request->get_param('start_date'),
            'end_date' => $request->get_param('end_date'),
            'bot_name' => $request->get_param('bot_name'),
            'bot_category' => $request->get_param('bot_category'),
            'limit' => $per_page,
            'offset' => $offset,
        ];

        $visits = $this->logger->get_visits($args);

        global $wpdb;
        $table_name = $wpdb->prefix . 'geo_bot_visits';
        
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

        $count_sql = "SELECT COUNT(*) FROM $table_name WHERE " . implode(' AND ', $where);
        if (!empty($values)) {
            $count_sql = $wpdb->prepare($count_sql, $values);
        }
        $total = (int) $wpdb->get_var($count_sql);

        $formatted_visits = [];
        foreach ($visits as $visit) {
            $formatted_visits[] = [
                'id' => (int) $visit->id,
                'date' => $visit->visit_date,
                'bot_name' => $visit->bot_name,
                'bot_category' => $visit->bot_category,
                'url' => $visit->url_visited,
                'ip' => $visit->ip_address,
                'http_status' => (int) $visit->http_status,
                'response_time' => (float) $visit->response_time,
                'user_agent' => $visit->user_agent,
            ];
        }

        return rest_ensure_response([
            'visits' => $formatted_visits,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => $total,
                'total_pages' => ceil($total / $per_page),
            ],
        ]);
    }

    public function get_comparison($request) {
        $dashboard = new GEO_Bot_Dashboard();
        
        $comparison = $dashboard->get_comparison_data(
            $request->get_param('period1_start'),
            $request->get_param('period1_end'),
            $request->get_param('period2_start'),
            $request->get_param('period2_end')
        );

        return rest_ensure_response($comparison);
    }

    public function get_bots_list($request) {
        $signatures = geo_bot_get_signatures();
        $category_labels = geo_bot_get_category_labels();

        $bots = [];
        foreach ($signatures as $category => $category_bots) {
            foreach ($category_bots as $name => $patterns) {
                $bots[] = [
                    'name' => $name,
                    'category' => $category,
                    'category_label' => $category_labels[$category] ?? $category,
                    'patterns' => $patterns,
                ];
            }
        }

        return rest_ensure_response([
            'total' => count($bots),
            'bots' => $bots,
        ]);
    }

    public function get_categories($request) {
        $labels = geo_bot_get_category_labels();
        $colors = geo_bot_get_category_colors();

        $categories = [];
        foreach ($labels as $key => $label) {
            $categories[] = [
                'key' => $key,
                'label' => $label,
                'color' => $colors[$key] ?? '#999',
            ];
        }

        return rest_ensure_response($categories);
    }

    public function get_database_info($request) {
        $db_size = $this->logger->get_database_size();
        $months = $this->logger->get_available_months();

        return rest_ensure_response([
            'total_rows' => (int) ($db_size->total_rows ?? 0),
            'size_mb' => (float) ($db_size->size_mb ?? 0),
            'months_count' => count($months),
            'oldest_data' => !empty($months) ? end($months)->month_year : null,
            'newest_data' => !empty($months) ? reset($months)->month_year : null,
        ]);
    }

    public function get_available_months($request) {
        $months = $this->logger->get_available_months();

        $formatted = [];
        foreach ($months as $month) {
            $formatted[] = [
                'month_year' => $month->month_year,
                'label' => $month->label,
                'count' => (int) $month->count,
            ];
        }

        return rest_ensure_response($formatted);
    }

    public function export_data($request) {
        $format = $request->get_param('format');
        $start_date = $request->get_param('start_date');
        $end_date = $request->get_param('end_date');

        $visits = $this->logger->get_visits([
            'start_date' => $start_date,
            'end_date' => $end_date,
            'limit' => 999999,
        ]);

        if ($format === 'csv') {
            $csv_lines = [];
            $csv_lines[] = implode(';', ['Date', 'Robot', 'Categorie', 'URL', 'IP', 'Status', 'Temps', 'User-Agent']);

            foreach ($visits as $visit) {
                $csv_lines[] = implode(';', [
                    $visit->visit_date,
                    $visit->bot_name,
                    $visit->bot_category,
                    '"' . str_replace('"', '""', $visit->url_visited) . '"',
                    $visit->ip_address,
                    $visit->http_status,
                    round($visit->response_time, 3),
                    '"' . str_replace('"', '""', $visit->user_agent) . '"',
                ]);
            }

            return new WP_REST_Response(
                implode("\n", $csv_lines),
                200,
                [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="bot-monitor-export.csv"',
                ]
            );
        }

        $formatted = [];
        foreach ($visits as $visit) {
            $formatted[] = [
                'id' => (int) $visit->id,
                'date' => $visit->visit_date,
                'bot_name' => $visit->bot_name,
                'bot_category' => $visit->bot_category,
                'url' => $visit->url_visited,
                'ip' => $visit->ip_address,
                'http_status' => (int) $visit->http_status,
                'response_time' => (float) $visit->response_time,
                'user_agent' => $visit->user_agent,
            ];
        }

        return rest_ensure_response([
            'period' => [
                'start' => $start_date,
                'end' => $end_date,
            ],
            'total' => count($formatted),
            'visits' => $formatted,
        ]);
    }
}
