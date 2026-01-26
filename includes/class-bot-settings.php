<?php
if (!defined('ABSPATH')) {
    exit;
}

class GEO_Bot_Settings {

    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings() {
        register_setting('geo_bot_monitor_settings', 'geo_bot_monitor_api_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ]);
    }

    public function render() {
        $api_key = get_option('geo_bot_monitor_api_key', '');
        $site_url = home_url();
        $nonce = wp_create_nonce('geo_bot_generate_key');
        ?>
        <div class="wrap geo-bot-dashboard">
            <h1><?php esc_html_e('Bot Monitor - Paramètres API', 'geo-bot-monitor'); ?></h1>

            <div class="geo-bot-settings-section">
                <h2><?php esc_html_e('Clé API', 'geo-bot-monitor'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Cette clé permet aux applications externes de se connecter à votre site pour récupérer les données de Bot Monitor.', 'geo-bot-monitor'); ?>
                </p>

                <form method="post" action="options.php">
                    <?php settings_fields('geo_bot_monitor_settings'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="geo_bot_monitor_api_key"><?php esc_html_e('Clé API', 'geo-bot-monitor'); ?></label>
                            </th>
                            <td>
                                <div class="geo-bot-api-key-field">
                                    <input type="text" 
                                           id="geo_bot_monitor_api_key" 
                                           name="geo_bot_monitor_api_key" 
                                           value="<?php echo esc_attr($api_key); ?>" 
                                           class="regular-text code"
                                           readonly>
                                    <button type="button" id="generate-api-key" class="button" data-nonce="<?php echo esc_attr($nonce); ?>">
                                        <?php esc_html_e('Générer une nouvelle clé', 'geo-bot-monitor'); ?>
                                    </button>
                                    <button type="button" id="copy-api-key" class="button" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                                        <?php esc_html_e('Copier', 'geo-bot-monitor'); ?>
                                    </button>
                                </div>
                                <p class="description">
                                    <?php esc_html_e('Gardez cette clé secrète. Toute personne ayant accès à cette clé peut consulter vos données de trafic.', 'geo-bot-monitor'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button(esc_html__('Enregistrer', 'geo-bot-monitor')); ?>
                </form>
            </div>

            <div class="geo-bot-settings-section">
                <h2><?php esc_html_e('Endpoints API', 'geo-bot-monitor'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Voici les endpoints disponibles pour votre application externe.', 'geo-bot-monitor'); ?>
                </p>

                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Endpoint', 'geo-bot-monitor'); ?></th>
                            <th><?php esc_html_e('Méthode', 'geo-bot-monitor'); ?></th>
                            <th><?php esc_html_e('Description', 'geo-bot-monitor'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code><?php echo esc_html($site_url); ?>/wp-json/geo-bot-monitor/v1/ping</code></td>
                            <td><span class="geo-bot-method get">GET</span></td>
                            <td><?php esc_html_e('Test de connexion (sans authentification)', 'geo-bot-monitor'); ?></td>
                        </tr>
                        <tr>
                            <td><code><?php echo esc_html($site_url); ?>/wp-json/geo-bot-monitor/v1/auth</code></td>
                            <td><span class="geo-bot-method post">POST</span></td>
                            <td><?php esc_html_e('Vérifier la validité de la clé API', 'geo-bot-monitor'); ?></td>
                        </tr>
                        <tr>
                            <td><code><?php echo esc_html($site_url); ?>/wp-json/geo-bot-monitor/v1/stats</code></td>
                            <td><span class="geo-bot-method get">GET</span></td>
                            <td><?php esc_html_e('Statistiques globales (total, par catégorie, par jour)', 'geo-bot-monitor'); ?></td>
                        </tr>
                        <tr>
                            <td><code><?php echo esc_html($site_url); ?>/wp-json/geo-bot-monitor/v1/visits</code></td>
                            <td><span class="geo-bot-method get">GET</span></td>
                            <td><?php esc_html_e('Liste paginée des visites', 'geo-bot-monitor'); ?></td>
                        </tr>
                        <tr>
                            <td><code><?php echo esc_html($site_url); ?>/wp-json/geo-bot-monitor/v1/compare</code></td>
                            <td><span class="geo-bot-method get">GET</span></td>
                            <td><?php esc_html_e('Comparer deux périodes', 'geo-bot-monitor'); ?></td>
                        </tr>
                        <tr>
                            <td><code><?php echo esc_html($site_url); ?>/wp-json/geo-bot-monitor/v1/bots</code></td>
                            <td><span class="geo-bot-method get">GET</span></td>
                            <td><?php esc_html_e('Liste des robots détectables', 'geo-bot-monitor'); ?></td>
                        </tr>
                        <tr>
                            <td><code><?php echo esc_html($site_url); ?>/wp-json/geo-bot-monitor/v1/categories</code></td>
                            <td><span class="geo-bot-method get">GET</span></td>
                            <td><?php esc_html_e('Liste des catégories', 'geo-bot-monitor'); ?></td>
                        </tr>
                        <tr>
                            <td><code><?php echo esc_html($site_url); ?>/wp-json/geo-bot-monitor/v1/database</code></td>
                            <td><span class="geo-bot-method get">GET</span></td>
                            <td><?php esc_html_e('Informations sur la base de données', 'geo-bot-monitor'); ?></td>
                        </tr>
                        <tr>
                            <td><code><?php echo esc_html($site_url); ?>/wp-json/geo-bot-monitor/v1/months</code></td>
                            <td><span class="geo-bot-method get">GET</span></td>
                            <td><?php esc_html_e('Mois disponibles avec le nombre de visites', 'geo-bot-monitor'); ?></td>
                        </tr>
                        <tr>
                            <td><code><?php echo esc_html($site_url); ?>/wp-json/geo-bot-monitor/v1/export</code></td>
                            <td><span class="geo-bot-method get">GET</span></td>
                            <td><?php esc_html_e('Exporter les données (JSON ou CSV)', 'geo-bot-monitor'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="geo-bot-settings-section">
                <h2><?php esc_html_e('Authentification', 'geo-bot-monitor'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Pour authentifier vos requêtes, utilisez l\'une des méthodes suivantes :', 'geo-bot-monitor'); ?>
                </p>

                <h4><?php esc_html_e('Option 1 : Header HTTP (recommandé)', 'geo-bot-monitor'); ?></h4>
                <pre class="geo-bot-code-block">X-GEO-Bot-API-Key: <?php echo esc_html($api_key ?: 'VOTRE_CLE_API'); ?></pre>

                <h4><?php esc_html_e('Option 2 : Paramètre URL', 'geo-bot-monitor'); ?></h4>
                <pre class="geo-bot-code-block"><?php echo esc_html($site_url); ?>/wp-json/geo-bot-monitor/v1/stats?api_key=<?php echo esc_html($api_key ?: 'VOTRE_CLE_API'); ?></pre>
            </div>

            <div class="geo-bot-settings-section">
                <h2><?php esc_html_e('Exemples de requêtes', 'geo-bot-monitor'); ?></h2>

                <h4><?php esc_html_e('cURL - Obtenir les statistiques des 30 derniers jours', 'geo-bot-monitor'); ?></h4>
                <pre class="geo-bot-code-block">curl -H "X-GEO-Bot-API-Key: <?php echo esc_html($api_key ?: 'VOTRE_CLE_API'); ?>" \
  "<?php echo esc_html($site_url); ?>/wp-json/geo-bot-monitor/v1/stats"</pre>

                <h4><?php esc_html_e('cURL - Comparer deux périodes', 'geo-bot-monitor'); ?></h4>
                <pre class="geo-bot-code-block">curl -H "X-GEO-Bot-API-Key: <?php echo esc_html($api_key ?: 'VOTRE_CLE_API'); ?>" \
  "<?php echo esc_html($site_url); ?>/wp-json/geo-bot-monitor/v1/compare?period1_start=2025-01-01&period1_end=2025-01-15&period2_start=2025-01-16&period2_end=2025-01-31"</pre>

                <h4><?php esc_html_e('JavaScript (fetch)', 'geo-bot-monitor'); ?></h4>
                <pre class="geo-bot-code-block">const response = await fetch('<?php echo esc_html($site_url); ?>/wp-json/geo-bot-monitor/v1/stats', {
  headers: {
    'X-GEO-Bot-API-Key': '<?php echo esc_html($api_key ?: 'VOTRE_CLE_API'); ?>'
  }
});
const data = await response.json();</pre>
            </div>
        </div>

        <style>
            .geo-bot-settings-section {
                background: #fff;
                padding: 20px 30px;
                border-radius: 8px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                margin-bottom: 20px;
            }
            .geo-bot-settings-section h2 {
                margin-top: 0;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
            }
            .geo-bot-api-key-field {
                display: flex;
                gap: 10px;
                align-items: center;
            }
            .geo-bot-api-key-field input {
                font-family: monospace;
            }
            .geo-bot-method {
                display: inline-block;
                padding: 2px 8px;
                border-radius: 3px;
                font-size: 0.85em;
                font-weight: 600;
                color: #fff;
            }
            .geo-bot-method.get { background: #61affe; }
            .geo-bot-method.post { background: #49cc90; }
            .geo-bot-code-block {
                background: #23282d;
                color: #eee;
                padding: 15px;
                border-radius: 5px;
                overflow-x: auto;
                font-size: 13px;
                line-height: 1.5;
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            $('#generate-api-key').on('click', function() {
                var btn = $(this);
                var nonce = btn.data('nonce');
                
                btn.prop('disabled', true).text('<?php echo esc_js(__('Génération...', 'geo-bot-monitor')); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'geo_bot_generate_api_key',
                        nonce: nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#geo_bot_monitor_api_key').val(response.data.key);
                            $('#copy-api-key').prop('disabled', false);
                        } else {
                            alert('<?php echo esc_js(__('Erreur lors de la génération', 'geo-bot-monitor')); ?>');
                        }
                    },
                    error: function() {
                        alert('<?php echo esc_js(__('Erreur de connexion', 'geo-bot-monitor')); ?>');
                    },
                    complete: function() {
                        btn.prop('disabled', false).text('<?php echo esc_js(__('Générer une nouvelle clé', 'geo-bot-monitor')); ?>');
                    }
                });
            });

            $('#copy-api-key').on('click', function() {
                var key = $('#geo_bot_monitor_api_key').val();
                navigator.clipboard.writeText(key).then(function() {
                    var btn = $('#copy-api-key');
                    var originalText = btn.text();
                    btn.text('<?php echo esc_js(__('Copié !', 'geo-bot-monitor')); ?>');
                    setTimeout(function() {
                        btn.text(originalText);
                    }, 2000);
                });
            });
        });
        </script>
        <?php
    }
}
