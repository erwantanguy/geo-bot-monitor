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
            'MJ12bot' => ['MJ12bot'],
            'AhrefsBot' => ['AhrefsBot'],
            'SemrushBot' => ['SemrushBot'],
            'DotBot' => ['DotBot'],
            'Screaming Frog' => ['Screaming Frog'],
            'SEOkicks' => ['SEOkicks'],
            'Seobility' => ['Seobility'],
            'Sistrix' => ['SISTRIX'],
            'Moz' => ['rogerbot', 'Moz'],
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
        ]
    ];
}

function geo_bot_get_category_labels() {
    return [
        'seo' => __('SEO', 'geo-bot-monitor'),
        'geo_ai' => __('GEO / IA', 'geo-bot-monitor'),
        'social' => __('Réseaux sociaux', 'geo-bot-monitor'),
        'other' => __('Autres', 'geo-bot-monitor'),
    ];
}

function geo_bot_get_category_colors() {
    return [
        'seo' => '#4285f4',
        'geo_ai' => '#ea4335',
        'social' => '#fbbc05',
        'other' => '#34a853',
    ];
}
