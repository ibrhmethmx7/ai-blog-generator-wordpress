<?php
/**
 * AI Blog Generator - Kurulum Scripti
 * Bu script plugin kurulumunu ve temel testleri yapar
 */

// WordPress environment check
if (!defined('ABSPATH')) {
    echo "Bu script sadece WordPress ortamında çalışır.\n";
    exit;
}

class AIBlogInstaller {
    
    private $requirements = array(
        'php_version' => '7.4',
        'wp_version' => '5.0',
        'extensions' => array('curl', 'json', 'openssl')
    );
    
    public function __construct() {
        echo "AI Blog Generator Kurulum Başlatılıyor...\n\n";
    }
    
    public function run() {
        if ($this->check_requirements()) {
            $this->install_plugin();
            $this->setup_database();
            $this->test_installation();
            echo "\n✅ Kurulum başarıyla tamamlandı!\n";
        } else {
            echo "\n❌ Kurulum başarısız. Gereksinimler karşılanmıyor.\n";
        }
    }
    
    private function check_requirements() {
        echo "🔍 Sistem gereksinimleri kontrol ediliyor...\n";
        
        // PHP version check
        if (version_compare(PHP_VERSION, $this->requirements['php_version'], '<')) {
            echo "❌ PHP {$this->requirements['php_version']} veya üzeri gerekli. Mevcut: " . PHP_VERSION . "\n";
            return false;
        }
        echo "✅ PHP Version: " . PHP_VERSION . "\n";
        
        // WordPress version check
        global $wp_version;
        if (version_compare($wp_version, $this->requirements['wp_version'], '<')) {
            echo "❌ WordPress {$this->requirements['wp_version']} veya üzeri gerekli. Mevcut: " . $wp_version . "\n";
            return false;
        }
        echo "✅ WordPress Version: " . $wp_version . "\n";
        
        // Extension checks
        foreach ($this->requirements['extensions'] as $extension) {
            if (!extension_loaded($extension)) {
                echo "❌ PHP {$extension} extension gerekli\n";
                return false;
            }
            echo "✅ PHP {$extension} extension yüklü\n";
        }
        
        // File permissions check
        $upload_dir = wp_upload_dir();
        if (!is_writable($upload_dir['basedir'])) {
            echo "❌ Upload dizini yazılabilir değil: {$upload_dir['basedir']}\n";
            return false;
        }
        echo "✅ Upload dizini yazılabilir\n";
        
        return true;
    }
    
    private function install_plugin() {
        echo "\n📦 Plugin kurulum ayarları yapılandırılıyor...\n";
        
        // Default options
        $default_options = array(
            'aiblog_openai_key' => '',
            'aiblog_default_category' => 1,
            'aiblog_default_author' => get_current_user_id(),
            'aiblog_version' => '1.0.0',
            'aiblog_install_date' => current_time('mysql')
        );
        
        foreach ($default_options as $option => $value) {
            if (!get_option($option)) {
                add_option($option, $value);
                echo "✅ Option eklendi: {$option}\n";
            }
        }
        
        // Create tables if needed (future expansion)
        $this->create_tables();
    }
    
    private function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aiblog_logs';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            action varchar(50) NOT NULL,
            topic varchar(255) NOT NULL,
            status varchar(20) NOT NULL,
            response_time float,
            error_message text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        echo "✅ Veritabanı tabloları oluşturuldu\n";
    }
    
    private function setup_database() {
        echo "\n💾 Veritabanı kurulumu kontrol ediliyor...\n";
        
        global $wpdb;
        
        // Check if tables exist
        $table_name = $wpdb->prefix . 'aiblog_logs';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if ($table_exists) {
            echo "✅ Log tablosu mevcut\n";
        } else {
            echo "❌ Log tablosu bulunamadı, yeniden oluşturuluyor...\n";
            $this->create_tables();
        }
        
        // Test database connection
        if ($wpdb->last_error) {
            echo "❌ Veritabanı hatası: " . $wpdb->last_error . "\n";
            return false;
        }
        
        echo "✅ Veritabanı bağlantısı başarılı\n";
        return true;
    }
    
    private function test_installation() {
        echo "\n🧪 Kurulum testleri çalıştırılıyor...\n";
        
        // Test plugin activation
        if (!is_plugin_active('ai-blog-generator/ai-blog-generator.php')) {
            echo "⚠️  Plugin henüz aktifleştirilmemiş\n";
        } else {
            echo "✅ Plugin aktif\n";
        }
        
        // Test admin menu
        if (function_exists('add_menu_page')) {
            echo "✅ WordPress admin fonksiyonları erişilebilir\n";
        }
        
        // Test AJAX endpoints
        $ajax_actions = array('generate_ai_blog', 'publish_ai_blog');
        foreach ($ajax_actions as $action) {
            if (has_action("wp_ajax_$action")) {
                echo "✅ AJAX action kayıtlı: $action\n";
            } else {
                echo "❌ AJAX action eksik: $action\n";
            }
        }
        
        // Test file permissions
        $plugin_dir = plugin_dir_path(__FILE__);
        if (is_readable($plugin_dir . 'assets/admin.css')) {
            echo "✅ CSS dosyası erişilebilir\n";
        } else {
            echo "❌ CSS dosyası okunamıyor\n";
        }
        
        if (is_readable($plugin_dir . 'assets/admin.js')) {
            echo "✅ JavaScript dosyası erişilebilir\n";
        } else {
            echo "❌ JavaScript dosyası okunamıyor\n";
        }
    }
    
    public function uninstall() {
        echo "🗑️  Plugin kaldırılıyor...\n";
        
        // Remove options
        $options = array(
            'aiblog_openai_key',
            'aiblog_default_category',
            'aiblog_default_author',
            'aiblog_version',
            'aiblog_install_date'
        );
        
        foreach ($options as $option) {
            delete_option($option);
            echo "✅ Option silindi: $option\n";
        }
        
        // Drop tables
        global $wpdb;
        $table_name = $wpdb->prefix . 'aiblog_logs';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        echo "✅ Log tablosu silindi\n";
        
        echo "✅ Plugin tamamen kaldırıldı\n";
    }
}

// CLI usage check
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('aiblog install', function() {
        $installer = new AIBlogInstaller();
        $installer->run();
    });
    
    WP_CLI::add_command('aiblog uninstall', function() {
        $installer = new AIBlogInstaller();
        $installer->uninstall();
    });
}

// Web usage (for testing)
if (isset($_GET['action']) && current_user_can('manage_options')) {
    $installer = new AIBlogInstaller();
    
    switch ($_GET['action']) {
        case 'install':
            $installer->run();
            break;
        case 'uninstall':
            $installer->uninstall();
            break;
        default:
            echo "Geçerli actionlar: install, uninstall\n";
    }
}
?>
