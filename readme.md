# GEO Bot Monitor

Plugin WordPress pour surveiller les visites des robots SEO et GEO/AI sur votre site.

## Fonctionnalités

- **Détection automatique** de 50+ robots (Googlebot, Bingbot, GPTBot, Claude-Web, PerplexityBot...)
- **Catégorisation** : SEO, GEO/IA, Réseaux sociaux, Autres
- **Tableau de bord** avec statistiques en temps réel et graphiques
- **Comparaison de périodes** pour mesurer l'impact d'une action
- **Exports** : CSV (Excel), PDF, Markdown
- **API REST** pour connexion avec applications externes
- **Maintenance** : purge des données anciennes

## Installation

1. Télécharger le dossier `geo-bot-monitor`
2. Le copier dans `wp-content/plugins/`
3. Activer le plugin dans l'admin WordPress
4. Accéder au menu **Bot Monitor**

## Configuration API

Pour connecter une application externe (comme GEO Bot Dashboard) :

1. Aller dans **Bot Monitor > API**
2. Cliquer sur **Générer une nouvelle clé**
3. Enregistrer
4. Utiliser cette clé dans votre application

## Endpoints API

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/wp-json/geo-bot-monitor/v1/ping` | GET | Test de connexion (sans auth) |
| `/wp-json/geo-bot-monitor/v1/auth` | POST | Vérifier la clé API |
| `/wp-json/geo-bot-monitor/v1/stats` | GET | Statistiques globales |
| `/wp-json/geo-bot-monitor/v1/visits` | GET | Liste paginée des visites |
| `/wp-json/geo-bot-monitor/v1/compare` | GET | Comparer deux périodes |
| `/wp-json/geo-bot-monitor/v1/bots` | GET | Liste des robots détectables |
| `/wp-json/geo-bot-monitor/v1/categories` | GET | Liste des catégories |
| `/wp-json/geo-bot-monitor/v1/database` | GET | Infos base de données |
| `/wp-json/geo-bot-monitor/v1/months` | GET | Mois disponibles |
| `/wp-json/geo-bot-monitor/v1/export` | GET | Export JSON ou CSV |

### Authentification

**Header HTTP (recommandé) :**
```
X-GEO-Bot-API-Key: votre_cle_api
```

**Paramètre URL :**
```
?api_key=votre_cle_api
```

### Exemples

```bash
# Statistiques des 30 derniers jours
curl -H "X-GEO-Bot-API-Key: gbm_xxx" \
  "https://example.com/wp-json/geo-bot-monitor/v1/stats"

# Statistiques personnalisées
curl -H "X-GEO-Bot-API-Key: gbm_xxx" \
  "https://example.com/wp-json/geo-bot-monitor/v1/stats?start_date=2025-01-01&end_date=2025-01-31"

# Comparer deux périodes
curl -H "X-GEO-Bot-API-Key: gbm_xxx" \
  "https://example.com/wp-json/geo-bot-monitor/v1/compare?period1_start=2025-01-01&period1_end=2025-01-15&period2_start=2025-01-16&period2_end=2025-01-31"
```

## Robots détectés

### SEO
Googlebot, Bingbot, YandexBot, Baiduspider, DuckDuckBot, Applebot, AhrefsBot, SemrushBot, MJ12bot, Screaming Frog, SEOkicks, Sistrix, Moz...

### GEO / IA
GPTBot, ChatGPT-User, Claude-Web, ClaudeBot, PerplexityBot, Google-Extended, Bytespider, CCBot, PetalBot, YouBot, Amazonbot, Meta-ExternalAgent...

### Réseaux sociaux
Twitterbot, LinkedInBot, Pinterest, Slackbot, TelegramBot, WhatsApp, Discordbot...

### Autres
UptimeRobot, Pingdom, GTmetrix, Lighthouse, Archive.org...

## Structure

```
geo-bot-monitor/
├── geo-bot-monitor.php         # Fichier principal
├── includes/
│   ├── bot-signatures.php      # Signatures des robots
│   ├── class-bot-api.php       # API REST
│   ├── class-bot-dashboard.php # Pages admin
│   ├── class-bot-detector.php  # Détection des robots
│   ├── class-bot-exporter.php  # Exports CSV/PDF/MD
│   ├── class-bot-logger.php    # Logging en BDD
│   └── class-bot-settings.php  # Page paramètres API
├── assets/
│   ├── css/admin.css           # Styles admin
│   └── js/admin.js             # Scripts admin
└── readme.md
```

## Base de données

Table `{prefix}_geo_bot_visits` :

| Colonne | Type | Description |
|---------|------|-------------|
| id | BIGINT | ID unique |
| visit_date | DATETIME | Date/heure de visite |
| bot_name | VARCHAR(100) | Nom du robot |
| bot_category | VARCHAR(20) | Catégorie (seo, geo_ai, social, other) |
| user_agent | TEXT | User-Agent complet |
| ip_address | VARCHAR(45) | Adresse IP |
| url_visited | TEXT | URL visitée |
| http_status | SMALLINT | Code HTTP |
| response_time | FLOAT | Temps de réponse (s) |

## Désinstallation

La désinstallation du plugin supprime automatiquement :
- La table `{prefix}_geo_bot_visits`
- Les options `geo_bot_monitor_db_version` et `geo_bot_monitor_api_key`

## Changelog

### 1.0.1
- Corrections de sécurité (sanitization, escaping)
- Génération de clé API côté serveur
- Ajout index composite pour performance
- Nettoyage à la désinstallation

### 1.0.0
- Version initiale

## Licence

GPLv2 or later

## Auteur

Erwan Tanguy
