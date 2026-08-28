<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de maintenance pour Geo Bot Monitor.
 *
 * Gère la rotation automatique des logs, l'optimisation de la table
 * et les alertes de taille.
 */
class GEO_Bot_Maintenance {

    private const CRON_HOOK = 'geo_bot_monitor_cleanup';
    private const DEFAULT_RETENTION_DAYS = 90;
    private const DEFAULT_SIZE_WARNING_MB = 100;

    private $logger;

    public function __construct() {
        $this->logger = new GEO_Bot_Logger();
    }

    /**
     * Initialise les hooks.
     */
    public static function init() {
        $instance = new self();

        add_action(self::CRON_HOOK, [$instance, 'run_cleanup']);
        add_action('admin_notices', [$instance, 'maybe_show_size_warning']);
        add_action('admin_init', [$instance, 'handle_manual_cleanup']);
    }

    /**
     * Programme le nettoyage automatique (appelé à l'activation).
     */
    public static function schedule_cleanup() {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time(), 'daily', self::CRON_HOOK);
        }
    }

    /**
     * Déprogramme le nettoyage automatique (appelé à la désactivation).
     */
    public static function unschedule_cleanup() {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }
    }

    /**
     * Exécute le nettoyage automatique (appelé par le cron ou manuellement).
     */
    public function run_cleanup() {
        $retention_days = $this->get_retention_days();

        if ($retention_days <= 0) {
            return;
        }

        $deleted = $this->purge_old_records($retention_days);

        if ($deleted > 0) {
            $this->optimize_table();
        }

        do_action('geo_bot_monitor_after_cleanup', $deleted, $retention_days);
    }

    /**
     * Supprime les enregistrements plus anciens que X jours.
     *
     * @param int $days Nombre de jours de conservation.
     * @return int Nombre de lignes supprimées.
     */
    public function purge_old_records($days) {
        global $wpdb;

        $days = absint($days);
        if ($days <= 0) {
            return 0;
        }

        $cutoff_date = gmdate('Y-m-d H:i:s', strtotime("-{$days} days"));
        $table = esc_sql($this->logger->get_table_name());

        return (int) $wpdb->query($wpdb->prepare(
            "DELETE FROM `$table` WHERE visit_date < %s",
            $cutoff_date
        ));
    }

    /**
     * Optimise la table après une purge massive.
     */
    public function optimize_table() {
        global $wpdb;

        $table = esc_sql($this->logger->get_table_name());
        $wpdb->query("OPTIMIZE TABLE `$table`");
    }

    /**
     * Récupère le nombre de jours de conservation.
     *
     * @return int
     */
    public function get_retention_days() {
        $days = get_option('geo_bot_monitor_retention_days', self::DEFAULT_RETENTION_DAYS);
        return absint($days);
    }

    /**
     * Vérifie si le nettoyage automatique est activé.
     *
     * @return bool
     */
    public function is_auto_cleanup_enabled() {
        return (bool) get_option('geo_bot_monitor_auto_cleanup', true);
    }

    /**
     * Récupère le seuil d'alerte de taille en Mo.
     *
     * @return int
     */
    public function get_size_warning_threshold() {
        $threshold = get_option('geo_bot_monitor_size_warning_mb', self::DEFAULT_SIZE_WARNING_MB);
        return absint($threshold);
    }

    /**
     * Affiche une alerte admin si la table dépasse le seuil configuré.
     */
    public function maybe_show_size_warning() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $threshold = $this->get_size_warning_threshold();
        if ($threshold <= 0) {
            return;
        }

        $db_size = $this->logger->get_database_size();
        if (empty($db_size->size_mb) || (float) $db_size->size_mb < $threshold) {
            return;
        }

        $maintenance_url = admin_url('admin.php?page=geo-bot-maintenance');
        $settings_url = admin_url('admin.php?page=geo-bot-settings');

        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>' . esc_html__('Geo Bot Monitor : table volumineuse', 'geo-bot-monitor') . '</strong></p>';
        echo '<p>';
        printf(
            /* translators: 1: table size in MB, 2: threshold in MB */
            esc_html__('La table des visites occupe actuellement %1$s Mo (seuil d\'alerte : %2$s Mo).', 'geo-bot-monitor'),
            esc_html($db_size->size_mb),
            esc_html($threshold)
        );
        echo '</p>';
        echo '<p>';
        echo '<a href="' . esc_url($maintenance_url) . '" class="button button-primary">' . esc_html__('Aller à la maintenance', 'geo-bot-monitor') . '</a> ';
        echo '<a href="' . esc_url($settings_url) . '" class="button">' . esc_html__('Configurer la rétention', 'geo-bot-monitor') . '</a>';
        echo '</p>';
        echo '</div>';
    }

    /**
     * Gère le nettoyage manuel depuis la page de maintenance.
     */
    public function handle_manual_cleanup() {
        if (!isset($_POST['geo_bot_manual_cleanup']) || !current_user_can('manage_options')) {
            return;
        }

        check_admin_referer('geo_bot_manual_cleanup', 'geo_bot_manual_cleanup_nonce');

        $days = isset($_POST['geo_bot_cleanup_days']) ? absint($_POST['geo_bot_cleanup_days']) : $this->get_retention_days();
        if ($days <= 0) {
            add_settings_error(
                'geo_bot_monitor',
                'geo_bot_cleanup_error',
                __('Durée de conservation invalide.', 'geo-bot-monitor'),
                'error'
            );
            return;
        }

        $deleted = $this->purge_old_records($days);
        $this->optimize_table();

        add_settings_error(
            'geo_bot_monitor',
            'geo_bot_cleanup_success',
            sprintf(
                /* translators: %d: number of deleted records */
                __('%d enregistrements supprimés et table optimisée.', 'geo-bot-monitor'),
                $deleted
            ),
            'success'
        );
    }
}
