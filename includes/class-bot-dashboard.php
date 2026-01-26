<?php
if (!defined('ABSPATH')) {
    exit;
}

class GEO_Bot_Dashboard {

    private $logger;

    public function __construct() {
        $this->logger = new GEO_Bot_Logger();
    }

    public function render() {
        $start_date = isset($_GET['start_date']) ? sanitize_text_field(wp_unslash($_GET['start_date'])) : gmdate('Y-m-d', strtotime('-30 days'));
        $end_date = isset($_GET['end_date']) ? sanitize_text_field(wp_unslash($_GET['end_date'])) : gmdate('Y-m-d');

        $stats = $this->logger->get_stats($start_date, $end_date);
        $category_labels = geo_bot_get_category_labels();
        $category_colors = geo_bot_get_category_colors();
        ?>
        <div class="wrap geo-bot-dashboard">
            <h1><?php esc_html_e('Bot Monitor - Tableau de bord', 'geo-bot-monitor'); ?></h1>

            <div class="geo-bot-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="geo-bot-monitor">
                    <label for="start_date"><?php esc_html_e('Du', 'geo-bot-monitor'); ?></label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo esc_attr($start_date); ?>">
                    <label for="end_date"><?php esc_html_e('Au', 'geo-bot-monitor'); ?></label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo esc_attr($end_date); ?>">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Filtrer', 'geo-bot-monitor'); ?></button>
                </form>
            </div>

            <div class="geo-bot-stats-grid">
                <div class="geo-bot-stat-card geo-bot-stat-total">
                    <span class="stat-value"><?php echo esc_html(number_format_i18n($stats['total'])); ?></span>
                    <span class="stat-label"><?php esc_html_e('Visites totales', 'geo-bot-monitor'); ?></span>
                </div>
                <?php foreach ($stats['by_category'] as $cat => $data): ?>
                <div class="geo-bot-stat-card" style="border-left-color: <?php echo esc_attr($category_colors[$cat] ?? '#999'); ?>">
                    <span class="stat-value"><?php echo esc_html(number_format_i18n($data->count)); ?></span>
                    <span class="stat-label"><?php echo esc_html($category_labels[$cat] ?? $cat); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="geo-bot-charts-grid">
                <div class="geo-bot-chart-card">
                    <h3><?php esc_html_e('Evolution par jour', 'geo-bot-monitor'); ?></h3>
                    <canvas id="geo-bot-chart-daily"></canvas>
                </div>
                <div class="geo-bot-chart-card">
                    <h3><?php esc_html_e('Répartition par catégorie', 'geo-bot-monitor'); ?></h3>
                    <canvas id="geo-bot-chart-categories"></canvas>
                </div>
            </div>

            <div class="geo-bot-tables-grid">
                <div class="geo-bot-table-card">
                    <h3><?php esc_html_e('Top 20 Robots', 'geo-bot-monitor'); ?></h3>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Robot', 'geo-bot-monitor'); ?></th>
                                <th><?php esc_html_e('Catégorie', 'geo-bot-monitor'); ?></th>
                                <th><?php esc_html_e('Visites', 'geo-bot-monitor'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['by_bot'] as $bot): ?>
                            <tr>
                                <td><strong><?php echo esc_html($bot->bot_name); ?></strong></td>
                                <td>
                                    <span class="geo-bot-category-badge" style="background: <?php echo esc_attr($category_colors[$bot->bot_category] ?? '#999'); ?>">
                                        <?php echo esc_html($category_labels[$bot->bot_category] ?? $bot->bot_category); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(number_format_i18n($bot->count)); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="geo-bot-table-card">
                    <h3><?php esc_html_e('Top 10 Pages visitées', 'geo-bot-monitor'); ?></h3>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('URL', 'geo-bot-monitor'); ?></th>
                                <th><?php esc_html_e('Visites', 'geo-bot-monitor'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['top_urls'] as $url): ?>
                            <tr>
                                <td class="geo-bot-url-cell" title="<?php echo esc_attr($url->url_visited); ?>">
                                    <?php echo esc_html(wp_trim_words($url->url_visited, 10, '...')); ?>
                                </td>
                                <td><?php echo esc_html(number_format_i18n($url->count)); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
        var geoBotChartData = {
            daily: <?php echo wp_json_encode($this->prepare_daily_chart_data($stats['by_day'], $category_colors)); ?>,
            categories: <?php echo wp_json_encode($this->prepare_category_chart_data($stats['by_category'], $category_labels, $category_colors)); ?>
        };
        </script>
        <?php
    }

    public function render_compare() {
        ?>
        <div class="wrap geo-bot-dashboard">
            <h1><?php _e('Bot Monitor - Comparer les périodes', 'geo-bot-monitor'); ?></h1>

            <div class="geo-bot-compare-form">
                <div class="geo-bot-period-selector">
                    <h3><?php _e('Période 1 (référence)', 'geo-bot-monitor'); ?></h3>
                    <label for="period1_start"><?php _e('Du', 'geo-bot-monitor'); ?></label>
                    <input type="date" id="period1_start" value="<?php echo date('Y-m-d', strtotime('-60 days')); ?>">
                    <label for="period1_end"><?php _e('Au', 'geo-bot-monitor'); ?></label>
                    <input type="date" id="period1_end" value="<?php echo date('Y-m-d', strtotime('-31 days')); ?>">
                </div>
                <div class="geo-bot-period-selector">
                    <h3><?php _e('Période 2 (à comparer)', 'geo-bot-monitor'); ?></h3>
                    <label for="period2_start"><?php _e('Du', 'geo-bot-monitor'); ?></label>
                    <input type="date" id="period2_start" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                    <label for="period2_end"><?php _e('Au', 'geo-bot-monitor'); ?></label>
                    <input type="date" id="period2_end" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <button type="button" id="geo-bot-compare-btn" class="button button-primary button-hero">
                    <?php _e('Comparer', 'geo-bot-monitor'); ?>
                </button>
            </div>

            <div id="geo-bot-comparison-results" style="display: none;">
                <h2><?php _e('Résultats de la comparaison', 'geo-bot-monitor'); ?></h2>
                
                <div class="geo-bot-comparison-summary">
                    <div class="geo-bot-comparison-card">
                        <h4><?php _e('Période 1', 'geo-bot-monitor'); ?></h4>
                        <span class="period-dates" id="period1-dates"></span>
                        <span class="period-total" id="period1-total"></span>
                    </div>
                    <div class="geo-bot-comparison-card geo-bot-comparison-diff">
                        <h4><?php _e('Variation', 'geo-bot-monitor'); ?></h4>
                        <span class="diff-value" id="diff-value"></span>
                        <span class="diff-percent" id="diff-percent"></span>
                    </div>
                    <div class="geo-bot-comparison-card">
                        <h4><?php _e('Période 2', 'geo-bot-monitor'); ?></h4>
                        <span class="period-dates" id="period2-dates"></span>
                        <span class="period-total" id="period2-total"></span>
                    </div>
                </div>

                <div class="geo-bot-comparison-details">
                    <h3><?php _e('Détail par catégorie', 'geo-bot-monitor'); ?></h3>
                    <table class="widefat striped" id="comparison-by-category">
                        <thead>
                            <tr>
                                <th><?php _e('Catégorie', 'geo-bot-monitor'); ?></th>
                                <th><?php _e('Période 1', 'geo-bot-monitor'); ?></th>
                                <th><?php _e('Période 2', 'geo-bot-monitor'); ?></th>
                                <th><?php _e('Variation', 'geo-bot-monitor'); ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="geo-bot-comparison-details">
                    <h3><?php _e('Détail par robot', 'geo-bot-monitor'); ?></h3>
                    <table class="widefat striped" id="comparison-by-bot">
                        <thead>
                            <tr>
                                <th><?php _e('Robot', 'geo-bot-monitor'); ?></th>
                                <th><?php _e('Période 1', 'geo-bot-monitor'); ?></th>
                                <th><?php _e('Période 2', 'geo-bot-monitor'); ?></th>
                                <th><?php _e('Variation', 'geo-bot-monitor'); ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="geo-bot-charts-grid">
                    <div class="geo-bot-chart-card">
                        <h3><?php _e('Comparaison visuelle', 'geo-bot-monitor'); ?></h3>
                        <canvas id="geo-bot-chart-comparison"></canvas>
                    </div>
                </div>
            </div>

            <?php wp_nonce_field('geo_bot_compare', 'geo_bot_compare_nonce'); ?>
        </div>
        <?php
    }

    public function render_maintenance() {
        $months = $this->logger->get_available_months();
        $db_size = $this->logger->get_database_size();
        ?>
        <div class="wrap geo-bot-dashboard">
            <h1><?php _e('Bot Monitor - Maintenance', 'geo-bot-monitor'); ?></h1>

            <div class="geo-bot-maintenance-info">
                <div class="geo-bot-stat-card">
                    <span class="stat-value"><?php echo number_format_i18n($db_size->total_rows ?? 0); ?></span>
                    <span class="stat-label"><?php _e('Enregistrements', 'geo-bot-monitor'); ?></span>
                </div>
                <div class="geo-bot-stat-card">
                    <span class="stat-value"><?php echo esc_html($db_size->size_mb ?? '0'); ?> MB</span>
                    <span class="stat-label"><?php _e('Taille de la base', 'geo-bot-monitor'); ?></span>
                </div>
            </div>

            <div class="geo-bot-maintenance-purge">
                <h2><?php _e('Purger les données anciennes', 'geo-bot-monitor'); ?></h2>
                <p class="description">
                    <?php _e('Sélectionnez les mois à supprimer pour libérer de l\'espace. Cette action est irréversible.', 'geo-bot-monitor'); ?>
                </p>

                <?php if (empty($months)): ?>
                <p><?php _e('Aucune donnée enregistrée.', 'geo-bot-monitor'); ?></p>
                <?php else: ?>
                <form id="geo-bot-purge-form">
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th class="check-column"><input type="checkbox" id="select-all-months"></th>
                                <th><?php _e('Mois', 'geo-bot-monitor'); ?></th>
                                <th><?php _e('Nombre de visites', 'geo-bot-monitor'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($months as $month): ?>
                            <tr>
                                <td><input type="checkbox" name="months[]" value="<?php echo esc_attr($month->month_year); ?>"></td>
                                <td><?php echo esc_html($month->label); ?></td>
                                <td><?php echo number_format_i18n($month->count); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-primary" id="purge-btn">
                            <?php _e('Supprimer les mois sélectionnés', 'geo-bot-monitor'); ?>
                        </button>
                    </p>
                    <?php wp_nonce_field('geo_bot_purge', 'geo_bot_purge_nonce'); ?>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function get_comparison_data($period1_start, $period1_end, $period2_start, $period2_end) {
        $stats1 = $this->logger->get_stats($period1_start, $period1_end);
        $stats2 = $this->logger->get_stats($period2_start, $period2_end);

        $category_labels = geo_bot_get_category_labels();

        $by_category = [];
        $all_categories = array_unique(array_merge(
            array_keys($stats1['by_category']),
            array_keys($stats2['by_category'])
        ));

        foreach ($all_categories as $cat) {
            $count1 = $stats1['by_category'][$cat]->count ?? 0;
            $count2 = $stats2['by_category'][$cat]->count ?? 0;
            $diff = $count2 - $count1;
            $diff_percent = $count1 > 0 ? round(($diff / $count1) * 100, 1) : ($count2 > 0 ? 100 : 0);

            $by_category[] = [
                'category' => $cat,
                'label' => $category_labels[$cat] ?? $cat,
                'period1' => $count1,
                'period2' => $count2,
                'diff' => $diff,
                'diff_percent' => $diff_percent,
            ];
        }

        $by_bot = [];
        $all_bots = [];
        foreach ($stats1['by_bot'] as $bot) {
            $all_bots[$bot->bot_name] = ['period1' => $bot->count, 'period2' => 0];
        }
        foreach ($stats2['by_bot'] as $bot) {
            if (isset($all_bots[$bot->bot_name])) {
                $all_bots[$bot->bot_name]['period2'] = $bot->count;
            } else {
                $all_bots[$bot->bot_name] = ['period1' => 0, 'period2' => $bot->count];
            }
        }

        foreach ($all_bots as $name => $counts) {
            $diff = $counts['period2'] - $counts['period1'];
            $diff_percent = $counts['period1'] > 0 ? round(($diff / $counts['period1']) * 100, 1) : ($counts['period2'] > 0 ? 100 : 0);
            
            $by_bot[] = [
                'bot_name' => $name,
                'period1' => $counts['period1'],
                'period2' => $counts['period2'],
                'diff' => $diff,
                'diff_percent' => $diff_percent,
            ];
        }

        usort($by_bot, function($a, $b) {
            return abs($b['diff']) - abs($a['diff']);
        });

        $total_diff = $stats2['total'] - $stats1['total'];
        $total_diff_percent = $stats1['total'] > 0 ? round(($total_diff / $stats1['total']) * 100, 1) : 0;

        return [
            'period1' => [
                'start' => $period1_start,
                'end' => $period1_end,
                'total' => $stats1['total'],
            ],
            'period2' => [
                'start' => $period2_start,
                'end' => $period2_end,
                'total' => $stats2['total'],
            ],
            'diff' => $total_diff,
            'diff_percent' => $total_diff_percent,
            'by_category' => $by_category,
            'by_bot' => array_slice($by_bot, 0, 20),
        ];
    }

    private function prepare_daily_chart_data($by_day, $colors) {
        $datasets = [];
        $labels = [];
        $data_by_category = [];

        foreach ($by_day as $row) {
            if (!in_array($row->day, $labels)) {
                $labels[] = $row->day;
            }
            if (!isset($data_by_category[$row->bot_category])) {
                $data_by_category[$row->bot_category] = [];
            }
            $data_by_category[$row->bot_category][$row->day] = $row->count;
        }

        $category_labels = geo_bot_get_category_labels();

        foreach ($data_by_category as $cat => $days) {
            $data = [];
            foreach ($labels as $day) {
                $data[] = $days[$day] ?? 0;
            }
            $datasets[] = [
                'label' => $category_labels[$cat] ?? $cat,
                'data' => $data,
                'backgroundColor' => $colors[$cat] ?? '#999',
                'borderColor' => $colors[$cat] ?? '#999',
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    private function prepare_category_chart_data($by_category, $labels, $colors) {
        $chart_labels = [];
        $data = [];
        $background_colors = [];

        foreach ($by_category as $cat => $row) {
            $chart_labels[] = $labels[$cat] ?? $cat;
            $data[] = $row->count;
            $background_colors[] = $colors[$cat] ?? '#999';
        }

        return [
            'labels' => $chart_labels,
            'datasets' => [[
                'data' => $data,
                'backgroundColor' => $background_colors,
            ]],
        ];
    }
}
