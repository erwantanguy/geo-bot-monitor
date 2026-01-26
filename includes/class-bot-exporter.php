<?php
if (!defined('ABSPATH')) {
    exit;
}

class GEO_Bot_Exporter {

    private $logger;

    public function __construct() {
        $this->logger = new GEO_Bot_Logger();
    }

    public function render_page() {
        $months = $this->logger->get_available_months();
        $current_year = date('Y');
        $years = range($current_year - 5, $current_year);
        ?>
        <div class="wrap geo-bot-dashboard">
            <h1><?php _e('Bot Monitor - Exporter', 'geo-bot-monitor'); ?></h1>

            <div class="geo-bot-export-options">
                <h2><?php _e('Exporter les données', 'geo-bot-monitor'); ?></h2>

                <form id="geo-bot-export-form" class="geo-bot-export-form">
                    <div class="geo-bot-export-period">
                        <h3><?php _e('Sélectionner la période', 'geo-bot-monitor'); ?></h3>
                        
                        <div class="geo-bot-export-type">
                            <label>
                                <input type="radio" name="export_type" value="month" checked>
                                <?php _e('Par mois', 'geo-bot-monitor'); ?>
                            </label>
                            <label>
                                <input type="radio" name="export_type" value="range">
                                <?php _e('Plage personnalisée', 'geo-bot-monitor'); ?>
                            </label>
                        </div>

                        <div id="export-month-selector" class="geo-bot-export-selector">
                            <label for="export_month"><?php _e('Mois', 'geo-bot-monitor'); ?></label>
                            <select id="export_month" name="month">
                                <option value=""><?php _e('Tous les mois', 'geo-bot-monitor'); ?></option>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>" <?php selected($m, date('n')); ?>>
                                    <?php echo date_i18n('F', mktime(0, 0, 0, $m, 1)); ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                            
                            <label for="export_year"><?php _e('Année', 'geo-bot-monitor'); ?></label>
                            <select id="export_year" name="year">
                                <?php foreach (array_reverse($years) as $year): ?>
                                <option value="<?php echo $year; ?>" <?php selected($year, $current_year); ?>>
                                    <?php echo $year; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="export-range-selector" class="geo-bot-export-selector" style="display: none;">
                            <label for="export_start"><?php _e('Du', 'geo-bot-monitor'); ?></label>
                            <input type="date" id="export_start" name="start_date" value="<?php echo date('Y-m-01'); ?>">
                            
                            <label for="export_end"><?php _e('Au', 'geo-bot-monitor'); ?></label>
                            <input type="date" id="export_end" name="end_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="geo-bot-export-format">
                        <h3><?php _e('Format d\'export', 'geo-bot-monitor'); ?></h3>
                        
                        <div class="geo-bot-format-cards">
                            <label class="geo-bot-format-card">
                                <input type="radio" name="format" value="csv" checked>
                                <span class="format-icon dashicons dashicons-media-spreadsheet"></span>
                                <span class="format-name">CSV</span>
                                <span class="format-desc"><?php _e('Compatible Excel', 'geo-bot-monitor'); ?></span>
                            </label>
                            
                            <label class="geo-bot-format-card">
                                <input type="radio" name="format" value="pdf">
                                <span class="format-icon dashicons dashicons-media-document"></span>
                                <span class="format-name">PDF</span>
                                <span class="format-desc"><?php _e('Rapport synthétique', 'geo-bot-monitor'); ?></span>
                            </label>
                            
                            <label class="geo-bot-format-card">
                                <input type="radio" name="format" value="markdown">
                                <span class="format-icon dashicons dashicons-editor-code"></span>
                                <span class="format-name">Markdown</span>
                                <span class="format-desc"><?php _e('Documentation', 'geo-bot-monitor'); ?></span>
                            </label>
                        </div>
                    </div>

                    <p class="submit">
                        <button type="submit" class="button button-primary button-hero">
                            <span class="dashicons dashicons-download"></span>
                            <?php _e('Télécharger', 'geo-bot-monitor'); ?>
                        </button>
                    </p>

                    <?php wp_nonce_field('geo_bot_export', 'geo_bot_export_nonce'); ?>
                </form>
            </div>

            <div class="geo-bot-export-history">
                <h2><?php _e('Données disponibles', 'geo-bot-monitor'); ?></h2>
                <?php if (empty($months)): ?>
                <p><?php _e('Aucune donnée enregistrée.', 'geo-bot-monitor'); ?></p>
                <?php else: ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php _e('Mois', 'geo-bot-monitor'); ?></th>
                            <th><?php _e('Visites enregistrées', 'geo-bot-monitor'); ?></th>
                            <th><?php _e('Actions rapides', 'geo-bot-monitor'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($months as $month): ?>
                        <tr>
                            <td><?php echo esc_html($month->label); ?></td>
                            <td><?php echo number_format_i18n($month->count); ?></td>
                            <td>
                                <button type="button" class="button quick-export" 
                                        data-month="<?php echo esc_attr($month->month_year); ?>" 
                                        data-format="csv">CSV</button>
                                <button type="button" class="button quick-export" 
                                        data-month="<?php echo esc_attr($month->month_year); ?>" 
                                        data-format="pdf">PDF</button>
                                <button type="button" class="button quick-export" 
                                        data-month="<?php echo esc_attr($month->month_year); ?>" 
                                        data-format="markdown">MD</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function export($format, $month = '', $year = null, $start_date = null, $end_date = null) {
        if ($start_date && $end_date) {
            $args = [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'limit' => 999999,
            ];
            $period_label = "$start_date - $end_date";
        } elseif ($month && $year) {
            $start_date = "$year-$month-01";
            $end_date = date('Y-m-t', strtotime($start_date));
            $args = [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'limit' => 999999,
            ];
            $period_label = date_i18n('F Y', strtotime($start_date));
        } else {
            $start_date = "$year-01-01";
            $end_date = "$year-12-31";
            $args = [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'limit' => 999999,
            ];
            $period_label = $year;
        }

        $visits = $this->logger->get_visits($args);
        $stats = $this->logger->get_stats($start_date, $end_date);

        switch ($format) {
            case 'csv':
                $this->export_csv($visits, $period_label);
                break;
            case 'pdf':
                $this->export_pdf($visits, $stats, $period_label);
                break;
            case 'markdown':
                $this->export_markdown($visits, $stats, $period_label);
                break;
        }

        exit;
    }

    private function export_csv($visits, $period_label) {
        $filename = 'bot-monitor-' . sanitize_file_name($period_label) . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            __('Date', 'geo-bot-monitor'),
            __('Robot', 'geo-bot-monitor'),
            __('Catégorie', 'geo-bot-monitor'),
            __('URL', 'geo-bot-monitor'),
            __('IP', 'geo-bot-monitor'),
            __('Status HTTP', 'geo-bot-monitor'),
            __('Temps réponse (s)', 'geo-bot-monitor'),
            __('User-Agent', 'geo-bot-monitor'),
        ], ';');

        $category_labels = geo_bot_get_category_labels();

        foreach ($visits as $visit) {
            fputcsv($output, [
                $visit->visit_date,
                $visit->bot_name,
                $category_labels[$visit->bot_category] ?? $visit->bot_category,
                $visit->url_visited,
                $visit->ip_address,
                $visit->http_status,
                round($visit->response_time, 3),
                $visit->user_agent,
            ], ';');
        }

        fclose($output);
    }

    private function export_pdf($visits, $stats, $period_label) {
        $filename = 'bot-monitor-' . sanitize_file_name($period_label) . '.pdf';
        $category_labels = geo_bot_get_category_labels();

        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.html"');

        ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php printf(__('Rapport Bot Monitor - %s', 'geo-bot-monitor'), $period_label); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; color: #333; }
        h1 { color: #0073aa; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
        h2 { color: #23282d; margin-top: 30px; }
        .stats-grid { display: flex; gap: 20px; flex-wrap: wrap; margin: 20px 0; }
        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 5px; min-width: 150px; text-align: center; }
        .stat-value { display: block; font-size: 2em; font-weight: bold; color: #0073aa; }
        .stat-label { color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f1f1f1; font-weight: bold; }
        .category-seo { color: #4285f4; }
        .category-geo_ai { color: #ea4335; }
        .category-social { color: #fbbc05; }
        .category-other { color: #34a853; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 0.9em; }
        @media print { body { margin: 20px; } }
    </style>
</head>
<body>
    <h1><?php printf(__('Rapport Bot Monitor - %s', 'geo-bot-monitor'), esc_html($period_label)); ?></h1>
    
    <h2><?php _e('Résumé', 'geo-bot-monitor'); ?></h2>
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-value"><?php echo number_format_i18n($stats['total']); ?></span>
            <span class="stat-label"><?php _e('Visites totales', 'geo-bot-monitor'); ?></span>
        </div>
        <?php foreach ($stats['by_category'] as $cat => $data): ?>
        <div class="stat-card">
            <span class="stat-value category-<?php echo esc_attr($cat); ?>"><?php echo number_format_i18n($data->count); ?></span>
            <span class="stat-label"><?php echo esc_html($category_labels[$cat] ?? $cat); ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <h2><?php _e('Top 20 Robots', 'geo-bot-monitor'); ?></h2>
    <table>
        <thead>
            <tr>
                <th><?php _e('Robot', 'geo-bot-monitor'); ?></th>
                <th><?php _e('Catégorie', 'geo-bot-monitor'); ?></th>
                <th><?php _e('Visites', 'geo-bot-monitor'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stats['by_bot'] as $bot): ?>
            <tr>
                <td><?php echo esc_html($bot->bot_name); ?></td>
                <td class="category-<?php echo esc_attr($bot->bot_category); ?>">
                    <?php echo esc_html($category_labels[$bot->bot_category] ?? $bot->bot_category); ?>
                </td>
                <td><?php echo number_format_i18n($bot->count); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2><?php _e('Top 10 Pages visitées', 'geo-bot-monitor'); ?></h2>
    <table>
        <thead>
            <tr>
                <th><?php _e('URL', 'geo-bot-monitor'); ?></th>
                <th><?php _e('Visites', 'geo-bot-monitor'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stats['top_urls'] as $url): ?>
            <tr>
                <td><?php echo esc_html($url->url_visited); ?></td>
                <td><?php echo number_format_i18n($url->count); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <?php printf(__('Généré le %s par GEO Bot Monitor', 'geo-bot-monitor'), date_i18n(get_option('date_format') . ' ' . get_option('time_format'))); ?>
    </div>
</body>
</html>
        <?php
    }

    private function export_markdown($visits, $stats, $period_label) {
        $filename = 'bot-monitor-' . sanitize_file_name($period_label) . '.md';
        $category_labels = geo_bot_get_category_labels();

        header('Content-Type: text/markdown; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo "# Rapport Bot Monitor - $period_label\n\n";
        echo "## Résumé\n\n";
        echo "| Métrique | Valeur |\n";
        echo "|----------|--------|\n";
        echo "| **Visites totales** | " . number_format_i18n($stats['total']) . " |\n";

        foreach ($stats['by_category'] as $cat => $data) {
            $label = $category_labels[$cat] ?? $cat;
            echo "| $label | " . number_format_i18n($data->count) . " |\n";
        }

        echo "\n## Top 20 Robots\n\n";
        echo "| Robot | Catégorie | Visites |\n";
        echo "|-------|-----------|--------|\n";

        foreach ($stats['by_bot'] as $bot) {
            $cat_label = $category_labels[$bot->bot_category] ?? $bot->bot_category;
            echo "| {$bot->bot_name} | $cat_label | " . number_format_i18n($bot->count) . " |\n";
        }

        echo "\n## Top 10 Pages visitées\n\n";
        echo "| URL | Visites |\n";
        echo "|-----|--------|\n";

        foreach ($stats['top_urls'] as $url) {
            $short_url = strlen($url->url_visited) > 60 ? substr($url->url_visited, 0, 60) . '...' : $url->url_visited;
            echo "| `$short_url` | " . number_format_i18n($url->count) . " |\n";
        }

        echo "\n---\n";
        echo "*Généré le " . date_i18n(get_option('date_format') . ' ' . get_option('time_format')) . " par GEO Bot Monitor*\n";
    }
}
