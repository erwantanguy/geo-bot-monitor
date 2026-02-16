<?php
if (!defined('ABSPATH')) {
    exit;
}

class GEO_Bot_Blocker {

    private static $known_user_agents = [
        'Googlebot' => 'Googlebot',
        'Googlebot-Image' => 'Googlebot-Image',
        'Googlebot-Video' => 'Googlebot-Video',
        'Googlebot-News' => 'Googlebot-News',
        'Bingbot' => 'bingbot',
        'Slurp' => 'Slurp',
        'DuckDuckBot' => 'DuckDuckBot',
        'Baiduspider' => 'Baiduspider',
        'YandexBot' => 'YandexBot',
        'GPTBot' => 'GPTBot',
        'ChatGPT-User' => 'ChatGPT-User',
        'Google-Extended' => 'Google-Extended',
        'ClaudeBot' => 'Claude-Web',
        'Claude-Web' => 'Claude-Web',
        'Anthropic-AI' => 'anthropic-ai',
        'PerplexityBot' => 'PerplexityBot',
        'Cohere-AI' => 'cohere-ai',
        'Bytespider' => 'Bytespider',
        'CCBot' => 'CCBot',
        'Applebot' => 'Applebot',
        'Applebot-Extended' => 'Applebot-Extended',
        'Facebot' => 'facebot',
        'FacebookBot' => 'FacebookBot',
        'Twitterbot' => 'Twitterbot',
        'LinkedInBot' => 'LinkedInBot',
        'PinterestBot' => 'Pinterest',
        'Slackbot' => 'Slackbot',
        'WhatsApp' => 'WhatsApp',
        'TelegramBot' => 'TelegramBot',
        'Discordbot' => 'Discordbot',
        'SemrushBot' => 'SemrushBot',
        'AhrefsBot' => 'AhrefsBot',
        'MJ12bot' => 'MJ12bot',
        'DotBot' => 'DotBot',
        'PetalBot' => 'PetalBot',
        'SeznamBot' => 'SeznamBot',
        'Sogou' => 'Sogou',
        'Exabot' => 'Exabot',
        'ia_archiver' => 'ia_archiver',
        'archive.org_bot' => 'archive.org_bot',
    ];

    private static $ai_bots = [
        'GPTBot', 'ChatGPT-User', 'Google-Extended', 'ClaudeBot', 'Claude-Web',
        'Anthropic-AI', 'PerplexityBot', 'Cohere-AI', 'Bytespider', 'CCBot',
        'Applebot-Extended', 'Meta-ExternalAgent', 'Meta-ExternalFetcher',
        'FacebookBot', 'OAI-SearchBot', 'AI2Bot', 'Diffbot', 'omgili', 'omgilibot'
    ];

    private static $seo_bots = [
        'Googlebot', 'Googlebot-Image', 'Googlebot-Video', 'Googlebot-News',
        'Bingbot', 'Slurp', 'DuckDuckBot', 'Baiduspider', 'YandexBot'
    ];

    public static function get_user_agent($bot_name) {
        return self::$known_user_agents[$bot_name] ?? $bot_name;
    }

    public static function is_ai_bot($bot_name) {
        return in_array($bot_name, self::$ai_bots);
    }

    public static function is_seo_bot($bot_name) {
        return in_array($bot_name, self::$seo_bots);
    }

    public static function generate_robots_txt($bot_names) {
        if (!is_array($bot_names)) {
            $bot_names = [$bot_names];
        }

        $lines = ["# Règles de blocage générées par GEO Bot Monitor"];
        $lines[] = "# Date: " . gmdate('Y-m-d H:i:s');
        $lines[] = "";

        foreach ($bot_names as $bot_name) {
            $user_agent = self::get_user_agent($bot_name);
            $lines[] = "# Bloquer $bot_name";
            $lines[] = "User-agent: $user_agent";
            $lines[] = "Disallow: /";
            $lines[] = "";
        }

        return implode("\n", $lines);
    }

    public static function generate_llms_txt($bot_names) {
        if (!is_array($bot_names)) {
            $bot_names = [$bot_names];
        }

        $site_name = get_bloginfo('name');
        $site_url = home_url();

        $lines = ["# $site_name - Fichier llms.txt"];
        $lines[] = "# Généré par GEO Bot Monitor";
        $lines[] = "# Date: " . gmdate('Y-m-d H:i:s');
        $lines[] = "";
        $lines[] = "# Ce fichier indique aux crawlers IA les préférences du site";
        $lines[] = "# Documentation: https://llmstxt.org/";
        $lines[] = "";
        $lines[] = "# Site";
        $lines[] = "url: $site_url";
        $lines[] = "name: $site_name";
        $lines[] = "";
        $lines[] = "# Bots IA bloqués";

        foreach ($bot_names as $bot_name) {
            if (self::is_ai_bot($bot_name)) {
                $user_agent = self::get_user_agent($bot_name);
                $lines[] = "User-agent: $user_agent";
            }
        }

        $lines[] = "";
        $lines[] = "# Règles";
        $lines[] = "Disallow: /";

        return implode("\n", $lines);
    }

    public static function generate_htaccess($bot_names) {
        if (!is_array($bot_names)) {
            $bot_names = [$bot_names];
        }

        $lines = ["# Règles de blocage générées par GEO Bot Monitor"];
        $lines[] = "# Date: " . gmdate('Y-m-d H:i:s');
        $lines[] = "# À ajouter dans votre fichier .htaccess";
        $lines[] = "";
        $lines[] = "<IfModule mod_rewrite.c>";
        $lines[] = "RewriteEngine On";
        $lines[] = "";

        foreach ($bot_names as $bot_name) {
            $user_agent = self::get_user_agent($bot_name);
            $escaped = preg_quote($user_agent, '/');
            $lines[] = "# Bloquer $bot_name";
            $lines[] = "RewriteCond %{HTTP_USER_AGENT} $escaped [NC]";
            $lines[] = "RewriteRule .* - [F,L]";
            $lines[] = "";
        }

        $lines[] = "</IfModule>";

        return implode("\n", $lines);
    }

    public static function get_blocked_bots() {
        return get_option('geo_bot_blocked_bots', []);
    }

    public static function add_blocked_bot($bot_name, $methods = ['robots']) {
        $blocked = self::get_blocked_bots();
        $blocked[$bot_name] = [
            'methods' => $methods,
            'date' => gmdate('Y-m-d H:i:s'),
        ];
        update_option('geo_bot_blocked_bots', $blocked);
        return true;
    }

    public static function remove_blocked_bot($bot_name) {
        $blocked = self::get_blocked_bots();
        if (isset($blocked[$bot_name])) {
            unset($blocked[$bot_name]);
            update_option('geo_bot_blocked_bots', $blocked);
            return true;
        }
        return false;
    }

    public static function filter_llms_content($content) {
        $blocked_bots = self::get_blocked_bots();
        
        if (empty($blocked_bots)) {
            return $content;
        }

        $ai_blocked = [];
        foreach ($blocked_bots as $bot_name => $info) {
            if (self::is_ai_bot($bot_name)) {
                $ai_blocked[$bot_name] = [
                    'user_agent' => self::get_user_agent($bot_name),
                    'date' => $info['date'] ?? '',
                ];
            }
        }

        if (empty($ai_blocked)) {
            return $content;
        }

        $section = "\n## Crawlers IA bloques\n\n";
        $section .= "Les crawlers IA suivants ne sont pas autorises a indexer ce site :\n\n";

        foreach ($ai_blocked as $bot_name => $info) {
            $section .= "- **$bot_name** (User-Agent: `{$info['user_agent']}`)\n";
        }

        $section .= "\n";
        $section .= "### Directives de blocage\n\n";
        $section .= "```\n";

        foreach ($ai_blocked as $bot_name => $info) {
            $section .= "User-agent: {$info['user_agent']}\n";
            $section .= "Disallow: /\n\n";
        }

        $section .= "```\n\n";
        $section .= "Ces directives sont egalement presentes dans le fichier robots.txt.\n";
        $section .= "Gere par : GEO Bot Monitor v" . GEO_BOT_MONITOR_VERSION . "\n\n";

        $pos = strpos($content, '## Informations techniques');
        if ($pos !== false) {
            $content = substr($content, 0, $pos) . $section . substr($content, $pos);
        } else {
            $content .= $section;
        }

        return $content;
    }

    public static function init_hooks() {
        add_filter('geo_llms_content', [self::class, 'filter_llms_content'], 20);
    }

    public function render_page() {
        $blocked_bots = self::get_blocked_bots();
        $logger = new GEO_Bot_Logger();
        $stats = $logger->get_stats(gmdate('Y-m-d', strtotime('-30 days')), gmdate('Y-m-d'));
        $category_labels = geo_bot_get_category_labels();
        $category_colors = geo_bot_get_category_colors();
        ?>
        <div class="wrap geo-bot-dashboard">
            <h1><?php esc_html_e('Bot Monitor - Blocage', 'geo-bot-monitor'); ?></h1>

            <div class="geo-bot-block-intro">
                <p><?php esc_html_e('Sélectionnez les bots à bloquer et générez les fichiers de configuration correspondants.', 'geo-bot-monitor'); ?></p>
                <?php 
                $robots_path = self::get_robots_txt_path();
                $robots_exists = file_exists($robots_path);
                if ($robots_exists): 
                    $blocked_in_robots = self::parse_robots_txt();
                ?>
                <div class="geo-bot-sync-notice">
                    <span class="dashicons dashicons-info"></span>
                    <?php printf(
                        esc_html__('Un fichier robots.txt existe avec %d bot(s) bloqué(s).', 'geo-bot-monitor'),
                        count($blocked_in_robots)
                    ); ?>
                    <button type="button" id="geo-bot-sync-robots" class="button button-small">
                        <span class="dashicons dashicons-update"></span>
                        <?php esc_html_e('Synchroniser', 'geo-bot-monitor'); ?>
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <div class="geo-bot-block-grid">
                <div class="geo-bot-block-list">
                    <h2><?php esc_html_e('Bots détectés (30 derniers jours)', 'geo-bot-monitor'); ?></h2>
                    <table class="widefat striped" id="geo-bot-block-table">
                        <thead>
                            <tr>
                                <th class="check-column"><input type="checkbox" id="select-all-bots"></th>
                                <th><?php esc_html_e('Robot', 'geo-bot-monitor'); ?></th>
                                <th><?php esc_html_e('Catégorie', 'geo-bot-monitor'); ?></th>
                                <th><?php esc_html_e('Visites', 'geo-bot-monitor'); ?></th>
                                <th><?php esc_html_e('Statut', 'geo-bot-monitor'); ?></th>
                                <th><?php esc_html_e('Actions', 'geo-bot-monitor'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['by_bot'] as $bot): 
                                $is_blocked_db = isset($blocked_bots[$bot->bot_name]);
                                $is_blocked_robots = self::is_bot_blocked_in_robots($bot->bot_name);
                                $is_blocked = $is_blocked_db || $is_blocked_robots;
                                $is_ai = self::is_ai_bot($bot->bot_name);
                                $is_seo = self::is_seo_bot($bot->bot_name);
                            ?>
                            <tr data-bot="<?php echo esc_attr($bot->bot_name); ?>" data-category="<?php echo esc_attr($bot->bot_category); ?>">
                                <td><input type="checkbox" name="bots[]" value="<?php echo esc_attr($bot->bot_name); ?>" <?php checked($is_blocked); ?>></td>
                                <td>
                                    <strong><?php echo esc_html($bot->bot_name); ?></strong>
                                    <?php if ($is_ai): ?>
                                        <span class="geo-bot-tag geo-bot-tag-ai"><?php esc_html_e('IA', 'geo-bot-monitor'); ?></span>
                                    <?php endif; ?>
                                    <?php if ($is_seo): ?>
                                        <span class="geo-bot-tag geo-bot-tag-seo"><?php esc_html_e('SEO', 'geo-bot-monitor'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="geo-bot-category-badge" style="background: <?php echo esc_attr($category_colors[$bot->bot_category] ?? '#999'); ?>">
                                        <?php echo esc_html($category_labels[$bot->bot_category] ?? $bot->bot_category); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(number_format_i18n($bot->count)); ?></td>
                                <td>
                                    <?php if ($is_blocked): ?>
                                        <span class="geo-bot-status geo-bot-status-blocked">
                                            <?php esc_html_e('Bloqué', 'geo-bot-monitor'); ?>
                                            <?php if ($is_blocked_robots && !$is_blocked_db): ?>
                                                <span class="dashicons dashicons-external" title="<?php esc_attr_e('Détecté dans robots.txt', 'geo-bot-monitor'); ?>"></span>
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="geo-bot-status geo-bot-status-allowed"><?php esc_html_e('Autorisé', 'geo-bot-monitor'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="button geo-bot-quick-block" data-bot="<?php echo esc_attr($bot->bot_name); ?>" data-category="<?php echo esc_attr($bot->bot_category); ?>" title="<?php esc_attr_e('Générer le code de blocage', 'geo-bot-monitor'); ?>">
                                        <span class="dashicons dashicons-shield"></span>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="geo-bot-block-generator">
                    <h2><?php esc_html_e('Générer les fichiers', 'geo-bot-monitor'); ?></h2>
                    
                    <div class="geo-bot-generator-options">
                        <label>
                            <input type="checkbox" id="gen-robots" checked>
                            <?php esc_html_e('robots.txt', 'geo-bot-monitor'); ?>
                        </label>
                        <label>
                            <input type="checkbox" id="gen-llms">
                            <?php esc_html_e('llms.txt (bots IA)', 'geo-bot-monitor'); ?>
                        </label>
                        <label>
                            <input type="checkbox" id="gen-htaccess">
                            <?php esc_html_e('.htaccess', 'geo-bot-monitor'); ?>
                        </label>
                    </div>

                    <button type="button" id="geo-bot-generate-btn" class="button button-primary button-hero">
                        <?php esc_html_e('Générer le code', 'geo-bot-monitor'); ?>
                    </button>

                    <div id="geo-bot-generated-code" style="display: none;">
                        <div class="geo-bot-code-section" id="robots-section">
                            <h3>robots.txt</h3>
                            <p class="description"><?php esc_html_e('Ajoutez ces lignes à votre fichier robots.txt à la racine du site.', 'geo-bot-monitor'); ?></p>
                            <div class="geo-bot-code-block">
                                <pre id="robots-code"></pre>
                                <button type="button" class="button geo-bot-copy-btn" data-target="robots-code">
                                    <span class="dashicons dashicons-clipboard"></span> <?php esc_html_e('Copier', 'geo-bot-monitor'); ?>
                                </button>
                            </div>
                        </div>

                        <div class="geo-bot-code-section" id="llms-section" style="display: none;">
                            <h3>llms.txt</h3>
                            <p class="description"><?php esc_html_e('Créez ce fichier à la racine de votre site pour bloquer les crawlers IA.', 'geo-bot-monitor'); ?></p>
                            <div class="geo-bot-code-block">
                                <pre id="llms-code"></pre>
                                <button type="button" class="button geo-bot-copy-btn" data-target="llms-code">
                                    <span class="dashicons dashicons-clipboard"></span> <?php esc_html_e('Copier', 'geo-bot-monitor'); ?>
                                </button>
                            </div>
                        </div>

                        <div class="geo-bot-code-section" id="htaccess-section" style="display: none;">
                            <h3>.htaccess</h3>
                            <p class="description">
                                <?php esc_html_e('Ajoutez ces lignes à votre fichier .htaccess pour un blocage au niveau serveur.', 'geo-bot-monitor'); ?>
                                <strong class="geo-bot-warning"><?php esc_html_e('Attention: faites une sauvegarde avant modification.', 'geo-bot-monitor'); ?></strong>
                            </p>
                            <div class="geo-bot-code-block">
                                <pre id="htaccess-code"></pre>
                                <button type="button" class="button geo-bot-copy-btn" data-target="htaccess-code">
                                    <span class="dashicons dashicons-clipboard"></span> <?php esc_html_e('Copier', 'geo-bot-monitor'); ?>
                                </button>
                            </div>
                        </div>

                        <div class="geo-bot-download-btns">
                            <button type="button" class="button" id="download-robots-btn">
                                <span class="dashicons dashicons-download"></span> <?php esc_html_e('Télécharger robots.txt', 'geo-bot-monitor'); ?>
                            </button>
                            <button type="button" class="button" id="download-llms-btn" style="display: none;">
                                <span class="dashicons dashicons-download"></span> <?php esc_html_e('Télécharger llms.txt', 'geo-bot-monitor'); ?>
                            </button>
                        </div>
                    </div>

                    <div class="geo-bot-info-box">
                        <h4><?php esc_html_e('Informations', 'geo-bot-monitor'); ?></h4>
                        <ul>
                            <li><?php esc_html_e('Le fichier robots.txt est une directive, tous les bots ne la respectent pas.', 'geo-bot-monitor'); ?></li>
                            <li><?php esc_html_e('Le blocage via .htaccess est plus strict mais consomme des ressources serveur.', 'geo-bot-monitor'); ?></li>
                            <li><?php esc_html_e('Bloquer les bots SEO peut affecter votre référencement.', 'geo-bot-monitor'); ?></li>
                            <li><?php esc_html_e('Le fichier llms.txt est spécifiquement reconnu par les crawlers IA.', 'geo-bot-monitor'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <?php wp_nonce_field('geo_bot_block', 'geo_bot_block_nonce'); ?>
        </div>

        <div id="geo-bot-block-modal" class="geo-bot-modal" style="display: none;">
            <div class="geo-bot-modal-content">
                <div class="geo-bot-modal-header">
                    <h2><span class="dashicons dashicons-shield"></span> <span id="modal-bot-name"></span></h2>
                    <button type="button" class="geo-bot-modal-close">&times;</button>
                </div>
                <div class="geo-bot-modal-body">
                    <div id="modal-bot-info"></div>
                    <div id="modal-code-sections"></div>
                </div>
            </div>
        </div>
        <?php
    }

    public static function ajax_generate_code() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Accès refusé', 'geo-bot-monitor'));
        }

        check_ajax_referer('geo_bot_block', 'nonce');

        $bots = isset($_POST['bots']) ? array_map('sanitize_text_field', wp_unslash($_POST['bots'])) : [];
        $generate_robots = isset($_POST['robots']) && $_POST['robots'] === 'true';
        $generate_llms = isset($_POST['llms']) && $_POST['llms'] === 'true';
        $generate_htaccess = isset($_POST['htaccess']) && $_POST['htaccess'] === 'true';

        if (empty($bots)) {
            wp_send_json_error(['message' => __('Aucun bot sélectionné', 'geo-bot-monitor')]);
        }

        $result = [
            'bots' => $bots,
        ];

        if ($generate_robots) {
            $result['robots'] = self::generate_robots_txt($bots);
        }

        if ($generate_llms) {
            $ai_bots = array_filter($bots, [self::class, 'is_ai_bot']);
            if (!empty($ai_bots)) {
                $result['llms'] = self::generate_llms_txt($ai_bots);
            }
        }

        if ($generate_htaccess) {
            $result['htaccess'] = self::generate_htaccess($bots);
        }

        wp_send_json_success($result);
    }

    public static function ajax_get_bot_code() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Accès refusé', 'geo-bot-monitor'));
        }

        check_ajax_referer('geo_bot_block', 'nonce');

        $bot_name = isset($_POST['bot']) ? sanitize_text_field(wp_unslash($_POST['bot'])) : '';
        $category = isset($_POST['category']) ? sanitize_text_field(wp_unslash($_POST['category'])) : '';

        if (empty($bot_name)) {
            wp_send_json_error(['message' => __('Bot non spécifié', 'geo-bot-monitor')]);
        }

        $is_ai = self::is_ai_bot($bot_name);
        $is_seo = self::is_seo_bot($bot_name);
        $user_agent = self::get_user_agent($bot_name);

        $result = [
            'bot_name' => $bot_name,
            'user_agent' => $user_agent,
            'category' => $category,
            'is_ai' => $is_ai,
            'is_seo' => $is_seo,
            'robots' => self::generate_robots_txt($bot_name),
            'htaccess' => self::generate_htaccess($bot_name),
        ];

        if ($is_ai) {
            $result['llms'] = self::generate_llms_txt($bot_name);
        }

        if ($is_seo) {
            $result['warning'] = __('Attention : bloquer ce bot SEO peut affecter votre référencement.', 'geo-bot-monitor');
        }

        wp_send_json_success($result);
    }

    public static function get_robots_txt_path() {
        return ABSPATH . 'robots.txt';
    }

    public static function parse_robots_txt() {
        $robots_path = self::get_robots_txt_path();
        $blocked_in_robots = [];

        if (!file_exists($robots_path)) {
            return $blocked_in_robots;
        }

        $content = file_get_contents($robots_path);
        if (empty($content)) {
            return $blocked_in_robots;
        }

        $lines = explode("\n", $content);
        $current_agent = null;
        $is_disallow_all = false;

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            if (preg_match('/^User-agent:\s*(.+)$/i', $line, $matches)) {
                if ($current_agent !== null && $is_disallow_all) {
                    $blocked_in_robots[$current_agent] = true;
                }
                $current_agent = trim($matches[1]);
                $is_disallow_all = false;
            } elseif (preg_match('/^Disallow:\s*\/\s*$/i', $line)) {
                $is_disallow_all = true;
            }
        }

        if ($current_agent !== null && $is_disallow_all) {
            $blocked_in_robots[$current_agent] = true;
        }

        return $blocked_in_robots;
    }

    public static function is_bot_blocked_in_robots($bot_name) {
        $blocked_in_robots = self::parse_robots_txt();
        
        if (isset($blocked_in_robots[$bot_name])) {
            return true;
        }

        $user_agent = self::get_user_agent($bot_name);
        if ($user_agent !== $bot_name && isset($blocked_in_robots[$user_agent])) {
            return true;
        }

        foreach ($blocked_in_robots as $agent => $blocked) {
            if (stripos($bot_name, $agent) !== false || stripos($agent, $bot_name) !== false) {
                return true;
            }
        }

        return false;
    }

    public static function get_all_blocked_status() {
        $blocked_in_db = self::get_blocked_bots();
        $blocked_in_robots = self::parse_robots_txt();

        $all_status = [];

        foreach ($blocked_in_db as $bot_name => $info) {
            $all_status[$bot_name] = [
                'blocked_in_db' => true,
                'blocked_in_robots' => self::is_bot_blocked_in_robots($bot_name),
                'methods' => $info['methods'] ?? ['robots'],
                'date' => $info['date'] ?? '',
            ];
        }

        foreach ($blocked_in_robots as $agent => $blocked) {
            if (!isset($all_status[$agent])) {
                $all_status[$agent] = [
                    'blocked_in_db' => false,
                    'blocked_in_robots' => true,
                    'methods' => ['robots'],
                    'date' => '',
                    'external' => true,
                ];
            }
        }

        return $all_status;
    }

    public static function sync_from_robots_txt() {
        $blocked_in_robots = self::parse_robots_txt();
        $blocked_in_db = self::get_blocked_bots();
        $synced = 0;

        foreach ($blocked_in_robots as $agent => $blocked) {
            $matched_bot = null;

            if (isset(self::$known_user_agents[$agent])) {
                $matched_bot = $agent;
            } else {
                foreach (self::$known_user_agents as $bot_name => $ua) {
                    if (strcasecmp($ua, $agent) === 0 || strcasecmp($bot_name, $agent) === 0) {
                        $matched_bot = $bot_name;
                        break;
                    }
                }
            }

            if ($matched_bot && !isset($blocked_in_db[$matched_bot])) {
                self::add_blocked_bot($matched_bot, ['robots']);
                $synced++;
            }
        }

        return $synced;
    }

    public static function ajax_sync_robots() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Acces refuse', 'geo-bot-monitor'));
        }

        check_ajax_referer('geo_bot_block', 'nonce');

        $synced = self::sync_from_robots_txt();
        $blocked_in_robots = self::parse_robots_txt();

        wp_send_json_success([
            'message' => sprintf(
                __('%d bot(s) synchronise(s) depuis robots.txt', 'geo-bot-monitor'),
                $synced
            ),
            'synced' => $synced,
            'robots_content' => $blocked_in_robots,
        ]);
    }
}
