<?php
/**
 * AI Blog Generator - Örnek Konfigürasyon
 * Bu dosyayı config.php olarak kopyalayın ve değerleri güncelleyin
 */

// Plugin varsayılan ayarları
define('AIBLOG_DEFAULT_SETTINGS', array(
    'openai_model' => 'gpt-3.5-turbo',
    'max_tokens' => 2000,
    'temperature' => 0.7,
    'default_style' => 'formal',
    'default_length' => 'medium',
    'auto_publish' => false,
    'default_category' => 1,
    'default_author' => 1
));

// API ayarları
define('AIBLOG_API_SETTINGS', array(
    'timeout' => 120,
    'retry_attempts' => 3,
    'rate_limit' => 60 // dakika başına istek sayısı
));

// Güvenlik ayarları
define('AIBLOG_SECURITY', array(
    'encrypt_api_key' => true,
    'require_ssl' => false, // Production'da true yapın
    'allowed_roles' => array('administrator'),
    'nonce_lifetime' => 3600 // 1 saat
));

// İçerik filtreleri
define('AIBLOG_CONTENT_FILTERS', array(
    'min_topic_length' => 3,
    'max_topic_length' => 200,
    'allowed_html_tags' => 'p,strong,em,ul,ol,li,h2,h3,h4,a,br',
    'auto_format' => true
));

// Log ayarları
define('AIBLOG_LOGGING', array(
    'enable_logging' => true,
    'log_level' => 'info', // debug, info, warning, error
    'log_retention_days' => 30
));
?>
