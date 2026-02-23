# GEO Bot Monitor

**Version** : 1.1.0  
**Auteur** : Erwan Tanguy - Ticoët  
**Licence** : GPL2+  
**Compatibilité** : WordPress 5.8+, PHP 7.4+

> Plugin WordPress de surveillance et gestion des robots visitant votre site, avec outils de blocage et intégration GEO.

## Description

**GEO Bot Monitor** surveille, catégorise et permet de bloquer les robots qui visitent votre site WordPress : moteurs de recherche (SEO), IA génératives (GEO), réseaux sociaux, outils SEO et podcasts.

## Fonctionnalités principales

### Surveillance des bots

- **Détection automatique** de 80+ robots avec signatures actualisées
- **Catégorisation** : SEO, GEO/IA, Réseaux sociaux, Outils SEO, Podcast, Interne, Autres
- **Tableau de bord** avec statistiques en temps réel
- **Graphiques d'évolution** par période
- **Historique** des visites par bot et par page

### Catégories de bots détectés

| Catégorie | Exemples |
|-----------|----------|
| **SEO** | Googlebot, Bingbot, Applebot, YandexBot, DuckDuckBot |
| **GEO / IA** | GPTBot, Claude-Web, PerplexityBot, Google-Extended, CCBot |
| **Outils SEO** | MozBot, AhrefsBot, SemrushBot, MJ12bot, Screaming Frog |
| **Réseaux sociaux** | Twitterbot, LinkedInBot, WhatsApp, Discordbot |
| **Podcast** | Podchaser, Spotify, Apple-Podcasts, Overcast |
| **Interne** | WordPress-Cron, GEO-Audit-Bot, Jetpack |

### Blocage des bots

- **Interface de gestion** pour bloquer/autoriser chaque bot
- **Génération automatique** des règles de blocage :
  - `robots.txt` : Directives Disallow
  - `.htaccess` : Règles RewriteCond (blocage serveur)
  - `llms.txt` : Format spécifique IA
- **Application directe** au fichier robots.txt du site
- **Détection** des bots déjà bloqués dans robots.txt existant

### Intégration GEO Authority Suite

Si le plugin **GEO Authority Suite** est installé :
- Synchronisation automatique avec le fichier `llms.txt`
- Ajout des directives de blocage IA dans la section dédiée
- Cohérence entre robots.txt et llms.txt

### Export des données

- **CSV** : Export complet ou par période
- **PDF** : Rapport formaté
- **Markdown** : Format texte structuré
- **API REST** : Accès programmatique aux données

## Installation

1. Téléchargez le plugin
2. Uploadez dans `/wp-content/plugins/geo-bot-monitor/`
3. Activez depuis **Extensions > Extensions installées**
4. Accédez au menu **Bot Monitor** dans l'administration

## Pages d'administration

| Page | Description |
|------|-------------|
| **Tableau de bord** | Vue d'ensemble et statistiques |
| **Liste des bots** | Historique détaillé par robot |
| **Blocage** | Gestion des autorisations et génération de règles |
| **Export** | Export des données (CSV, PDF, Markdown) |
| **Réglages** | Configuration du plugin |

## API REST

Endpoints disponibles :

```
GET /wp-json/geo-bot-monitor/v1/stats
GET /wp-json/geo-bot-monitor/v1/bots
GET /wp-json/geo-bot-monitor/v1/visits
```

## Signatures de bots

Le plugin inclut des signatures pour :

- **Moteurs de recherche** : Google, Bing, Yahoo, Baidu, Yandex, DuckDuckGo
- **Bots IA** : OpenAI (GPTBot), Anthropic (Claude), Perplexity, Google-Extended, Meta
- **Outils SEO** : Moz, Ahrefs, Semrush, Majestic, Screaming Frog
- **Réseaux sociaux** : Twitter, LinkedIn, Facebook, WhatsApp, Discord
- **Podcasts** : Spotify, Apple Podcasts, Overcast, Pocket Casts

## Cas d'utilisation

### Surveiller l'activité IA

Identifiez quels crawlers IA visitent votre site et à quelle fréquence.

### Bloquer les bots indésirables

Bloquez les bots SEO tiers (Moz, Ahrefs, MJ12bot) qui consomment de la bande passante sans apporter de valeur.

### Optimiser pour le GEO

Vérifiez que les bots IA (GPTBot, Claude-Web) accèdent correctement à vos contenus optimisés.

### Mesurer l'impact des optimisations

Comparez l'activité des bots avant/après modifications pour valider l'effet de vos actions GEO.

## Changelog

### Version 1.1.0 (Février 2026)

- **Nouveau** : Système de blocage des bots
- **Nouveau** : Génération de règles robots.txt, .htaccess, llms.txt
- **Nouveau** : Application directe au robots.txt du site
- **Nouveau** : Détection des bots déjà bloqués
- **Nouveau** : Intégration avec GEO Authority Suite (llms.txt)
- **Nouveau** : Catégorie Podcast (Podchaser, Spotify, Apple Podcasts...)
- **Nouveau** : Signatures MozBot et MJ12bot améliorées
- **Amélioration** : Détection Unknown Bot (distinction WP-Cron, internes)
- **Amélioration** : Interface de blocage avec statut en temps réel

### Version 1.0.0 (Janvier 2026)

- Version initiale
- Détection et catégorisation des bots
- Tableau de bord et statistiques
- Export CSV, PDF, Markdown
- API REST

## Ressources

- [Documentation Schema.org](https://schema.org/)
- [Spécification robots.txt](https://developers.google.com/search/docs/crawling-indexing/robots/intro)
- [llms.txt Standard](https://llmstxt.org/)

## Support

**Auteur** : Erwan Tanguy - Ticoët  
**Site** : [ticoet.fr](https://www.ticoet.fr/)  
**Wiki** : [wiki.ticoet.me](https://wiki.ticoet.me/doku.php?id=geo-bot-monitor)
