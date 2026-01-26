<?php
if (!defined('ABSPATH')) {
    exit;
}

class GEO_Bot_Detector {

    private $signatures;

    public function __construct() {
        $this->signatures = geo_bot_get_signatures();
    }

    public function detect() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (empty($user_agent)) {
            return null;
        }

        foreach ($this->signatures as $category => $bots) {
            foreach ($bots as $bot_name => $patterns) {
                foreach ($patterns as $pattern) {
                    if (stripos($user_agent, $pattern) !== false) {
                        return [
                            'bot_name' => $bot_name,
                            'bot_category' => $category,
                            'user_agent' => $user_agent,
                            'ip_address' => $this->get_client_ip(),
                            'url_visited' => $this->get_current_url(),
                        ];
                    }
                }
            }
        }

        if ($this->looks_like_bot($user_agent)) {
            return [
                'bot_name' => 'Unknown Bot',
                'bot_category' => 'other',
                'user_agent' => $user_agent,
                'ip_address' => $this->get_client_ip(),
                'url_visited' => $this->get_current_url(),
            ];
        }

        return null;
    }

    private function looks_like_bot($user_agent) {
        $bot_indicators = [
            'bot', 'crawler', 'spider', 'scraper', 'fetch',
            'http', 'curl', 'wget', 'python', 'java/',
            'Apache-HttpClient', 'Go-http-client', 'libwww'
        ];

        $ua_lower = strtolower($user_agent);
        
        foreach ($bot_indicators as $indicator) {
            if (strpos($ua_lower, strtolower($indicator)) !== false) {
                return true;
            }
        }

        return false;
    }

    private function get_client_ip() {
        $ip_keys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    private function get_current_url() {
        $protocol = is_ssl() ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        return $protocol . $host . $uri;
    }

    public function verify_bot($bot_name, $ip) {
        if (in_array($bot_name, ['Googlebot', 'Googlebot-Mobile', 'Googlebot-Image'])) {
            return $this->verify_google_bot($ip);
        }

        if ($bot_name === 'Bingbot') {
            return $this->verify_bing_bot($ip);
        }

        return null;
    }

    private function verify_google_bot($ip) {
        $host = gethostbyaddr($ip);
        if ($host && (
            str_ends_with($host, '.googlebot.com') ||
            str_ends_with($host, '.google.com')
        )) {
            $resolved_ip = gethostbyname($host);
            return $resolved_ip === $ip;
        }
        return false;
    }

    private function verify_bing_bot($ip) {
        $host = gethostbyaddr($ip);
        if ($host && str_ends_with($host, '.search.msn.com')) {
            $resolved_ip = gethostbyname($host);
            return $resolved_ip === $ip;
        }
        return false;
    }
}
