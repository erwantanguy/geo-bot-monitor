<?php
if (!defined('ABSPATH')) {
    exit;
}

function geo_bot_get_signatures() {
    return [
        'seo' => [
            'Googlebot' => ['Googlebot/', 'Googlebot-Image/', 'Googlebot-News/', 'Googlebot-Video/'],
            'Googlebot-Mobile' => ['Googlebot-Mobile'],
            'Google-InspectionTool' => ['Google-InspectionTool'],
            'Bingbot' => ['bingbot/', 'BingPreview/'],
            'YandexBot' => ['YandexBot/', 'YandexImages/', 'YandexMobileBot/'],
            'Baiduspider' => ['Baiduspider', 'Baiduspider-image'],
            'DuckDuckBot' => ['DuckDuckBot/', 'DuckDuckGo-Favicons-Bot'],
            'Applebot' => ['Applebot/'],
            'Sogou' => ['Sogou web spider', 'Sogou inst spider'],
            'Exabot' => ['Exabot'],
            'facebot' => ['facebot', 'facebookexternalhit'],
            'ia_archiver' => ['ia_archiver'],
        ],
        'seo_tools' => [
            'MozBot' => ['rogerbot', 'DotBot/1.0; http://www.opensiteexplorer.org', 'Moz.com'],
            'Moz' => ['rogerbot', 'Moz'],
            'MJ12bot' => ['MJ12bot'],
            'AhrefsBot' => ['AhrefsBot'],
            'SemrushBot' => ['SemrushBot'],
            'DotBot' => ['DotBot'],
            'Screaming Frog' => ['Screaming Frog'],
            'SEOkicks' => ['SEOkicks'],
            'Seobility' => ['Seobility'],
            'Sistrix' => ['SISTRIX'],
            'Majestic' => ['MJ12bot', 'Majestic'],
            'Serpstat' => ['SerpstatBot'],
            'Ubersuggest' => ['Ubersuggest'],
        ],
        'geo_ai' => [
            'GPTBot' => ['GPTBot'],
            'ChatGPT-User' => ['ChatGPT-User'],
            'Claude-Web' => ['Claude-Web', 'ClaudeBot'],
            'Anthropic' => ['anthropic-ai'],
            'PerplexityBot' => ['PerplexityBot'],
            'Google-Extended' => ['Google-Extended'],
            'Cohere-ai' => ['cohere-ai'],
            'CCBot' => ['CCBot'],
            'Bytespider' => ['Bytespider'],
            'PetalBot' => ['PetalBot'],
            'YouBot' => ['YouBot'],
            'Diffbot' => ['Diffbot'],
            'OAI-SearchBot' => ['OAI-SearchBot'],
            'Meta-ExternalAgent' => ['Meta-ExternalAgent'],
            'Meta-ExternalFetcher' => ['Meta-ExternalFetcher'],
            'Amazonbot' => ['Amazonbot'],
            'ImagesiftBot' => ['ImagesiftBot'],
            'Omgili' => ['omgili', 'omgilibot'],
            'Webz.io' => ['webzio'],
            'AI2Bot' => ['AI2Bot'],
            'Applebot-Extended' => ['Applebot-Extended'],
        ],
        'social' => [
            'Twitterbot' => ['Twitterbot'],
            'LinkedInBot' => ['LinkedInBot'],
            'Pinterest' => ['Pinterest', 'Pinterestbot'],
            'Slackbot' => ['Slackbot'],
            'TelegramBot' => ['TelegramBot'],
            'WhatsApp' => ['WhatsApp'],
            'Discordbot' => ['Discordbot'],
            'Snapchat' => ['Snapchat'],
        ],
        'podcast' => [
            'Podchaser' => ['Podchaser'],
            'Podplay' => ['Podplay'],
            'PodcastAddict' => ['PodcastAddict', 'Podcast Addict'],
            'Overcast' => ['Overcast'],
            'Castro' => ['Castro'],
            'Pocket Casts' => ['PocketCasts', 'Pocket Casts'],
            'Spotify-Podcast' => ['Spotify'],
            'Apple-Podcasts' => ['AppleCoreMedia', 'iTunes'],
            'Google-Podcasts' => ['GooglePodcasts', 'Google-Podcast'],
            'Deezer' => ['Deezer'],
            'Castbox' => ['Castbox'],
            'Stitcher' => ['Stitcher'],
            'iHeartRadio' => ['iHeartRadio'],
            'TuneIn' => ['TuneIn'],
            'Podcast Republic' => ['Podcast Republic'],
            'Podbean' => ['Podbean'],
            'Audioboom' => ['Audioboom'],
            'Spreaker' => ['Spreaker'],
        ],
        'internal' => [
            'WordPress-Cron' => ['WordPress/'],
            'GEO-Audit-Bot' => ['GEO-Audit-Bot'],
            'WP-REST' => ['wp-rest'],
            'Jetpack' => ['Jetpack'],
        ],
        'other' => [
            'Uptimerobot' => ['UptimeRobot'],
            'Pingdom' => ['Pingdom'],
            'StatusCake' => ['StatusCake'],
            'Netcraft' => ['Netcraft'],
            'W3C_Validator' => ['W3C_Validator'],
            'Validator.nu' => ['Validator.nu'],
            'GTmetrix' => ['GTmetrix'],
            'WebPageTest' => ['WebPageTest'],
            'Lighthouse' => ['Chrome-Lighthouse'],
            'Archive.org' => ['archive.org_bot'],
            'Feedfetcher' => ['Feedfetcher'],
            'curl' => ['curl/'],
            'wget' => ['Wget/'],
            'Python-Requests' => ['python-requests'],
            'Axios' => ['axios/'],
            'Node-Fetch' => ['node-fetch'],
        ]
    ];
}

function geo_bot_get_category_labels() {
    return [
        'seo' => __('SEO', 'geo-bot-monitor'),
        'seo_tools' => __('Outils SEO', 'geo-bot-monitor'),
        'geo_ai' => __('GEO / IA', 'geo-bot-monitor'),
        'social' => __('Réseaux sociaux', 'geo-bot-monitor'),
        'podcast' => __('Podcast', 'geo-bot-monitor'),
        'internal' => __('Interne', 'geo-bot-monitor'),
        'other' => __('Autres', 'geo-bot-monitor'),
    ];
}

function geo_bot_get_category_colors() {
    return [
        'seo' => '#4285f4',
        'seo_tools' => '#9c27b0',
        'geo_ai' => '#ea4335',
        'social' => '#fbbc05',
        'podcast' => '#ff5722',
        'internal' => '#607d8b',
        'other' => '#34a853',
    ];
}

function geo_bot_get_category_recommendations() {
    return [
        'seo' => [
            'status' => 'keep',
            'icon' => '✅',
            'label' => __('Garder', 'geo-bot-monitor'),
            'description' => __('Essentiel pour le référencement', 'geo-bot-monitor'),
        ],
        'seo_tools' => [
            'status' => 'evaluate',
            'icon' => '⚠️',
            'label' => __('À évaluer', 'geo-bot-monitor'),
            'description' => __('Utile si vous utilisez ces outils, sinon peut être bloqué', 'geo-bot-monitor'),
        ],
        'geo_ai' => [
            'status' => 'evaluate',
            'icon' => '🔶',
            'label' => __('Selon stratégie', 'geo-bot-monitor'),
            'description' => __('Décidez si vous voulez être indexé par les IA', 'geo-bot-monitor'),
        ],
        'social' => [
            'status' => 'keep',
            'icon' => '✅',
            'label' => __('Garder', 'geo-bot-monitor'),
            'description' => __('Nécessaire pour les previews de liens', 'geo-bot-monitor'),
        ],
        'podcast' => [
            'status' => 'keep',
            'icon' => '✅',
            'label' => __('Garder', 'geo-bot-monitor'),
            'description' => __('Essentiel pour la diffusion des podcasts', 'geo-bot-monitor'),
        ],
        'internal' => [
            'status' => 'keep',
            'icon' => '✅',
            'label' => __('Garder', 'geo-bot-monitor'),
            'description' => __('Requêtes internes WordPress', 'geo-bot-monitor'),
        ],
        'other' => [
            'status' => 'evaluate',
            'icon' => '❓',
            'label' => __('À analyser', 'geo-bot-monitor'),
            'description' => __('Vérifier l\'utilité au cas par cas', 'geo-bot-monitor'),
        ],
    ];
}
