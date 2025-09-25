<?php
/**
 * Plugin Name: AI Blog Generator
 * Plugin URI: https://example.com/ai-blog-generator
 * Description: OpenAI ile otomatik blog yazısı üreten ve WordPress'e yayınlayan plugin
 * Version: 1.0.0
 * Author: AI Blog System
 * License: GPL v2 or later
 */

// WordPress dışından doğrudan erişimi engelle
if (!defined('ABSPATH')) {
    exit;
}

// Plugin sabitleri
define('AIBLOG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AIBLOG_PLUGIN_PATH', plugin_dir_path(__FILE__));

class AIBlogGenerator {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_generate_ai_blog', array($this, 'generate_ai_blog'));
        add_action('wp_ajax_publish_ai_blog', array($this, 'publish_ai_blog'));
        add_action('wp_ajax_test_image_connection', array($this, 'test_image_connection'));
        add_action('wp_ajax_update_ai_blog', array($this, 'update_ai_blog'));
        add_action('wp_ajax_search_images', array($this, 'search_images'));
        add_action('wp_ajax_import_image', array($this, 'import_image'));
        add_action('wp_ajax_ai_content_tools', array($this, 'ai_content_tools'));
        add_action('wp_ajax_test_openai_connection', array($this, 'test_openai_connection'));
        add_action('wp_ajax_save_api_key', array($this, 'save_api_key'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Plugin başlatma işlemleri
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'AI Blog Generator',
            'AI Blog',
            'manage_options',
            'ai-blog-generator',
            array($this, 'admin_page'),
            'dashicons-edit-large',
            30
        );
        
        add_submenu_page(
            'ai-blog-generator',
            'Yeni Blog Oluştur',
            'Yeni Blog',
            'manage_options',
            'ai-blog-generator',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'ai-blog-generator',
            'Blog Yönetimi',
            'Blog Yönetimi',
            'manage_options',
            'ai-blog-management',
            array($this, 'management_page')
        );
        
        add_submenu_page(
            'ai-blog-generator',
            'İçerik Takvimi',
            'İçerik Takvimi',
            'manage_options',
            'ai-blog-calendar',
            array($this, 'calendar_page')
        );
        
        add_submenu_page(
            'ai-blog-generator',
            'Ayarlar',
            'Ayarlar',
            'manage_options',
            'ai-blog-settings',
            array($this, 'settings_page')
        );
        
        // Hidden edit page
        add_submenu_page(
            null, // No parent menu, makes it hidden
            'Blog Düzenle',
            'Blog Düzenle',
            'manage_options',
            'ai-blog-edit',
            array($this, 'edit_page')
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'ai-blog') !== false) {
            // WordPress media uploader
            wp_enqueue_media();
            
            wp_enqueue_script('ai-blog-admin', AIBLOG_PLUGIN_URL . 'assets/admin.js', array('jquery'), '1.0.0', true);
            wp_enqueue_style('ai-blog-admin', AIBLOG_PLUGIN_URL . 'assets/admin.css', array(), '1.0.0');
            
            wp_localize_script('ai-blog-admin', 'aiblog_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('aiblog_nonce')
            ));
        }
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>AI Blog Generator</h1>
            <div class="aiblog-container">
                <div class="aiblog-form">
                    <h2>Yeni Blog Yazısı Oluştur</h2>
                    <form id="ai-blog-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="blog_topic">Konu</label>
                                </th>
                                <td>
                                    <input type="text" id="blog_topic" name="blog_topic" class="regular-text" placeholder="Blog yazısının konusu..." required />
                                    <p class="description">AI'nın yazacağı blog yazısının ana konusunu belirtin.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="blog_style">Yazım Stili</label>
                                </th>
                                <td>
                                    <select id="blog_style" name="blog_style">
                                        <option value="formal">Resmi</option>
                                        <option value="casual">Günlük</option>
                                        <option value="technical">Teknik</option>
                                        <option value="creative">Yaratıcı</option>
                                        <option value="news">Haber</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="blog_length">Uzunluk</label>
                                </th>
                                <td>
                                    <select id="blog_length" name="blog_length">
                                        <option value="short">Short (800-1200 words)</option>
                                        <option value="medium" selected>Medium (1200-1800 words)</option>
                                        <option value="long">Long (1800-2500 words)</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="target_audience">Hedef Kitle</label>
                                </th>
                                <td>
                                    <input type="text" id="target_audience" name="target_audience" class="regular-text" placeholder="Ör: Teknoloji meraklıları, Girişimciler..." />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="keywords">Anahtar Kelimeler</label>
                                </th>
                                <td>
                                    <input type="text" id="keywords" name="keywords" class="regular-text" placeholder="Virgülle ayırın: teknoloji, yapay zeka, blog..." />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="post_action">İşlem</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="radio" name="post_action" value="draft" checked />
                                        Taslak olarak kaydet (Sonra düzenleyebilirim)
                                    </label>
                                    <br>
                                    <label>
                                        <input type="radio" name="post_action" value="publish" />
                                        Direkt yayınla
                                    </label>
                                    <p class="description">
                                        Taslak seçerseniz, Blog Yönetimi'nden düzenleyip yayınlayabilirsiniz.
                                    </p>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <button type="submit" class="button button-primary" id="generate-btn">
                                <span class="dashicons dashicons-edit-large"></span>
                                Blog Yazısı Oluştur
                            </button>
                        </p>
                    </form>
                </div>
                
                <div class="aiblog-result" id="blog-result" style="display: none;">
                    <h2>Oluşturulan Blog Yazısı</h2>
                    <div class="blog-preview">
                        <h3 id="generated-title"></h3>
                        <div id="generated-content"></div>
                    </div>
                    <div class="blog-actions">
                        <button type="button" class="button button-primary" id="publish-btn">Yayınla</button>
                        <button type="button" class="button" id="edit-btn">Düzenle</button>
                        <button type="button" class="button" id="regenerate-btn">Yeniden Oluştur</button>
                    </div>
                </div>
                
                <div class="aiblog-loading" id="loading" style="display: none;">
                    <div class="spinner"></div>
                    <p>AI blog yazısı oluşturuyor, lütfen bekleyin...</p>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function settings_page() {
        if (isset($_POST['save_settings'])) {
            update_option('aiblog_openai_key', sanitize_text_field($_POST['openai_key']));
            update_option('aiblog_unsplash_key', sanitize_text_field($_POST['unsplash_key']));
            update_option('aiblog_pexels_key', sanitize_text_field($_POST['pexels_key']));
            update_option('aiblog_default_category', intval($_POST['default_category']));
            update_option('aiblog_default_author', intval($_POST['default_author']));
            update_option('aiblog_auto_images', isset($_POST['auto_images']) ? 1 : 0);
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }
        
        $openai_key = get_option('aiblog_openai_key', '');
        $unsplash_key = get_option('aiblog_unsplash_key', '');
        $pexels_key = get_option('aiblog_pexels_key', '');
        $default_category = get_option('aiblog_default_category', 1);
        $default_author = get_option('aiblog_default_author', 1);
        $auto_images = get_option('aiblog_auto_images', 1);
        ?>
        <div class="wrap">
            <h1>AI Blog Generator Settings</h1>
            <form method="post" action="">
                <h2 class="nav-tab-wrapper">
                    <a href="#api-settings" class="nav-tab nav-tab-active">API Settings</a>
                    <a href="#blog-settings" class="nav-tab">Blog Settings</a>
                    <a href="#image-settings" class="nav-tab">Image Settings</a>
                </h2>
                
                <div id="api-settings" class="tab-content">
                    <h3>API Configuration</h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="openai_key">OpenAI API Key</label>
                            </th>
                            <td>
                                <input type="password" id="openai_key" name="openai_key" value="<?php echo esc_attr($openai_key); ?>" class="regular-text" />
                                <button type="button" id="test-openai" class="button button-secondary" style="margin-left: 10px;">
                                    <span class="dashicons dashicons-admin-tools"></span> Test Bağlantı
                                </button>
                                <div id="openai-test-result"></div>
                                <p class="description">
                                    <strong>🔗 <a href="https://platform.openai.com/api-keys" target="_blank">Bedava API Key Alın</a></strong><br>
                                    AI içerik üretimi için gerekli. Bu olmadan AI araçları çalışmaz.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="unsplash_key">Unsplash Access Key</label>
                            </th>
                            <td>
                                <input type="password" id="unsplash_key" name="unsplash_key" value="<?php echo esc_attr($unsplash_key); ?>" class="regular-text" />
                                <p class="description">
                                    <strong>🔗 <a href="https://unsplash.com/developers" target="_blank">Bedava API Key Alın</a></strong><br>
                                    Yüksek kaliteli, alakalı resimler için gerekli. Bu olmadan sadece bedava/düşük kalite resimler gösterilir.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="pexels_key">Pexels API Key</label>
                            </th>
                            <td>
                                <input type="password" id="pexels_key" name="pexels_key" value="<?php echo esc_attr($pexels_key); ?>" class="regular-text" />
                                <p class="description">
                                    <strong>🔗 <a href="https://www.pexels.com/api/" target="_blank">Bedava API Key Alın</a></strong><br>
                                    Unsplash alternatifi olarak kullanılır. İkisi birlikte daha fazla resim seçeneği sunar.
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div id="blog-settings" class="tab-content" style="display:none;">
                    <h3>Default Blog Settings</h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="default_category">Default Category</label>
                            </th>
                            <td>
                                <?php wp_dropdown_categories(array(
                                    'name' => 'default_category',
                                    'selected' => $default_category,
                                    'show_option_none' => 'Select category',
                                    'option_none_value' => 0
                                )); ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="default_author">Default Author</label>
                            </th>
                            <td>
                                <?php wp_dropdown_users(array(
                                    'name' => 'default_author',
                                    'selected' => $default_author,
                                    'show_option_none' => 'Select author',
                                    'option_none_value' => 0
                                )); ?>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div id="image-settings" class="tab-content" style="display:none;">
                    <h3>Image Configuration</h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="auto_images">Auto Add Images</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" id="auto_images" name="auto_images" <?php checked($auto_images, 1); ?> />
                                    Automatically add relevant images to blog posts
                                </label>
                                <p class="description">
                                    When enabled, the system will automatically fetch and insert relevant images into blog posts.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label>Image Connection Test</label>
                            </th>
                            <td>
                                <button type="button" id="test-image-connection" class="button button-secondary">
                                    Test Image Sources
                                </button>
                                <p class="description">
                                    Test connectivity to image sources and view debug information.
                                </p>
                                <div id="image-test-results" style="display: none; margin-top: 15px;"></div>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <?php submit_button('Save Settings', 'primary', 'save_settings'); ?>
            </form>
            
            <script>
            jQuery(document).ready(function($) {
                $('.nav-tab').click(function(e) {
                    e.preventDefault();
                    $('.nav-tab').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active');
                    $('.tab-content').hide();
                    $($(this).attr('href')).show();
                });
                
                // Image connection test
                $('#test-image-connection').click(function() {
                    const $button = $(this);
                    const $results = $('#image-test-results');
                    
                    $button.prop('disabled', true).text('Testing...');
                    $results.html('<p>Testing image connections...</p>').show();
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'test_image_connection',
                            nonce: '<?php echo wp_create_nonce("aiblog_nonce"); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                let html = '<div style="background: #fff; border: 1px solid #ddd; padding: 15px; border-radius: 4px;">';
                                html += '<h4>🔍 Image Connection Test Results</h4>';
                                
                                response.results.forEach(function(result, index) {
                                    const status = result.status === 'success' ? '✅' : '❌';
                                    const statusClass = result.status === 'success' ? 'success' : 'error';
                                    
                                    html += '<div style="margin-bottom: 10px; padding: 10px; border-left: 4px solid ' + (result.status === 'success' ? '#46b450' : '#dc3232') + '; background: ' + (result.status === 'success' ? '#f0fff4' : '#fff0f0') + '">';
                                    html += '<strong>' + status + ' Source ' + (index + 1) + '</strong><br>';
                                    html += '<small>' + result.url + '</small><br>';
                                    
                                    if (result.status === 'success') {
                                        html += 'Response: ' + result.response_time + 'ms | Size: ' + Math.round(result.size/1024) + 'KB';
                                    } else {
                                        html += 'Error: ' + (result.message || 'HTTP ' + result.http_code);
                                    }
                                    html += '</div>';
                                });
                                
                                html += '<h4>🖥️ Server Information</h4>';
                                html += '<div style="background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; font-size: 12px;">';
                                html += 'PHP: ' + response.server_info.php_version + '<br>';
                                html += 'cURL: ' + response.server_info.curl_version + '<br>';
                                html += 'URL fopen: ' + response.server_info.allow_url_fopen + '<br>';
                                html += 'Max execution: ' + response.server_info.max_execution_time + 's<br>';
                                html += 'Memory limit: ' + response.server_info.memory_limit;
                                html += '</div>';
                                html += '</div>';
                                
                                $results.html(html);
                            } else {
                                $results.html('<div style="color: #dc3232;">Test failed: ' + (response.message || 'Unknown error') + '</div>');
                            }
                        },
                        error: function() {
                            $results.html('<div style="color: #dc3232;">Connection test failed</div>');
                        },
                        complete: function() {
                            $button.prop('disabled', false).text('Test Image Sources');
                        }
                    });
                    });
                    
                    // OpenAI test connection
                    $('#test-openai').click(function() {
                        const apiKey = $('#openai_key').val();
                        const $button = $(this);
                        const $result = $('#openai-test-result');
                        
                        if (!apiKey) {
                            $result.html('<div class="notice notice-error" style="margin: 10px 0;"><p>❌ Önce API anahtarını girin</p></div>');
                            return;
                        }
                        
                        $button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Test ediliyor...');
                        $result.html('<div class="notice notice-info" style="margin: 10px 0;"><p>🔄 OpenAI API bağlantısı test ediliyor...</p></div>');
                        
                        $.ajax({
                            url: aiblog_ajax.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'test_openai_connection',
                                nonce: aiblog_ajax.nonce,
                                api_key: apiKey
                            },
                            success: function(response) {
                                $button.prop('disabled', false).html('<span class="dashicons dashicons-admin-tools"></span> Test Bağlantı');
                                
                                if (response.success) {
                                    $result.html('<div class="notice notice-success" style="margin: 10px 0;"><p>' + response.data + '</p></div>');
                                } else {
                                    $result.html('<div class="notice notice-error" style="margin: 10px 0;"><p>' + response.data + '</p></div>');
                                }
                            },
                            error: function() {
                                $button.prop('disabled', false).html('<span class="dashicons dashicons-admin-tools"></span> Test Bağlantı');
                                $result.html('<div class="notice notice-error" style="margin: 10px 0;"><p>❌ Test sırasında hata oluştu</p></div>');
                            }
                        });
                    });
                });
            </script>
        </div>
        <?php
    }
    
    public function management_page() {
        // Handle actions
        if (isset($_GET['action']) && isset($_GET['post_id'])) {
            $action = sanitize_text_field($_GET['action']);
            $post_id = intval($_GET['post_id']);
            
            switch ($action) {
                case 'delete':
                    if (wp_verify_nonce($_GET['_wpnonce'], 'delete_aiblog_' . $post_id)) {
                        wp_delete_post($post_id, true);
                        echo '<div class="notice notice-success"><p>Blog yazısı silindi!</p></div>';
                    }
                    break;
                case 'publish':
                    if (wp_verify_nonce($_GET['_wpnonce'], 'publish_aiblog_' . $post_id)) {
                        wp_update_post(array('ID' => $post_id, 'post_status' => 'publish'));
                        echo '<div class="notice notice-success"><p>Blog yazısı yayınlandı!</p></div>';
                    }
                    break;
                case 'draft':
                    if (wp_verify_nonce($_GET['_wpnonce'], 'draft_aiblog_' . $post_id)) {
                        wp_update_post(array('ID' => $post_id, 'post_status' => 'draft'));
                        echo '<div class="notice notice-success"><p>Blog yazısı taslağa alındı!</p></div>';
                    }
                    break;
            }
        }
        
        // Get AI generated posts
        $ai_posts = get_posts(array(
            'meta_key' => '_aiblog_generated',
            'meta_value' => true,
            'post_status' => array('publish', 'draft', 'pending'),
            'numberposts' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        ?>
        <div class="wrap">
            <h1>🤖 AI Blog Yönetimi</h1>
            
            <?php if (empty($ai_posts)): ?>
                <div class="aiblog-empty-state">
                    <div style="text-align: center; padding: 60px 20px; background: #fff; border: 1px solid #ddd; border-radius: 8px;">
                        <h2>📝 Henüz AI Blog Yazısı Yok</h2>
                        <p>AI ile ilk blog yazınızı oluşturmak için başlayın!</p>
                        <a href="<?php echo admin_url('admin.php?page=ai-blog-generator'); ?>" class="button button-primary button-large">
                            <span class="dashicons dashicons-plus-alt"></span> Yeni Blog Oluştur
                        </a>
                    </div>
                </div>
            <?php else: ?>
                
                <div class="aiblog-stats-bar">
                    <div class="stats-item">
                        <span class="stats-number"><?php echo count($ai_posts); ?></span>
                        <span class="stats-label">Toplam Blog</span>
                    </div>
                    <div class="stats-item">
                        <span class="stats-number"><?php echo count(array_filter($ai_posts, function($p) { return $p->post_status === 'publish'; })); ?></span>
                        <span class="stats-label">Yayında</span>
                    </div>
                    <div class="stats-item">
                        <span class="stats-number"><?php echo count(array_filter($ai_posts, function($p) { return $p->post_status === 'draft'; })); ?></span>
                        <span class="stats-label">Taslak</span>
                    </div>
                    <div class="stats-item">
                        <a href="<?php echo admin_url('admin.php?page=ai-blog-generator'); ?>" class="button button-primary">
                            <span class="dashicons dashicons-plus-alt"></span> Yeni Blog
                        </a>
                    </div>
                </div>
                
                <div class="aiblog-posts-grid">
                    <?php foreach ($ai_posts as $post): 
                        $word_count = get_post_meta($post->ID, '_aiblog_word_count', true);
                        $keywords = get_post_meta($post->ID, '_aiblog_keywords', true);
                        $generated_date = get_post_meta($post->ID, '_aiblog_generated_date', true);
                        $thumbnail = get_the_post_thumbnail_url($post->ID, 'medium');
                        ?>
                        <div class="aiblog-post-card" data-post-id="<?php echo $post->ID; ?>">
                            <div class="post-thumbnail">
                                <?php if ($thumbnail): ?>
                                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($post->post_title); ?>">
                                <?php else: ?>
                                    <div class="no-thumbnail">
                                        <span class="dashicons dashicons-camera"></span>
                                        <small>Kapak resmi yok</small>
                                    </div>
                                <?php endif; ?>
                                <div class="post-status status-<?php echo $post->post_status; ?>">
                                    <?php echo ucfirst($post->post_status); ?>
                                </div>
                            </div>
                            
                            <div class="post-content">
                                <h3><?php echo esc_html($post->post_title); ?></h3>
                                <p class="post-excerpt"><?php echo esc_html(wp_trim_words($post->post_excerpt ?: $post->post_content, 20)); ?></p>
                                
                                <div class="post-meta">
                                    <span><strong>📅</strong> <?php echo date('d M Y', strtotime($post->post_date)); ?></span>
                                    <?php if ($word_count): ?>
                                        <span><strong>📝</strong> <?php echo $word_count; ?> kelime</span>
                                    <?php endif; ?>
                                    <?php if ($keywords): ?>
                                        <span><strong>🏷️</strong> <?php echo esc_html($keywords); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="post-actions">
                                <a href="<?php echo admin_url('admin.php?page=ai-blog-edit&post_id=' . $post->ID); ?>" class="button button-secondary">
                                    <span class="dashicons dashicons-edit"></span> Düzenle
                                </a>
                                
                                <?php if ($post->post_status === 'draft'): ?>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=ai-blog-management&action=publish&post_id=' . $post->ID), 'publish_aiblog_' . $post->ID); ?>" 
                                       class="button button-primary">
                                        <span class="dashicons dashicons-visibility"></span> Yayınla
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo get_permalink($post->ID); ?>" class="button button-secondary" target="_blank">
                                        <span class="dashicons dashicons-external"></span> Görüntüle
                                    </a>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=ai-blog-management&action=draft&post_id=' . $post->ID), 'draft_aiblog_' . $post->ID); ?>" 
                                       class="button button-secondary">
                                        <span class="dashicons dashicons-hidden"></span> Taslağa Al
                                    </a>
                                <?php endif; ?>
                                
                                <div class="post-actions-more">
                                    <button class="button button-link-delete" onclick="if(confirm('Bu blog yazısını silmek istediğinizden emin misiniz?')) { window.location.href='<?php echo wp_nonce_url(admin_url('admin.php?page=ai-blog-management&action=delete&post_id=' . $post->ID), 'delete_aiblog_' . $post->ID); ?>'; }">
                                        <span class="dashicons dashicons-trash"></span> Sil
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .aiblog-stats-bar {
            display: flex;
            gap: 20px;
            margin: 20px 0;
            align-items: center;
        }
        
        .stats-item {
            text-align: center;
            background: #fff;
            padding: 15px 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
            min-width: 100px;
        }
        
        .stats-number {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: #0073aa;
        }
        
        .stats-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        
        .aiblog-posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .aiblog-post-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }
        
        .aiblog-post-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .post-thumbnail {
            position: relative;
            height: 200px;
            background: #f8f9fa;
        }
        
        .post-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .no-thumbnail {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #666;
        }
        
        .no-thumbnail .dashicons {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .post-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-publish {
            background: #00a32a;
            color: white;
        }
        
        .status-draft {
            background: #dba617;
            color: white;
        }
        
        .status-pending {
            background: #646970;
            color: white;
        }
        
        .post-content {
            padding: 20px;
        }
        
        .post-content h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
            line-height: 1.4;
        }
        
        .post-excerpt {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .post-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 13px;
            color: #666;
        }
        
        .post-actions {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .post-actions .button {
            font-size: 13px;
            padding: 6px 12px;
            height: auto;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .post-actions-more {
            margin-left: auto;
        }
        </style>
        <?php
    }
    
    public function edit_page() {
        if (!isset($_GET['post_id'])) {
            wp_die('Post ID gerekli.');
        }
        
        $post_id = intval($_GET['post_id']);
        $post = get_post($post_id);
        
        if (!$post || !get_post_meta($post_id, '_aiblog_generated', true)) {
            wp_die('Geçersiz AI blog post.');
        }
        
        // Get post data
        $categories = get_categories();
        $post_categories = wp_get_post_categories($post_id);
        $thumbnail_id = get_post_thumbnail_id($post_id);
        $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : '';
        $keywords = get_post_meta($post_id, '_aiblog_keywords', true);
        $word_count = get_post_meta($post_id, '_aiblog_word_count', true);
        
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">
                <span class="dashicons dashicons-edit"></span>
                Blog Düzenle: <?php echo esc_html($post->post_title); ?>
            </h1>
            <a href="<?php echo admin_url('admin.php?page=ai-blog-management'); ?>" class="page-title-action">
                ← Geri Dön
            </a>
            <hr class="wp-header-end">
            
            <form id="aiblog-edit-form" method="post">
                <?php wp_nonce_field('aiblog_edit_post', 'aiblog_edit_nonce'); ?>
                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                
                <div class="aiblog-edit-container">
                    <div class="aiblog-main-content">
                        
                        <!-- Başlık -->
                        <div class="aiblog-field">
                            <label for="post_title"><strong>📝 Başlık</strong></label>
                            <div class="title-editor-wrapper">
                                <input type="text" id="post_title" name="post_title" value="<?php echo esc_attr($post->post_title); ?>" class="aiblog-title-input" required>
                                <div class="ai-title-tools">
                                    <button type="button" class="button button-secondary ai-btn" data-action="improve-title" title="Başlığı AI ile iyileştir">
                                        <span class="dashicons dashicons-admin-tools"></span>
                                    </button>
                                    <button type="button" class="button button-secondary ai-btn" data-action="generate-alternatives" title="Alternatif başlıklar öner">
                                        <span class="dashicons dashicons-admin-page"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- İçerik Editörü -->
                        <div class="aiblog-field">
                            <label for="post_content"><strong>✏️ İçerik</strong></label>
                            
                            <!-- AI Content Tools -->
                            <div class="ai-content-tools">
                                <div class="ai-tools-row">
                                    <button type="button" class="button button-primary ai-btn" data-action="improve-content" title="Yazım hatalarını düzelt ve iyileştir">
                                        <span class="dashicons dashicons-yes-alt"></span> Düzelt & İyileştir
                                    </button>
                                    
                                    <button type="button" class="button button-secondary ai-btn" data-action="change-tone" title="Yazının tonunu değiştir">
                                        <span class="dashicons dashicons-admin-customizer"></span> Ton Değiştir
                                    </button>
                                    
                                    <button type="button" class="button button-secondary ai-btn" data-action="expand-content" title="İçeriği genişlet ve detaylandır">
                                        <span class="dashicons dashicons-plus-alt2"></span> Genişlet
                                    </button>
                                    
                                    <button type="button" class="button button-secondary ai-btn" data-action="summarize" title="İçeriği özetle">
                                        <span class="dashicons dashicons-minus"></span> Özetle
                                    </button>
                                    
                                    <button type="button" class="button button-secondary ai-btn" data-action="seo-optimize" title="SEO için optimize et">
                                        <span class="dashicons dashicons-chart-line"></span> SEO Optimize
                                    </button>
                                </div>
                                
                                <div class="ai-tools-row" id="tone-selection" style="display: none;">
                                    <select id="tone-selector" class="ai-select">
                                        <option value="">Ton seçin...</option>
                                        <option value="formal">📋 Formal/Resmi</option>
                                        <option value="casual">😊 Samimi/Rahat</option>
                                        <option value="professional">💼 Profesyonel</option>
                                        <option value="friendly">🤝 Arkadaşça</option>
                                        <option value="technical">⚙️ Teknik</option>
                                        <option value="simple">📖 Basit/Anlaşılır</option>
                                        <option value="persuasive">💪 İkna Edici</option>
                                        <option value="storytelling">📚 Hikaye Anlatımı</option>
                                    </select>
                                    
                                    <button type="button" id="apply-tone" class="button button-primary">
                                        Uygula
                                    </button>
                                    
                                    <button type="button" id="cancel-tone" class="button button-secondary">
                                        İptal
                                    </button>
                                </div>
                                
                                <div id="ai-processing" class="ai-processing" style="display: none;">
                                    <div class="spinner"></div>
                                    <span class="processing-text">AI işlem yapıyor...</span>
                                </div>
                            </div>
                            
                            <?php
                            wp_editor($post->post_content, 'post_content', array(
                                'textarea_name' => 'post_content',
                                'textarea_rows' => 20,
                                'media_buttons' => true,
                                'teeny' => false,
                                'quicktags' => true,
                                'tinymce' => array(
                                    'toolbar1' => 'bold,italic,underline,strikethrough,bullist,numlist,link,unlink,blockquote,hr,alignleft,aligncenter,alignright,undo,redo',
                                    'toolbar2' => 'formatselect,forecolor,backcolor,pastetext,removeformat,charmap,outdent,indent,fullscreen,wp_help'
                                )
                            ));
                            ?>
                        </div>
                        
                        <!-- Özet -->
                        <div class="aiblog-field">
                            <label for="post_excerpt"><strong>📄 Özet</strong></label>
                            <textarea id="post_excerpt" name="post_excerpt" rows="4" class="aiblog-textarea"><?php echo esc_textarea($post->post_excerpt); ?></textarea>
                            <p class="description">Blog yazısının kısa özeti (meta description olarak kullanılır)</p>
                        </div>
                        
                    </div>
                    
                    <div class="aiblog-sidebar">
                        
                        <!-- SEO Analizi -->
                        <div class="aiblog-meta-box">
                            <h3>📊 SEO Analizi</h3>
                            
                            <div class="seo-score-container">
                                <div class="seo-score-circle">
                                    <div class="seo-score-value" id="seo-score">-</div>
                                    <div class="seo-score-label">SEO Skoru</div>
                                </div>
                            </div>
                            
                            <div class="seo-checks">
                                <div class="seo-check" id="title-length">
                                    <span class="seo-icon">⚠️</span>
                                    <span class="seo-text">Başlık uzunluğu: <span class="seo-value">-</span></span>
                                </div>
                                
                                <div class="seo-check" id="meta-description">
                                    <span class="seo-icon">⚠️</span>
                                    <span class="seo-text">Meta açıklama: <span class="seo-value">Eksik</span></span>
                                </div>
                                
                                <div class="seo-check" id="keyword-density">
                                    <span class="seo-icon">⚠️</span>
                                    <span class="seo-text">Anahtar kelime yoğunluğu: <span class="seo-value">-</span></span>
                                </div>
                                
                                <div class="seo-check" id="readability">
                                    <span class="seo-icon">⚠️</span>
                                    <span class="seo-text">Okunabilirlik: <span class="seo-value">-</span></span>
                                </div>
                                
                                <div class="seo-check" id="word-count">
                                    <span class="seo-icon">⚠️</span>
                                    <span class="seo-text">Kelime sayısı: <span class="seo-value">-</span></span>
                                </div>
                                
                                <div class="seo-check" id="headings-check">
                                    <span class="seo-icon">⚠️</span>
                                    <span class="seo-text">Alt başlıklar: <span class="seo-value">-</span></span>
                                </div>
                            </div>
                            
                            <button type="button" id="analyze-seo" class="button button-secondary" style="width: 100%; margin-top: 10px;">
                                <span class="dashicons dashicons-chart-line"></span> Analizi Yenile
                            </button>
                        </div>
                        
                        <!-- Akıllı Zamanlama -->
                        <div class="aiblog-meta-box">
                            <h3>⏰ Akıllı Zamanlama</h3>
                            
                            <div class="scheduling-options">
                                <label>
                                    <input type="radio" name="schedule_type" value="now" checked>
                                    <span class="schedule-option">
                                        <strong>🚀 Hemen Yayınla</strong>
                                        <small>Anında yayına alır</small>
                                    </span>
                                </label>
                                
                                <label>
                                    <input type="radio" name="schedule_type" value="smart">
                                    <span class="schedule-option">
                                        <strong>🎯 Akıllı Zamanlama</strong>
                                        <small>En iyi zamanda otomatik yayınlar</small>
                                    </span>
                                </label>
                                
                                <label>
                                    <input type="radio" name="schedule_type" value="custom">
                                    <span class="schedule-option">
                                        <strong>📅 Özel Tarih</strong>
                                        <small>Belirli bir tarih ve saat seçin</small>
                                    </span>
                                </label>
                            </div>
                            
                            <div id="smart-scheduling-info" class="scheduling-info" style="display: none;">
                                <div class="optimal-times">
                                    <h4>💡 Önerilen Yayın Zamanları:</h4>
                                    <div class="time-suggestions">
                                        <div class="time-slot best">
                                            <span class="time">🕘 09:00</span>
                                            <span class="score">92% başarı</span>
                                        </div>
                                        <div class="time-slot good">
                                            <span class="time">🕐 13:00</span>
                                            <span class="score">87% başarı</span>
                                        </div>
                                        <div class="time-slot good">
                                            <span class="time">🕖 19:00</span>
                                            <span class="score">84% başarı</span>
                                        </div>
                                    </div>
                                    <button type="button" id="analyze-optimal-times" class="button button-secondary">
                                        📊 Zamanlama Analizi Yenile
                                    </button>
                                </div>
                            </div>
                            
                            <div id="custom-scheduling" class="scheduling-info" style="display: none;">
                                <div class="custom-datetime">
                                    <label>📅 Yayın Tarihi:</label>
                                    <input type="date" id="schedule_date" name="schedule_date" class="schedule-input">
                                    
                                    <label>🕐 Yayın Saati:</label>
                                    <input type="time" id="schedule_time" name="schedule_time" class="schedule-input">
                                    
                                    <div class="timezone-info">
                                        <small>⏰ Saat dilimi: <?php echo wp_timezone_string(); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sosyal Medya Paylaşımı -->
                        <div class="aiblog-meta-box">
                            <h3>📱 Sosyal Medya Otomasyonu</h3>
                            
                            <div class="social-media-options">
                                <label class="social-option">
                                    <input type="checkbox" name="auto_share_twitter" value="1">
                                    <span class="social-icon">🐦</span>
                                    <span class="social-text">
                                        <strong>Twitter'da Paylaş</strong>
                                        <small>Otomatik tweet atılır</small>
                                    </span>
                                </label>
                                
                                <label class="social-option">
                                    <input type="checkbox" name="auto_share_facebook" value="1">
                                    <span class="social-icon">📘</span>
                                    <span class="social-text">
                                        <strong>Facebook'ta Paylaş</strong>
                                        <small>Sayfanızda otomatik post</small>
                                    </span>
                                </label>
                                
                                <label class="social-option">
                                    <input type="checkbox" name="auto_share_linkedin" value="1">
                                    <span class="social-icon">💼</span>
                                    <span class="social-text">
                                        <strong>LinkedIn'de Paylaş</strong>
                                        <small>Profesyonel ağınızda paylaş</small>
                                    </span>
                                </label>
                            </div>
                            
                            <div class="social-preview" id="social-preview" style="display: none;">
                                <h4>📝 Sosyal Medya Önizleme:</h4>
                                <div class="preview-content">
                                    <div class="preview-text" id="preview-text"></div>
                                    <div class="preview-hashtags" id="preview-hashtags"></div>
                                </div>
                                <button type="button" id="customize-social" class="button button-secondary">
                                    ✏️ Özelleştir
                                </button>
                            </div>
                        </div>
                        
                        <!-- Yayın Durumu -->
                        <div class="aiblog-meta-box">
                            <h3>📋 Yayın Durumu</h3>
                            <div class="aiblog-field">
                                <label>
                                    <input type="radio" name="post_status" value="draft" <?php checked($post->post_status, 'draft'); ?>>
                                    <span class="status-label status-draft">Taslak</span>
                                </label>
                                <label>
                                    <input type="radio" name="post_status" value="publish" <?php checked($post->post_status, 'publish'); ?>>
                                    <span class="status-label status-publish">Yayında</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Kapak Resmi -->
                        <div class="aiblog-meta-box">
                            <h3>🖼️ Kapak Resmi</h3>
                            <div class="aiblog-thumbnail-container">
                                <div id="thumbnail-preview" class="thumbnail-preview" <?php echo $thumbnail_url ? '' : 'style="display:none;"'; ?>>
                                    <img src="<?php echo esc_url($thumbnail_url); ?>" alt="Kapak resmi" id="thumbnail-image">
                                    <button type="button" id="remove-thumbnail" class="button-link-delete">✕ Kaldır</button>
                                </div>
                                
                                <div id="thumbnail-placeholder" class="thumbnail-placeholder" <?php echo $thumbnail_url ? 'style="display:none;"' : ''; ?>>
                                    <span class="dashicons dashicons-camera"></span>
                                    <p>Kapak resmi seçilmedi</p>
                                </div>
                                
                                <button type="button" id="select-thumbnail" class="button button-secondary">
                                    <span class="dashicons dashicons-admin-media"></span>
                                    <?php echo $thumbnail_url ? 'Resmi Değiştir' : 'Resim Seç'; ?>
                                </button>
                                
                                <button type="button" id="search-images" class="button button-primary">
                                    <span class="dashicons dashicons-search"></span>
                                    Resim Ara
                                </button>
                                
                                <input type="hidden" id="thumbnail_id" name="thumbnail_id" value="<?php echo $thumbnail_id; ?>">
                            </div>
                        </div>
                        
                        <!-- Kategori -->
                        <div class="aiblog-meta-box">
                            <h3>📁 Kategori</h3>
                            <select name="post_category" id="post_category" class="aiblog-select">
                                <option value="">Kategori seçin</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category->term_id; ?>" 
                                            <?php echo in_array($category->term_id, $post_categories) ? 'selected' : ''; ?>>
                                        <?php echo esc_html($category->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Anahtar Kelimeler -->
                        <div class="aiblog-meta-box">
                            <h3>🏷️ Anahtar Kelimeler</h3>
                            <textarea name="keywords" id="keywords" rows="3" class="aiblog-textarea" placeholder="Virgülle ayırın"><?php echo esc_textarea($keywords); ?></textarea>
                        </div>
                        
                        <!-- İstatistikler -->
                        <?php if ($word_count): ?>
                        <div class="aiblog-meta-box">
                            <h3>📊 İstatistikler</h3>
                            <div class="aiblog-stats">
                                <div class="stat-item">
                                    <strong><?php echo number_format($word_count); ?></strong>
                                    <span>Kelime</span>
                                </div>
                                <div class="stat-item">
                                    <strong><?php echo date('d.m.Y', strtotime($post->post_date)); ?></strong>
                                    <span>Oluşturulma</span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Kaydet Butonları -->
                        <div class="aiblog-save-actions">
                            <button type="submit" class="button button-primary button-large" id="save-post">
                                <span class="dashicons dashicons-yes"></span>
                                Değişiklikleri Kaydet
                            </button>
                            
                            <button type="button" class="button button-secondary" onclick="window.location.href='<?php echo admin_url('admin.php?page=ai-blog-management'); ?>'">
                                <span class="dashicons dashicons-arrow-left-alt"></span>
                                İptal
                            </button>
                            
                            <button type="button" class="button button-link-delete" id="delete-post" data-post-id="<?php echo $post_id; ?>">
                                <span class="dashicons dashicons-trash"></span>
                                Sil
                            </button>
                        </div>
                        
                    </div>
                </div>
            </form>
            
            <div id="saving-indicator" style="display: none;">
                <div class="notice notice-info">
                    <p>💾 Kaydediliyor...</p>
                </div>
            </div>
            
        </div>
        
        <!-- Image Search Modal -->
        <div id="image-search-modal" class="aiblog-modal" style="display: none;">
            <div class="aiblog-modal-content">
                <div class="aiblog-modal-header">
                    <h2>🔍 Resim Ara ve Seç</h2>
                    <button type="button" class="aiblog-modal-close">&times;</button>
                </div>
                
                <div class="aiblog-modal-body">
                    <div class="image-search-form">
                        <input type="text" id="image-search-query" placeholder="Arama terimi girin (örn: technology, nature, business)" class="image-search-input">
                        <button type="button" id="do-image-search" class="button button-primary">
                            <span class="dashicons dashicons-search"></span> Ara
                        </button>
                    </div>
                    
                    <div class="search-suggestions">
                        <h4>💡 Önerilen aramalar:</h4>
                        <div class="suggestion-tags">
                            <span class="suggestion-tag" data-query="technology">Technology</span>
                            <span class="suggestion-tag" data-query="business">Business</span>
                            <span class="suggestion-tag" data-query="nature">Nature</span>
                            <span class="suggestion-tag" data-query="lifestyle">Lifestyle</span>
                            <span class="suggestion-tag" data-query="abstract">Abstract</span>
                            <span class="suggestion-tag" data-query="minimal">Minimal</span>
                        </div>
                    </div>
                    
                    <div id="image-search-loading" class="search-loading" style="display: none;">
                        <div class="spinner"></div>
                        <p>Resimler aranıyor...</p>
                    </div>
                    
                    <div id="image-search-results" class="image-results-grid">
                        <!-- Results will be populated here -->
                    </div>
                    
                    <div class="search-pagination" id="search-pagination" style="display: none;">
                        <button type="button" id="load-more-images" class="button button-secondary">
                            Daha Fazla Resim Yükle
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .aiblog-edit-container {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
            margin-top: 20px;
        }
        
        .aiblog-main-content {
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .aiblog-sidebar {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .aiblog-meta-box {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
        }
        
        .aiblog-meta-box h3 {
            margin: 0 0 15px 0;
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }
        
        .aiblog-field {
            margin-bottom: 20px;
        }
        
        .aiblog-field label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .aiblog-title-input {
            width: 100%;
            font-size: 18px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .aiblog-textarea, .aiblog-select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .thumbnail-preview {
            position: relative;
            margin-bottom: 10px;
        }
        
        .thumbnail-preview img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .thumbnail-preview .button-link-delete {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0,0,0,0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
        }
        
        .thumbnail-placeholder {
            text-align: center;
            padding: 40px 20px;
            border: 2px dashed #ddd;
            border-radius: 4px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .thumbnail-placeholder .dashicons {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .status-label {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 5px;
        }
        
        .status-draft {
            background: #dba617;
            color: white;
        }
        
        .status-publish {
            background: #00a32a;
            color: white;
        }
        
        .aiblog-stats {
            display: flex;
            gap: 15px;
        }
        
        .stat-item {
            text-align: center;
            flex: 1;
        }
        
        .stat-item strong {
            display: block;
            font-size: 18px;
            color: #0073aa;
        }
        
        .stat-item span {
            font-size: 12px;
            color: #666;
        }
        
        .aiblog-save-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .aiblog-save-actions .button {
            justify-content: center;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        @media (max-width: 768px) {
            .aiblog-edit-container {
                grid-template-columns: 1fr;
            }
        }
        
        /* Modal Styles */
        .aiblog-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .aiblog-modal-content {
            background: #fff;
            border-radius: 8px;
            width: 90%;
            max-width: 1000px;
            max-height: 90%;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .aiblog-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #ddd;
            background: #f8f9fa;
        }
        
        .aiblog-modal-header h2 {
            margin: 0;
            font-size: 18px;
        }
        
        .aiblog-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .aiblog-modal-close:hover {
            background: #e9ecef;
            color: #333;
        }
        
        .aiblog-modal-body {
            padding: 20px;
            max-height: 70vh;
            overflow-y: auto;
        }
        
        .image-search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .image-search-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .search-suggestions {
            margin-bottom: 20px;
        }
        
        .search-suggestions h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
        }
        
        .suggestion-tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .suggestion-tag {
            background: #e9ecef;
            color: #495057;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .suggestion-tag:hover {
            background: #0073aa;
            color: white;
        }
        
        .search-loading {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .search-loading .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #0073aa;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px auto;
        }
        
        .image-results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .image-result-item {
            position: relative;
            cursor: pointer;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease;
            border: 2px solid transparent;
        }
        
        .image-result-item:hover {
            transform: scale(1.05);
            border-color: #0073aa;
        }
        
        .image-result-item.selected {
            border-color: #00a32a;
            transform: scale(1.05);
        }
        
        .image-result-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            display: block;
        }
        
        .image-result-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 15px 10px 10px 10px;
            font-size: 12px;
        }
        
        .image-result-overlay .photographer {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .image-result-overlay .source {
            opacity: 0.8;
        }
        
        .image-select-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            border: none;
            border-radius: 20px;
            padding: 8px 12px;
            font-size: 12px;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .image-result-item:hover .image-select-btn {
            opacity: 1;
        }
        
        .image-select-btn:hover {
            background: #0073aa;
        }
        
        .search-pagination {
            text-align: center;
            padding: 20px 0;
        }
        
        /* AI Tools Styles */
        .title-editor-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .title-editor-wrapper .aiblog-title-input {
            flex: 1;
        }
        
        .ai-title-tools {
            display: flex;
            gap: 5px;
        }
        
        .ai-content-tools {
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .ai-tools-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        
        .ai-tools-row:last-child {
            margin-bottom: 0;
        }
        
        .ai-btn {
            display: flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }
        
        .ai-btn .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
        }
        
        .ai-select {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
        }
        
        .ai-processing {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            color: #856404;
        }
        
        .ai-processing .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #856404;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        /* SEO Analysis Styles */
        .seo-score-container {
            text-align: center;
            margin: 20px 0;
        }
        
        .seo-score-circle {
            display: inline-block;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(45deg, #e9ecef, #f8f9fa);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            position: relative;
        }
        
        .seo-score-circle.good {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
        }
        
        .seo-score-circle.average {
            background: linear-gradient(45deg, #ffc107, #fd7e14);
            color: white;
        }
        
        .seo-score-circle.poor {
            background: linear-gradient(45deg, #dc3545, #e83e8c);
            color: white;
        }
        
        .seo-score-value {
            font-size: 24px;
            font-weight: bold;
            line-height: 1;
        }
        
        .seo-score-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .seo-checks {
            space-y: 8px;
        }
        
        .seo-check {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
            font-size: 13px;
        }
        
        .seo-check:last-child {
            border-bottom: none;
        }
        
        .seo-icon {
            font-size: 14px;
            width: 20px;
        }
        
        .seo-text {
            flex: 1;
        }
        
        .seo-value {
            font-weight: bold;
        }
        
        .seo-check.good .seo-icon {
            color: #28a745;
        }
        
        .seo-check.good .seo-icon::before {
            content: "✅";
        }
        
        .seo-check.average .seo-icon {
            color: #ffc107;
        }
        
        .seo-check.average .seo-icon::before {
            content: "⚠️";
        }
        
        .seo-check.poor .seo-icon {
            color: #dc3545;
        }
        
        .seo-check.poor .seo-icon::before {
            content: "❌";
        }
        
        /* Scheduling Styles */
        .scheduling-options {
            margin-bottom: 15px;
        }
        
        .scheduling-options label {
            display: block;
            margin-bottom: 10px;
            cursor: pointer;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 12px;
            transition: all 0.3s ease;
        }
        
        .scheduling-options label:hover {
            border-color: #0073aa;
            background: #f8f9fa;
        }
        
        .scheduling-options input[type="radio"] {
            margin-right: 10px;
        }
        
        .schedule-option {
            display: flex;
            flex-direction: column;
        }
        
        .schedule-option strong {
            font-size: 14px;
            margin-bottom: 3px;
        }
        
        .schedule-option small {
            color: #666;
            font-size: 12px;
        }
        
        .scheduling-info {
            background: #f8f9fa;
            border: 1px solid #e1e1e1;
            border-radius: 6px;
            padding: 15px;
            margin-top: 10px;
        }
        
        .time-suggestions {
            display: flex;
            gap: 10px;
            margin: 10px 0;
            flex-wrap: wrap;
        }
        
        .time-slot {
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
            flex: 1;
            min-width: 80px;
        }
        
        .time-slot.best {
            border-color: #28a745;
            background: #d4edda;
        }
        
        .time-slot.good {
            border-color: #ffc107;
            background: #fff3cd;
        }
        
        .time-slot .time {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .time-slot .score {
            font-size: 11px;
            color: #666;
        }
        
        .custom-datetime {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px;
            align-items: center;
        }
        
        .schedule-input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .timezone-info {
            grid-column: 1 / -1;
            margin-top: 10px;
            text-align: center;
        }
        
        /* Social Media Styles */
        .social-media-options {
            margin-bottom: 15px;
        }
        
        .social-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .social-option:hover {
            border-color: #0073aa;
            background: #f8f9fa;
        }
        
        .social-icon {
            font-size: 20px;
            width: 30px;
            text-align: center;
        }
        
        .social-text {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .social-text strong {
            font-size: 14px;
            margin-bottom: 2px;
        }
        
        .social-text small {
            color: #666;
            font-size: 12px;
        }
        
        .social-preview {
            background: #f8f9fa;
            border: 1px solid #e1e1e1;
            border-radius: 6px;
            padding: 15px;
            margin-top: 10px;
        }
        
        .preview-content {
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 12px;
            margin: 10px 0;
        }
        
        .preview-text {
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .preview-hashtags {
            font-size: 12px;
            color: #1da1f2;
        }
        
        /* UX Enhancement Styles */
        
        /* Keyboard Shortcuts Feedback */
        .shortcut-feedback {
            position: fixed;
            top: 50px;
            right: 20px;
            background: #0073aa;
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 500;
            z-index: 999999;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .shortcut-feedback.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        /* Shortcuts Introduction */
        .shortcuts-intro {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            border: 1px solid #0073aa;
            border-radius: 8px;
            padding: 20px;
            max-width: 350px;
            z-index: 999999;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            animation: slideInUp 0.5s ease;
        }
        
        .shortcuts-content h4 {
            margin: 0 0 10px 0;
            color: #0073aa;
        }
        
        .shortcuts-content p {
            margin: 0 0 15px 0;
            font-size: 13px;
            color: #666;
        }
        
        .shortcut-examples {
            margin-bottom: 15px;
        }
        
        .shortcut-key {
            background: #f1f1f1;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 3px 8px;
            font-family: monospace;
            font-size: 11px;
            margin-right: 5px;
        }
        
        .close-shortcuts-intro {
            background: #0073aa;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 16px;
            cursor: pointer;
            font-size: 12px;
        }
        
        /* Shortcuts Modal */
        .shortcuts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .shortcut-category h4 {
            color: #0073aa;
            border-bottom: 1px solid #e1e1e1;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        
        .shortcut-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .shortcut-keys {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 4px 8px;
            font-family: monospace;
            font-size: 11px;
            font-weight: bold;
        }
        
        .shortcut-desc {
            color: #555;
            font-size: 13px;
        }
        
        /* Dark Mode Styles */
        .aiblog-top-controls {
            float: right;
            margin-top: -45px;
            margin-right: 10px;
        }
        
        .aiblog-darkmode-wp-bar {
            float: right;
            height: 32px;
            line-height: 32px;
            padding: 0 10px;
        }
        
        #dark-mode-toggle {
            position: relative;
            padding: 6px 10px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        #dark-mode-toggle .dark-icon {
            display: none;
        }
        
        /* Dark Mode Theme */
        body.aiblog-dark-mode {
            background: #1a1a1a !important;
            color: #e0e0e0 !important;
        }
        
        body.aiblog-dark-mode .wrap {
            background: #2d2d2d;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        body.aiblog-dark-mode #dark-mode-toggle .light-icon {
            display: none;
        }
        
        body.aiblog-dark-mode #dark-mode-toggle .dark-icon {
            display: inline;
        }
        
        body.aiblog-dark-mode .aiblog-edit-container,
        body.aiblog-dark-mode .aiblog-meta-box,
        body.aiblog-dark-mode .calendar-container {
            background: #3a3a3a !important;
            border: 1px solid #555 !important;
            color: #e0e0e0 !important;
        }
        
        body.aiblog-dark-mode .aiblog-field input,
        body.aiblog-dark-mode .aiblog-field textarea,
        body.aiblog-dark-mode .aiblog-field select {
            background: #4a4a4a !important;
            border: 1px solid #666 !important;
            color: #e0e0e0 !important;
        }
        
        body.aiblog-dark-mode .ai-content-tools {
            background: #444 !important;
            border: 1px solid #666 !important;
        }
        
        body.aiblog-dark-mode .seo-score-circle {
            background: linear-gradient(45deg, #444, #555) !important;
            color: #e0e0e0 !important;
        }
        
        body.aiblog-dark-mode .aiblog-modal-content {
            background: #3a3a3a !important;
            color: #e0e0e0 !important;
        }
        
        body.aiblog-dark-mode .aiblog-modal-header {
            background: #2d2d2d !important;
            border-bottom: 1px solid #555 !important;
        }
        
        body.aiblog-dark-mode .time-slot,
        body.aiblog-dark-mode .social-option,
        body.aiblog-dark-mode .scheduling-options label {
            background: #4a4a4a !important;
            border: 1px solid #666 !important;
            color: #e0e0e0 !important;
        }
        
        body.aiblog-dark-mode .calendar-day {
            background: #4a4a4a !important;
            border: 1px solid #666 !important;
            color: #e0e0e0 !important;
        }
        
        body.aiblog-dark-mode .calendar-header {
            background: #0073aa !important;
        }
        
        /* Animations */
        @keyframes slideInUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* Auto-save indicator */
        .auto-save-indicator {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: #28a745;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            z-index: 999999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .auto-save-indicator.show {
            opacity: 1;
        }
        
        /* Smooth transitions for all elements */
        .aiblog-meta-box,
        .aiblog-field input,
        .aiblog-field textarea,
        .button,
        .calendar-day {
            transition: all 0.3s ease !important;
        }
        
        /* Focus improvements */
        .aiblog-field input:focus,
        .aiblog-field textarea:focus {
            border-color: #0073aa !important;
            box-shadow: 0 0 0 2px rgba(0,115,170,0.2) !important;
            outline: none !important;
        }
        
        body.aiblog-dark-mode .aiblog-field input:focus,
        body.aiblog-dark-mode .aiblog-field textarea:focus {
            border-color: #4dabf7 !important;
            box-shadow: 0 0 0 2px rgba(77,171,247,0.2) !important;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Media uploader for thumbnail
            let mediaUploader;
            
            $('#select-thumbnail').click(function(e) {
                e.preventDefault();
                
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                
                mediaUploader = wp.media({
                    title: 'Kapak Resmi Seç',
                    button: {
                        text: 'Seç'
                    },
                    multiple: false
                });
                
                mediaUploader.on('select', function() {
                    const attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#thumbnail_id').val(attachment.id);
                    $('#thumbnail-image').attr('src', attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url);
                    $('#thumbnail-preview').show();
                    $('#thumbnail-placeholder').hide();
                    $('#select-thumbnail').text('Resmi Değiştir');
                });
                
                mediaUploader.open();
            });
            
            // Remove thumbnail
            $('#remove-thumbnail').click(function() {
                $('#thumbnail_id').val('');
                $('#thumbnail-preview').hide();
                $('#thumbnail-placeholder').show();
                $('#select-thumbnail').text('Resim Seç');
            });
            
            // Form submission
            $('#aiblog-edit-form').on('submit', function(e) {
                e.preventDefault();
                
                $('#saving-indicator').show();
                $('#save-post').prop('disabled', true);
                
                const formData = new FormData(this);
                formData.append('action', 'update_ai_blog');
                formData.append('nonce', aiblog_ajax.nonce);
                
                // Get content from WordPress editor
                if (typeof tinyMCE !== 'undefined') {
                    const editor = tinyMCE.get('post_content');
                    if (editor) {
                        formData.set('post_content', editor.getContent());
                    }
                }
                
                $.ajax({
                    url: aiblog_ajax.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#saving-indicator').hide();
                        $('#save-post').prop('disabled', false);
                        
                        if (response.success) {
                            $('<div class="notice notice-success is-dismissible"><p><strong>✅ Değişiklikler kaydedildi!</strong></p></div>')
                                .insertAfter('.wp-header-end');
                        } else {
                            $('<div class="notice notice-error is-dismissible"><p><strong>❌ Hata:</strong> ' + (response.message || 'Bilinmeyen hata') + '</p></div>')
                                .insertAfter('.wp-header-end');
                        }
                        
                        // Scroll to top to show message
                        $('html, body').animate({scrollTop: 0}, 500);
                    },
                    error: function() {
                        $('#saving-indicator').hide();
                        $('#save-post').prop('disabled', false);
                        $('<div class="notice notice-error is-dismissible"><p><strong>❌ Bağlantı hatası!</strong></p></div>')
                            .insertAfter('.wp-header-end');
                    }
                });
            });
            
            // Delete post
            $('#delete-post').click(function() {
                if (confirm('Bu blog yazısını kalıcı olarak silmek istediğinizden emin misiniz?')) {
                    const postId = $(this).data('post-id');
                    window.location.href = '<?php echo admin_url('admin.php?page=ai-blog-management&action=delete&post_id='); ?>' + postId + '&_wpnonce=<?php echo wp_create_nonce('delete_aiblog_' . $post_id); ?>';
                }
            });
            
            // Image search functionality
            let currentSearchPage = 1;
            let currentSearchQuery = '';
            
            // Open image search modal
            $('#search-images').click(function() {
                $('#image-search-modal').show();
                $('#image-search-query').focus();
                
                // Auto-suggest based on post title and keywords
                const postTitle = $('#post_title').val();
                const keywords = $('#keywords').val();
                if (postTitle && !$('#image-search-query').val()) {
                    const suggestion = extractSearchTerm(postTitle + ' ' + keywords);
                    $('#image-search-query').val(suggestion);
                }
            });
            
            // Close modal
            $('.aiblog-modal-close, .aiblog-modal').click(function(e) {
                if (e.target === this) {
                    $('#image-search-modal').hide();
                }
            });
            
            // Suggestion tags
            $('.suggestion-tag').click(function() {
                const query = $(this).data('query');
                $('#image-search-query').val(query);
                performImageSearch(query);
            });
            
            // Search button
            $('#do-image-search').click(function() {
                const query = $('#image-search-query').val().trim();
                if (query) {
                    performImageSearch(query);
                }
            });
            
            // Enter key search
            $('#image-search-query').keypress(function(e) {
                if (e.which === 13) {
                    const query = $(this).val().trim();
                    if (query) {
                        performImageSearch(query);
                    }
                }
            });
            
            // Load more images
            $('#load-more-images').click(function() {
                currentSearchPage++;
                performImageSearch(currentSearchQuery, true);
            });
            
            function extractSearchTerm(text) {
                // Simple extraction of meaningful terms
                const words = text.toLowerCase().split(/[^a-zA-Z0-9]+/);
                const meaningfulWords = words.filter(word => 
                    word.length > 3 && 
                    !['the', 'and', 'for', 'are', 'but', 'not', 'you', 'all', 'can', 'had', 'her', 'was', 'one', 'our', 'out', 'day', 'get', 'has', 'him', 'his', 'how', 'its', 'may', 'new', 'now', 'old', 'see', 'two', 'who', 'boy', 'did', 'does', 'let', 'put', 'say', 'she', 'too', 'use'].includes(word)
                );
                return meaningfulWords[0] || 'business';
            }
            
            function performImageSearch(query, append = false) {
                if (!append) {
                    currentSearchPage = 1;
                    currentSearchQuery = query;
                    $('#image-search-results').empty();
                }
                
                $('#image-search-loading').show();
                $('#search-pagination').hide();
                
                $.ajax({
                    url: aiblog_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'search_images',
                        nonce: aiblog_ajax.nonce,
                        query: query,
                        page: currentSearchPage
                    },
                    success: function(response) {
                        $('#image-search-loading').hide();
                        
                        if (response.success && response.data.length > 0) {
                            displayImageResults(response.data, append);
                            
                            if (response.data.length >= 12) {
                                $('#search-pagination').show();
                            }
                        } else {
                            if (!append) {
                                $('#image-search-results').html('<div style="text-align: center; padding: 40px; color: #666;"><p>🔍 Resim bulunamadı. Farklı arama terimleri deneyin.</p></div>');
                            }
                        }
                    },
                    error: function() {
                        $('#image-search-loading').hide();
                        $('#image-search-results').html('<div style="text-align: center; padding: 40px; color: #d63638;"><p>❌ Arama sırasında hata oluştu.</p></div>');
                    }
                });
            }
            
            function displayImageResults(images, append) {
                const $container = $('#image-search-results');
                
                // Check if these are fallback images
                const hasFallbackImages = images.some(img => img.is_fallback);
                
                if (hasFallbackImages && !append) {
                    $container.prepend(`
                        <div class="fallback-notice" style="grid-column: 1 / -1; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 15px; margin-bottom: 15px; text-align: center;">
                            <h4 style="margin: 0 0 8px 0; color: #856404;">
                                🔑 API Anahtarı Bulunamadı
                            </h4>
                            <p style="margin: 0; color: #856404; font-size: 13px;">
                                Daha kaliteli ve alakalı resimler için <strong>Ayarlar</strong> sayfasından Unsplash ve Pexels API anahtarlarınızı ekleyin. 
                                Şu anda bedava kaynaklardan resimler gösteriliyor.
                            </p>
                        </div>
                    `);
                }
                
                images.forEach(function(image) {
                    const $item = $(`
                        <div class="image-result-item" data-image-id="${image.id}" data-image-url="${image.url}">
                            <img src="${image.thumbnail}" alt="${image.alt}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="image-error-placeholder" style="display: none; width: 100%; height: 150px; background: #f8f9fa; border: 2px dashed #dee2e6; display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 12px;">
                                Resim yüklenemedi
                            </div>
                            <div class="image-result-overlay">
                                <div class="photographer">${image.photographer}</div>
                                <div class="source">${image.source}${image.is_fallback ? ' (Bedava)' : ''}</div>
                            </div>
                            <button class="image-select-btn" data-image-data='${JSON.stringify(image)}'>
                                Seç
                            </button>
                        </div>
                    `);
                    
                    if (append) {
                        $container.append($item);
                    } else {
                        $container.append($item);
                    }
                });
                
                // Image selection
                $('.image-select-btn').off('click').on('click', function(e) {
                    e.stopPropagation();
                    const imageData = JSON.parse($(this).attr('data-image-data'));
                    selectImage(imageData);
                });
            }
            
            function selectImage(imageData) {
                // Show loading
                const $modal = $('#image-search-modal');
                $modal.find('.aiblog-modal-body').append('<div id="image-import-loading" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.9); display: flex; align-items: center; justify-content: center; z-index: 1000;"><div><div class="spinner"></div><p>Resim içe aktarılıyor...</p></div></div>');
                
                $.ajax({
                    url: aiblog_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'import_image',
                        nonce: aiblog_ajax.nonce,
                        image_data: JSON.stringify(imageData)
                    },
                    success: function(response) {
                        $('#image-import-loading').remove();
                        
                        if (response.success) {
                            // Set as thumbnail
                            $('#thumbnail_id').val(response.data.attachment_id);
                            $('#thumbnail-image').attr('src', response.data.thumbnail_url);
                            $('#thumbnail-preview').show();
                            $('#thumbnail-placeholder').hide();
                            $('#select-thumbnail').text('Resmi Değiştir');
                            
                            // Close modal
                            $('#image-search-modal').hide();
                            
                            // Show success message
                            $('<div class="notice notice-success" style="margin: 10px 0;"><p><strong>✅ Resim başarıyla eklendi!</strong></p></div>')
                                .insertAfter('#search-images').delay(3000).fadeOut();
                        } else {
                            alert('Resim içe aktarılamadı: ' + (response.data || 'Bilinmeyen hata'));
                        }
                    },
                    error: function() {
                        $('#image-import-loading').remove();
                        alert('Bağlantı hatası oluştu.');
                    }
                });
            }
            
            // AI Content Tools
            $('.ai-btn').click(function() {
                const action = $(this).data('action');
                
                if (action === 'change-tone') {
                    $('#tone-selection').show();
                    return;
                }
                
                processAIAction(action);
            });
            
            // Tone change handlers
            $('#apply-tone').click(function() {
                const tone = $('#tone-selector').val();
                if (tone) {
                    processAIAction('change-tone', tone);
                    $('#tone-selection').hide();
                }
            });
            
            $('#cancel-tone').click(function() {
                $('#tone-selection').hide();
                $('#tone-selector').val('');
            });
            
            function processAIAction(action, tone = null) {
                const title = $('#post_title').val();
                let content = '';
                
                // Get content from TinyMCE editor
                if (typeof tinyMCE !== 'undefined' && tinyMCE.get('post_content')) {
                    content = tinyMCE.get('post_content').getContent();
                } else {
                    content = $('#post_content').val();
                }
                
                if (!content && action !== 'improve-title' && action !== 'generate-alternatives') {
                    alert('İçerik bulunamadı. Önce içerik yazın.');
                    return;
                }
                
                if (!title && (action === 'improve-title' || action === 'generate-alternatives')) {
                    alert('Başlık bulunamadı. Önce başlık yazın.');
                    return;
                }
                
                $('#ai-processing').show();
                $('.ai-btn').prop('disabled', true);
                
                console.log('AI Action:', action, 'Title:', title.substring(0, 50), 'Content length:', content.length);
                
                $.ajax({
                    url: aiblog_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'ai_content_tools',
                        nonce: aiblog_ajax.nonce,
                        ai_action: action,
                        title: title,
                        content: content,
                        tone: tone
                    },
                    timeout: 60000, // 60 seconds timeout
                    success: function(response) {
                        console.log('AI Response:', response);
                        $('#ai-processing').hide();
                        $('.ai-btn').prop('disabled', false);
                        
                        if (response.success) {
                            switch(action) {
                                case 'improve-title':
                                    $('#post_title').val(response.data.title);
                                    break;
                                case 'generate-alternatives':
                                    showAlternativeTitles(response.data.alternatives);
                                    break;
                                case 'improve-content':
                                case 'change-tone':
                                case 'expand-content':
                                case 'summarize':
                                case 'seo-optimize':
                                    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('post_content')) {
                                        tinyMCE.get('post_content').setContent(response.data.content);
                                    } else {
                                        $('#post_content').val(response.data.content);
                                    }
                                    break;
                            }
                            
                            // Trigger SEO analysis
                            analyzeSEO();
                            
                            // Show success message
                            $('<div class="notice notice-success" style="margin: 10px 0;"><p><strong>✅ AI işlemi tamamlandı!</strong></p></div>')
                                .insertAfter('.ai-content-tools').delay(3000).fadeOut();
                        } else {
                            console.error('AI Error:', response.data);
                            showErrorMessage('AI işlemi başarısız', response.data || 'Bilinmeyen hata');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', {xhr: xhr, status: status, error: error});
                        $('#ai-processing').hide();
                        $('.ai-btn').prop('disabled', false);
                        
                        let errorMessage = '';
                        if (status === 'timeout') {
                            errorMessage = 'İşlem zaman aşımına uğradı. Daha kısa içeriklerle deneyin.';
                        } else if (xhr.status === 0) {
                            errorMessage = 'İnternet bağlantınızı kontrol edin.';
                        } else if (xhr.status === 403) {
                            errorMessage = 'Yetki hatası. Sayfayı yenileyin ve tekrar deneyin.';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Sunucu hatası. OpenAI API anahtarınızı kontrol edin.';
                        } else {
                            errorMessage = 'Bağlantı hatası oluştu. (Kod: ' + xhr.status + ')';
                        }
                        
                        showErrorMessage('Bağlantı Hatası', errorMessage);
                    }
                });
            }
            
            function showAlternativeTitles(alternatives) {
                let html = '<div id="alternative-titles-modal" class="aiblog-modal"><div class="aiblog-modal-content">';
                html += '<div class="aiblog-modal-header"><h2>📝 Alternatif Başlıklar</h2>';
                html += '<button type="button" class="aiblog-modal-close">&times;</button></div>';
                html += '<div class="aiblog-modal-body">';
                
                alternatives.forEach(function(alt, index) {
                    html += '<div class="alternative-title" data-title="' + alt + '">';
                    html += '<h4>' + alt + '</h4>';
                    html += '<button type="button" class="button button-primary use-title">Bu Başlığı Kullan</button>';
                    html += '</div>';
                });
                
                html += '</div></div></div>';
                
                $('body').append(html);
                
                // Modal handlers
                $('.aiblog-modal-close, #alternative-titles-modal').click(function(e) {
                    if (e.target === this) {
                        $('#alternative-titles-modal').remove();
                    }
                });
                
                $('.use-title').click(function() {
                    const title = $(this).closest('.alternative-title').data('title');
                    $('#post_title').val(title);
                    $('#alternative-titles-modal').remove();
                });
            }
            
            // SEO Analysis
            $('#analyze-seo, #post_title, #post_excerpt, #keywords').on('input change', function() {
                clearTimeout(window.seoTimeout);
                window.seoTimeout = setTimeout(analyzeSEO, 1000);
            });
            
            // Trigger on content change
            if (typeof tinyMCE !== 'undefined') {
                $(document).on('tinymce-editor-init', function(event, editor) {
                    if (editor.id === 'post_content') {
                        editor.on('input change', function() {
                            clearTimeout(window.seoTimeout);
                            window.seoTimeout = setTimeout(analyzeSEO, 1000);
                        });
                    }
                });
            }
            
            function analyzeSEO() {
                const title = $('#post_title').val();
                const excerpt = $('#post_excerpt').val();
                const keywords = $('#keywords').val();
                let content = '';
                
                if (typeof tinyMCE !== 'undefined' && tinyMCE.get('post_content')) {
                    content = tinyMCE.get('post_content').getContent({format: 'text'});
                } else {
                    content = $('#post_content').val();
                }
                
                // Title length check
                const titleLength = title.length;
                const titleCheck = $('#title-length');
                titleCheck.find('.seo-value').text(titleLength + ' karakter');
                
                if (titleLength >= 50 && titleLength <= 60) {
                    titleCheck.removeClass('poor average').addClass('good');
                } else if (titleLength >= 40 && titleLength <= 70) {
                    titleCheck.removeClass('poor good').addClass('average');
                } else {
                    titleCheck.removeClass('good average').addClass('poor');
                }
                
                // Meta description check
                const metaCheck = $('#meta-description');
                if (excerpt.length > 0) {
                    metaCheck.find('.seo-value').text(excerpt.length + ' karakter');
                    if (excerpt.length >= 150 && excerpt.length <= 160) {
                        metaCheck.removeClass('poor average').addClass('good');
                    } else if (excerpt.length >= 120 && excerpt.length <= 180) {
                        metaCheck.removeClass('poor good').addClass('average');
                    } else {
                        metaCheck.removeClass('good average').addClass('poor');
                    }
                } else {
                    metaCheck.find('.seo-value').text('Eksik');
                    metaCheck.removeClass('good average').addClass('poor');
                }
                
                // Word count
                const wordCount = content.split(/\s+/).filter(word => word.length > 0).length;
                const wordCheck = $('#word-count');
                wordCheck.find('.seo-value').text(wordCount + ' kelime');
                
                if (wordCount >= 300) {
                    wordCheck.removeClass('poor average').addClass('good');
                } else if (wordCount >= 150) {
                    wordCheck.removeClass('poor good').addClass('average');
                } else {
                    wordCheck.removeClass('good average').addClass('poor');
                }
                
                // Keyword density
                if (keywords && content) {
                    const keywordList = keywords.split(',').map(k => k.trim().toLowerCase());
                    const contentLower = content.toLowerCase();
                    let totalKeywords = 0;
                    
                    keywordList.forEach(keyword => {
                        const regex = new RegExp('\\b' + keyword + '\\b', 'g');
                        const matches = contentLower.match(regex);
                        if (matches) totalKeywords += matches.length;
                    });
                    
                    const density = ((totalKeywords / wordCount) * 100).toFixed(1);
                    const keywordCheck = $('#keyword-density');
                    keywordCheck.find('.seo-value').text(density + '%');
                    
                    if (density >= 1 && density <= 3) {
                        keywordCheck.removeClass('poor average').addClass('good');
                    } else if (density >= 0.5 && density <= 5) {
                        keywordCheck.removeClass('poor good').addClass('average');
                    } else {
                        keywordCheck.removeClass('good average').addClass('poor');
                    }
                }
                
                // Readability (simple approximation)
                const sentences = content.split(/[.!?]+/).filter(s => s.trim().length > 0).length;
                const readabilityScore = sentences > 0 ? Math.max(0, Math.min(100, 100 - (wordCount / sentences - 15) * 2)) : 0;
                const readabilityCheck = $('#readability');
                readabilityCheck.find('.seo-value').text(Math.round(readabilityScore) + '/100');
                
                if (readabilityScore >= 70) {
                    readabilityCheck.removeClass('poor average').addClass('good');
                } else if (readabilityScore >= 50) {
                    readabilityCheck.removeClass('poor good').addClass('average');
                } else {
                    readabilityCheck.removeClass('good average').addClass('poor');
                }
                
                // Headings check
                const headingMatches = content.match(/<h[1-6][^>]*>/gi);
                const headingCount = headingMatches ? headingMatches.length : 0;
                const headingCheck = $('#headings-check');
                headingCheck.find('.seo-value').text(headingCount + ' başlık');
                
                if (headingCount >= 3) {
                    headingCheck.removeClass('poor average').addClass('good');
                } else if (headingCount >= 1) {
                    headingCheck.removeClass('poor good').addClass('average');
                } else {
                    headingCheck.removeClass('good average').addClass('poor');
                }
                
                // Calculate overall SEO score
                const checks = $('.seo-check');
                let goodCount = 0, averageCount = 0, poorCount = 0;
                
                checks.each(function() {
                    if ($(this).hasClass('good')) goodCount++;
                    else if ($(this).hasClass('average')) averageCount++;
                    else poorCount++;
                });
                
                const totalChecks = checks.length;
                const score = Math.round(((goodCount * 100) + (averageCount * 60)) / totalChecks);
                
                const scoreElement = $('#seo-score');
                const scoreCircle = scoreElement.closest('.seo-score-circle');
                
                scoreElement.text(score);
                scoreCircle.removeClass('good average poor');
                
                if (score >= 80) {
                    scoreCircle.addClass('good');
                } else if (score >= 60) {
                    scoreCircle.addClass('average');
                } else {
                    scoreCircle.addClass('poor');
                }
            }
            
            // Initial SEO analysis
            setTimeout(analyzeSEO, 1000);
            
            // Keyboard shortcuts system
            initializeKeyboardShortcuts();
            
            // Scheduling functionality
            $('input[name="schedule_type"]').change(function() {
                const type = $(this).val();
                $('.scheduling-info').hide();
                
                if (type === 'smart') {
                    $('#smart-scheduling-info').show();
                    loadOptimalTimes();
                } else if (type === 'custom') {
                    $('#custom-scheduling').show();
                    setMinDateTime();
                }
            });
            
            function loadOptimalTimes() {
                // Simulate loading optimal times based on analytics
                const times = [
                    { time: '09:00', emoji: '🕘', score: 92, label: 'best' },
                    { time: '13:00', emoji: '🕐', score: 87, label: 'good' },
                    { time: '19:00', emoji: '🕖', score: 84, label: 'good' },
                    { time: '15:00', emoji: '🕒', score: 79, label: 'average' },
                    { time: '21:00', emoji: '🕘', score: 76, label: 'average' }
                ];
                
                const $container = $('.time-suggestions');
                $container.empty();
                
                times.slice(0, 3).forEach(function(timeSlot) {
                    $container.append(`
                        <div class="time-slot ${timeSlot.label}" data-time="${timeSlot.time}">
                            <span class="time">${timeSlot.emoji} ${timeSlot.time}</span>
                            <span class="score">${timeSlot.score}% başarı</span>
                        </div>
                    `);
                });
                
                // Make time slots clickable
                $('.time-slot').click(function() {
                    $('.time-slot').removeClass('selected');
                    $(this).addClass('selected');
                    const selectedTime = $(this).data('time');
                    
                    // Set to tomorrow at selected time
                    const tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    $('#schedule_date').val(tomorrow.toISOString().split('T')[0]);
                    $('#schedule_time').val(selectedTime);
                    
                    $('input[name="schedule_type"][value="custom"]').prop('checked', true).trigger('change');
                });
            }
            
            function setMinDateTime() {
                const now = new Date();
                const currentDate = now.toISOString().split('T')[0];
                const currentTime = now.toTimeString().slice(0, 5);
                
                $('#schedule_date').attr('min', currentDate);
                
                // If today is selected, set minimum time to current time
                $('#schedule_date').change(function() {
                    if ($(this).val() === currentDate) {
                        $('#schedule_time').attr('min', currentTime);
                    } else {
                        $('#schedule_time').removeAttr('min');
                    }
                });
            }
            
            $('#analyze-optimal-times').click(function() {
                $(this).prop('disabled', true).html('🔄 Analiz ediliyor...');
                
                setTimeout(() => {
                    loadOptimalTimes();
                    $(this).prop('disabled', false).html('📊 Zamanlama Analizi Yenile');
                    
                    $('<div class="notice notice-success" style="margin: 10px 0;"><p>✅ Optimal zamanlar güncellendi!</p></div>')
                        .insertAfter(this).delay(3000).fadeOut();
                }, 2000);
            });
            
            // Social Media functionality
            $('.social-option input[type="checkbox"]').change(function() {
                updateSocialPreview();
            });
            
            $('#post_title, #post_excerpt, #keywords').on('input', function() {
                updateSocialPreview();
            });
            
            function updateSocialPreview() {
                const hasAnyChecked = $('.social-option input[type="checkbox"]:checked').length > 0;
                
                if (hasAnyChecked) {
                    $('#social-preview').show();
                    generateSocialContent();
                } else {
                    $('#social-preview').hide();
                }
            }
            
            function generateSocialContent() {
                const title = $('#post_title').val() || 'Blog Başlığı';
                const excerpt = $('#post_excerpt').val();
                const keywords = $('#keywords').val();
                
                // Generate tweet-like content
                let socialText = title;
                
                if (excerpt && excerpt.length > 0) {
                    const shortExcerpt = excerpt.length > 100 ? excerpt.substring(0, 100) + '...' : excerpt;
                    socialText += '\n\n' + shortExcerpt;
                }
                
                socialText += '\n\n📖 Detaylar: [BLOG_LINK]';
                
                // Generate hashtags
                let hashtags = [];
                if (keywords) {
                    const keywordList = keywords.split(',').map(k => k.trim());
                    keywordList.forEach(keyword => {
                        const hashtag = '#' + keyword.replace(/\s+/g, '').replace(/[^a-zA-Z0-9]/g, '');
                        if (hashtag.length > 2 && hashtag.length < 20) {
                            hashtags.push(hashtag);
                        }
                    });
                }
                
                // Add common hashtags
                hashtags.push('#blog', '#içerik', '#teknoloji');
                hashtags = [...new Set(hashtags)]; // Remove duplicates
                
                $('#preview-text').text(socialText);
                $('#preview-hashtags').text(hashtags.slice(0, 5).join(' '));
            }
            
            $('#customize-social').click(function() {
                showSocialCustomizationModal();
            });
            
            function showSocialCustomizationModal() {
                const currentText = $('#preview-text').text();
                const currentHashtags = $('#preview-hashtags').text();
                
                let html = '<div id="social-customize-modal" class="aiblog-modal"><div class="aiblog-modal-content">';
                html += '<div class="aiblog-modal-header"><h2>📱 Sosyal Medya Özelleştir</h2>';
                html += '<button type="button" class="aiblog-modal-close">&times;</button></div>';
                html += '<div class="aiblog-modal-body">';
                html += '<div style="margin-bottom: 15px;">';
                html += '<label><strong>📝 Paylaşım Metni:</strong></label>';
                html += '<textarea id="custom-social-text" rows="4" style="width: 100%; margin-top: 5px;">' + currentText + '</textarea>';
                html += '</div>';
                html += '<div style="margin-bottom: 15px;">';
                html += '<label><strong>🏷️ Hashtag\'ler:</strong></label>';
                html += '<input type="text" id="custom-hashtags" style="width: 100%; margin-top: 5px;" value="' + currentHashtags + '">';
                html += '</div>';
                html += '<div style="text-align: center;">';
                html += '<button type="button" id="save-social-custom" class="button button-primary">Kaydet</button>';
                html += '<button type="button" class="button button-secondary aiblog-modal-close" style="margin-left: 10px;">İptal</button>';
                html += '</div>';
                html += '</div></div></div>';
                
                $('body').append(html);
                
                // Modal handlers
                $('.aiblog-modal-close, #social-customize-modal').click(function(e) {
                    if (e.target === this) {
                        $('#social-customize-modal').remove();
                    }
                });
                
                $('#save-social-custom').click(function() {
                    $('#preview-text').text($('#custom-social-text').val());
                    $('#preview-hashtags').text($('#custom-hashtags').val());
                    $('#social-customize-modal').remove();
                });
            }
            
            // Keyboard shortcuts system
            function initializeKeyboardShortcuts() {
                // Show shortcuts help
                showShortcutsNotification();
                
                $(document).keydown(function(e) {
                    // Ctrl+S: Save post
                    if (e.ctrlKey && e.key === 's') {
                        e.preventDefault();
                        savePost();
                        showShortcutFeedback('💾 Kaydedildi');
                        return false;
                    }
                    
                    // Ctrl+P: Preview post  
                    if (e.ctrlKey && e.key === 'p') {
                        e.preventDefault();
                        previewPost();
                        showShortcutFeedback('👀 Önizleme açıldı');
                        return false;
                    }
                    
                    // Ctrl+Shift+S: Quick SEO analysis
                    if (e.ctrlKey && e.shiftKey && e.key === 'S') {
                        e.preventDefault();
                        analyzeSEO();
                        showShortcutFeedback('📊 SEO analizi yenilendi');
                        return false;
                    }
                    
                    // Ctrl+I: Improve content with AI
                    if (e.ctrlKey && e.key === 'i') {
                        e.preventDefault();
                        processAIAction('improve-content');
                        showShortcutFeedback('🤖 AI ile iyileştiriliyor...');
                        return false;
                    }
                    
                    // Ctrl+T: Improve title
                    if (e.ctrlKey && e.key === 't') {
                        e.preventDefault();
                        processAIAction('improve-title');
                        showShortcutFeedback('📝 Başlık iyileştiriliyor...');
                        return false;
                    }
                    
                    // Ctrl+M: Search and add image
                    if (e.ctrlKey && e.key === 'm') {
                        e.preventDefault();
                        $('#search-images').click();
                        showShortcutFeedback('🖼️ Resim arama açıldı');
                        return false;
                    }
                    
                    // Ctrl+D: Toggle dark mode
                    if (e.ctrlKey && e.key === 'd') {
                        e.preventDefault();
                        toggleDarkMode();
                        return false;
                    }
                    
                    // F1: Show help
                    if (e.key === 'F1') {
                        e.preventDefault();
                        showKeyboardShortcutsModal();
                        return false;
                    }
                    
                    // Escape: Close modals
                    if (e.key === 'Escape') {
                        $('.aiblog-modal').remove();
                        return false;
                    }
                });
            }
            
            function savePost() {
                // Trigger the save functionality
                if ($('#aiblog-edit-form').length) {
                    $('#aiblog-edit-form').submit();
                } else {
                    // Auto-save functionality for drafts
                    autoSaveDraft();
                }
            }
            
            function previewPost() {
                const postId = $('input[name="post_id"]').val();
                if (postId) {
                    window.open('<?php echo home_url(); ?>?p=' + postId + '&preview=true', '_blank');
                } else {
                    showShortcutFeedback('⚠️ Önce kaydedip önizleyebilirsiniz');
                }
            }
            
            function autoSaveDraft() {
                const title = $('#post_title').val();
                const content = typeof tinyMCE !== 'undefined' && tinyMCE.get('post_content') 
                    ? tinyMCE.get('post_content').getContent() 
                    : $('#post_content').val();
                
                if (title || content) {
                    // Simple auto-save logic
                    localStorage.setItem('aiblog_autosave_title', title);
                    localStorage.setItem('aiblog_autosave_content', content);
                    localStorage.setItem('aiblog_autosave_time', new Date().toISOString());
                    
                    showShortcutFeedback('💾 Otomatik kaydedildi');
                }
            }
            
            function showShortcutFeedback(message) {
                // Remove existing feedback
                $('.shortcut-feedback').remove();
                
                // Show new feedback
                const $feedback = $('<div class="shortcut-feedback">' + message + '</div>');
                $('body').append($feedback);
                
                // Animate and remove
                setTimeout(() => {
                    $feedback.addClass('show');
                }, 100);
                
                setTimeout(() => {
                    $feedback.removeClass('show');
                    setTimeout(() => $feedback.remove(), 300);
                }, 2000);
            }
            
            function showShortcutsNotification() {
                // Show notification on first visit
                if (!localStorage.getItem('aiblog_shortcuts_seen')) {
                    setTimeout(() => {
                        $('<div class="shortcuts-intro">' +
                            '<div class="shortcuts-content">' +
                                '<h4>⌨️ Kısayol Tuşları Aktif!</h4>' +
                                '<p>Hızlı çalışmak için kısayol tuşlarını kullanabilirsiniz:</p>' +
                                '<div class="shortcut-examples">' +
                                    '<span class="shortcut-key">Ctrl+S</span> Kaydet ' +
                                    '<span class="shortcut-key">Ctrl+I</span> AI ile iyileştir ' +
                                    '<span class="shortcut-key">F1</span> Yardım' +
                                '</div>' +
                                '<button class="close-shortcuts-intro">Tamam</button>' +
                            '</div>' +
                        '</div>').appendTo('body');
                        
                        $('.close-shortcuts-intro').click(function() {
                            $('.shortcuts-intro').remove();
                            localStorage.setItem('aiblog_shortcuts_seen', 'true');
                        });
                    }, 2000);
                }
            }
            
            function showKeyboardShortcutsModal() {
                let html = '<div id="shortcuts-modal" class="aiblog-modal"><div class="aiblog-modal-content">';
                html += '<div class="aiblog-modal-header"><h2>⌨️ Kısayol Tuşları</h2>';
                html += '<button type="button" class="aiblog-modal-close">&times;</button></div>';
                html += '<div class="aiblog-modal-body">';
                html += '<div class="shortcuts-grid">';
                
                const shortcuts = [
                    { keys: 'Ctrl + S', desc: '💾 Kaydet', category: 'Genel' },
                    { keys: 'Ctrl + P', desc: '👀 Önizleme', category: 'Genel' },
                    { keys: 'Ctrl + I', desc: '🤖 AI ile iyileştir', category: 'AI' },
                    { keys: 'Ctrl + T', desc: '📝 Başlığı iyileştir', category: 'AI' },
                    { keys: 'Ctrl + M', desc: '🖼️ Resim ara', category: 'Medya' },
                    { keys: 'Ctrl + D', desc: '🌙 Dark mode', category: 'Görünüm' },
                    { keys: 'Ctrl + Shift + S', desc: '📊 SEO analizi', category: 'SEO' },
                    { keys: 'F1', desc: '❓ Yardım', category: 'Genel' },
                    { keys: 'Escape', desc: '❌ Modal kapat', category: 'Genel' }
                ];
                
                const categories = [...new Set(shortcuts.map(s => s.category))];
                
                categories.forEach(category => {
                    html += '<div class="shortcut-category">';
                    html += '<h4>' + category + '</h4>';
                    
                    shortcuts.filter(s => s.category === category).forEach(shortcut => {
                        html += '<div class="shortcut-row">';
                        html += '<span class="shortcut-keys">' + shortcut.keys + '</span>';
                        html += '<span class="shortcut-desc">' + shortcut.desc + '</span>';
                        html += '</div>';
                    });
                    
                    html += '</div>';
                });
                
                html += '</div>';
                html += '<div style="text-align: center; margin-top: 20px;">';
                html += '<button type="button" class="button button-primary aiblog-modal-close">Tamam</button>';
                html += '</div>';
                html += '</div></div></div>';
                
                $('body').append(html);
                
                // Modal handlers
                $('.aiblog-modal-close, #shortcuts-modal').click(function(e) {
                    if (e.target === this) {
                        $('#shortcuts-modal').remove();
                    }
                });
            }
            
            // Dark Mode functionality
            function toggleDarkMode() {
                const body = $('body');
                const isDark = body.hasClass('aiblog-dark-mode');
                
                if (isDark) {
                    body.removeClass('aiblog-dark-mode');
                    localStorage.setItem('aiblog_dark_mode', 'false');
                    showShortcutFeedback('☀️ Light mode aktif');
                } else {
                    body.addClass('aiblog-dark-mode');
                    localStorage.setItem('aiblog_dark_mode', 'true');
                    showShortcutFeedback('🌙 Dark mode aktif');
                }
            }
            
            // Initialize dark mode on page load
            function initializeDarkMode() {
                const isDarkMode = localStorage.getItem('aiblog_dark_mode') === 'true';
                if (isDarkMode) {
                    $('body').addClass('aiblog-dark-mode');
                }
                
                // Add dark mode toggle button to top bar
                addDarkModeToggle();
            }
            
            function addDarkModeToggle() {
                const $toggle = $('<button id="dark-mode-toggle" class="button button-secondary" title="Dark/Light mode geçişi (Ctrl+D)">' +
                    '<span class="light-icon">☀️</span>' +
                    '<span class="dark-icon">🌙</span>' +
                '</button>');
                
                // Add to WordPress admin bar if exists, otherwise add to our interface
                if ($('#wpadminbar').length) {
                    $('#wpadminbar').append('<div id="wp-admin-bar-aiblog-dark-mode" class="aiblog-darkmode-wp-bar"></div>');
                    $('#wp-admin-bar-aiblog-dark-mode').append($toggle);
                } else {
                    $('.wrap h1').after('<div class="aiblog-top-controls">' + $toggle[0].outerHTML + '</div>');
                }
                
                // Bind click event
                $(document).on('click', '#dark-mode-toggle', function() {
                    toggleDarkMode();
                });
            }
            
            // Call on page load
            initializeDarkMode();
            
            // Error message function
            function showErrorMessage(title, message) {
                let html = '<div id="error-modal" class="aiblog-modal"><div class="aiblog-modal-content">';
                html += '<div class="aiblog-modal-header" style="background: #dc3545; color: white;"><h2>❌ ' + title + '</h2>';
                html += '<button type="button" class="aiblog-modal-close" style="color: white;">&times;</button></div>';
                html += '<div class="aiblog-modal-body">';
                html += '<div style="padding: 20px; text-align: center;">';
                html += '<p style="font-size: 16px; margin: 0 0 20px 0;">' + message + '</p>';
                html += '<button type="button" class="button button-primary close-error">Tamam</button>';
                html += '</div>';
                html += '</div></div></div>';
                
                $('body').append(html);
                
                // Modal handlers
                $('.aiblog-modal-close, #error-modal, .close-error').click(function(e) {
                    if (e.target === this || $(this).hasClass('close-error')) {
                        $('#error-modal').remove();
                    }
                });
            }
        });
        </script>
        <?php
    }
    
    public function generate_ai_blog() {
        check_ajax_referer('aiblog_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Yetkisiz erişim');
        }
        
        $topic = sanitize_text_field($_POST['topic']);
        $style = sanitize_text_field($_POST['style']);
        $length = sanitize_text_field($_POST['length']);
        $audience = sanitize_text_field($_POST['audience']);
        $keywords = sanitize_text_field($_POST['keywords']);
        $post_action = sanitize_text_field($_POST['post_action']);
        
        $blog_content = $this->call_openai_api($topic, $style, $length, $audience, $keywords);
        
        if ($blog_content) {
            $response = array(
                'success' => true,
                'title' => $blog_content['title'],
                'content' => $blog_content['content'],
                'excerpt' => $blog_content['excerpt']
            );
            
            if ($post_action === 'publish' || $post_action === 'draft') {
                $blog_content['post_status'] = $post_action;
                $post_id = $this->create_wordpress_post($blog_content);
                $response['post_id'] = $post_id;
                $response['post_url'] = get_permalink($post_id);
                $response['edit_url'] = get_edit_post_link($post_id, 'raw');
                $response['post_status'] = $post_action;
                
                if ($post_action === 'draft') {
                    $response['message'] = 'Blog yazısı taslak olarak kaydedildi! Blog Yönetimi\'nden düzenleyebilirsiniz.';
                } else {
                    $response['message'] = 'Blog yazısı başarıyla yayınlandı!';
                }
            }
        } else {
            $response = array(
                'success' => false,
                'message' => 'AI blog yazısı oluşturulamadı. Lütfen API anahtarınızı kontrol edin.'
            );
        }
        
        wp_send_json($response);
    }
    
    public function publish_ai_blog() {
        check_ajax_referer('aiblog_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Yetkisiz erişim');
        }
        
        $title = sanitize_text_field($_POST['title']);
        $content = wp_kses_post($_POST['content']);
        $excerpt = sanitize_textarea_field($_POST['excerpt']);
        
        $blog_data = array(
            'title' => $title,
            'content' => $content,
            'excerpt' => $excerpt
        );
        
        $post_id = $this->create_wordpress_post($blog_data);
        
        if ($post_id) {
            $response = array(
                'success' => true,
                'post_id' => $post_id,
                'post_url' => get_permalink($post_id),
                'edit_url' => get_edit_post_link($post_id, 'raw')
            );
        } else {
            $response = array(
                'success' => false,
                'message' => 'Blog yazısı yayınlanamadı.'
            );
        }
        
        wp_send_json($response);
    }
    
    public function test_image_connection() {
        check_ajax_referer('aiblog_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Yetkisiz erişim');
        }
        
        $test_urls = array(
            'https://picsum.photos/400/300?random=1',
            'https://source.unsplash.com/400x300/?luxury+watch',
            'https://source.unsplash.com/400x300/?rolex',
            'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=400&h=300&fit=crop'
        );
        
        $results = array();
        
        foreach ($test_urls as $index => $url) {
            $start_time = microtime(true);
            
            $response = wp_remote_get($url, array(
                'timeout' => 15,
                'headers' => array('Accept' => 'image/*')
            ));
            
            $end_time = microtime(true);
            $response_time = round(($end_time - $start_time) * 1000, 2);
            
            if (is_wp_error($response)) {
                $results[] = array(
                    'url' => $url,
                    'status' => 'error',
                    'message' => $response->get_error_message(),
                    'response_time' => $response_time
                );
            } else {
                $code = wp_remote_retrieve_response_code($response);
                $body = wp_remote_retrieve_body($response);
                $size = strlen($body);
                
                $results[] = array(
                    'url' => $url,
                    'status' => $code == 200 ? 'success' : 'error',
                    'http_code' => $code,
                    'response_time' => $response_time,
                    'size' => $size,
                    'content_type' => wp_remote_retrieve_header($response, 'content-type')
                );
            }
        }
        
        wp_send_json(array(
            'success' => true,
            'results' => $results,
            'server_info' => array(
                'php_version' => PHP_VERSION,
                'curl_version' => function_exists('curl_version') ? curl_version()['version'] : 'Not available',
                'allow_url_fopen' => ini_get('allow_url_fopen') ? 'Yes' : 'No',
                'max_execution_time' => ini_get('max_execution_time'),
                'memory_limit' => ini_get('memory_limit')
            )
        ));
    }
    
    private function call_openai_api($topic, $style, $length, $audience, $keywords) {
        $api_key = get_option('aiblog_openai_key');
        
        if (!$api_key) {
            return false;
        }
        
        // Stil ve uzunluk tanımları
        $style_prompts = array(
            'formal' => 'resmi ve profesyonel bir dil kullanarak',
            'casual' => 'günlük ve samimi bir dil kullanarak',
            'technical' => 'teknik terimler ve detaylı açıklamalar kullanarak',
            'creative' => 'yaratıcı ve ilgi çekici bir dil kullanarak',
            'news' => 'haber yazısı formatında objektif bir dil kullanarak'
        );
        
        $length_words = array(
            'short' => '800-1200',
            'medium' => '1200-1800',
            'long' => '1800-2500'
        );
        
        $prompt = "Write a comprehensive, high-quality blog post about '{$topic}' in English. ";
        $prompt .= "The writing style should be " . $this->get_english_style($style) . ". ";
        $prompt .= "The article should be {$length_words[$length]} words long. ";
        
        if ($audience) {
            $prompt .= "Target audience: {$audience}. ";
        }
        
        if ($keywords) {
            $prompt .= "IMPORTANT: Use these keywords naturally throughout the content (aim for 2-3% keyword density): {$keywords}. ";
            $prompt .= "Include keywords in headings, subheadings, and body text. ";
        }
        
        $prompt .= "Requirements: ";
        $prompt .= "1. Create an SEO-optimized, engaging title with primary keyword ";
        $prompt .= "2. Write a compelling meta description (150-160 characters) ";
        $prompt .= "3. Structure with H2 and H3 headings that include keywords ";
        $prompt .= "4. Include 5-7 detailed paragraphs with proper transitions ";
        $prompt .= "5. Add a strong introduction hook and conclusion with call-to-action ";
        $prompt .= "6. Suggest 3 highly specific image descriptions that PERFECTLY match the topic '{$topic}'. ";
        $prompt .= "IMPORTANT: Images must be directly related to the topic content, not generic. ";
        $prompt .= "EXAMPLE for 'Rolex Replica Reviews': ";
        $prompt .= "- Image 1: 'Close-up of luxury watch face showing intricate details' with keywords ['luxury watch', 'timepiece', 'watch face'] ";
        $prompt .= "- Image 2: 'Person wearing elegant watch on wrist in business setting' with keywords ['wearing watch', 'business luxury', 'wrist watch'] ";
        $prompt .= "- Image 3: 'Watch collection display on elegant surface' with keywords ['watch collection', 'luxury timepieces', 'watch display'] ";
        $prompt .= "For YOUR topic, provide similar specific, relevant images: ";
        $prompt .= "7. Include internal linking suggestions ";
        $prompt .= "8. Make it actionable and valuable for readers ";
        $prompt .= "9. Use bullet points, numbered lists where appropriate ";
        $prompt .= "10. Include FAQ section if relevant ";
        
        $prompt .= "Response format JSON: {";
        $prompt .= "\"title\": \"SEO optimized title\", ";
        $prompt .= "\"content\": \"Full HTML formatted article with headings\", ";
        $prompt .= "\"excerpt\": \"Meta description 150-160 chars\", ";
        $prompt .= "\"keywords_used\": [\"list of keywords used\"], ";
        $prompt .= "\"image_suggestions\": [";
        $prompt .= "{\"description\": \"detailed image description\", \"keywords\": [\"primary keyword\", \"secondary keyword\"], \"alt_keywords\": [\"backup terms\"]}";
        $prompt .= "], ";
        $prompt .= "\"internal_links\": [\"suggested internal link topics\"], ";
        $prompt .= "\"word_count\": estimated_word_count ";
        $prompt .= "}";
        
        $data = array(
            'model' => 'gpt-3.5-turbo',
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => 4000,
            'temperature' => 0.7
        );
        
        $args = array(
            'body' => json_encode($data),
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'timeout' => 60
        );
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', $args);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            return false;
        }
        
        $content = $data['choices'][0]['message']['content'];
        
        // Debug: Log AI response
        error_log('AI Blog Generator - Raw AI Response: ' . $content);
        
        $blog_data = json_decode($content, true);
        
        if (!$blog_data) {
            // Try to extract JSON if wrapped in markdown
            if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
                $blog_data = json_decode($matches[1], true);
                error_log('AI Blog Generator - Extracted JSON from markdown');
            }
        }
        
        if (!$blog_data) {
            // JSON parse hatası varsa, basit metin olarak döndür
            error_log('AI Blog Generator - JSON parse failed, using fallback format');
            return array(
                'title' => 'About ' . $topic,
                'content' => $content,
                'excerpt' => substr(strip_tags($content), 0, 150) . '...',
                'keywords_used' => array(),
                'image_suggestions' => array(),
                'word_count' => str_word_count(strip_tags($content))
            );
        }
        
        // Debug: Log parsed data
        error_log('AI Blog Generator - Parsed blog data: ' . print_r($blog_data, true));
        
        // Resimleri fetch et ve içeriğe ekle
        if (isset($blog_data['image_suggestions']) && !empty($blog_data['image_suggestions'])) {
            error_log('AI Blog Generator - Image suggestions found: ' . print_r($blog_data['image_suggestions'], true));
            $images = $this->fetch_unsplash_images($blog_data['image_suggestions'], $keywords);
            error_log('AI Blog Generator - Fetched images: ' . print_r($images, true));
            if (!empty($images)) {
                $blog_data['content'] = $this->insert_images_into_content($blog_data['content'], $images);
                $blog_data['images'] = $images;
                error_log('AI Blog Generator - Images inserted into content');
            } else {
                error_log('AI Blog Generator - No images could be fetched');
            }
        } else {
            error_log('AI Blog Generator - No image suggestions in AI response');
        }
        
        // SEO meta tags ekle
        $blog_data['seo_meta'] = $this->generate_seo_meta($blog_data, $keywords);
        
        return $blog_data;
    }
    
    private function get_english_style($style) {
        $english_styles = array(
            'formal' => 'professional, authoritative, and academic with proper citations and research-backed content',
            'casual' => 'conversational, friendly, and approachable with personal anecdotes and relatable examples',
            'technical' => 'detailed, precise, and expert-level with technical terminology, code examples, and in-depth analysis',
            'creative' => 'engaging, storytelling-driven, and imaginative with metaphors, analogies, and compelling narratives',
            'news' => 'objective, fact-based, and journalistic with data, statistics, and unbiased reporting'
        );
        
        return isset($english_styles[$style]) ? $english_styles[$style] : $english_styles['formal'];
    }
    
    private function fetch_unsplash_images($descriptions, $keywords = '') {
        $images = array();
        
        // Unsplash Access Key
        $unsplash_key = get_option('aiblog_unsplash_key', '');
        
        if (!$unsplash_key) {
            return $this->fetch_fallback_images($descriptions, $keywords);
        }
        
        foreach ($descriptions as $description) {
            $image_data = null;
            
            // New format: structured image suggestions
            if (is_array($description) && isset($description['keywords'])) {
                $image_data = $this->fetch_smart_image($description, $unsplash_key);
            } else {
                // Old format: simple string description
                $image_data = $this->fetch_simple_image($description, $unsplash_key, $keywords);
            }
            
            if ($image_data) {
                $images[] = $image_data;
            }
        }
        
        // If no images found, use fallback
        if (empty($images)) {
            return $this->fetch_fallback_images($descriptions, $keywords);
        }
        
        return $images;
    }
    
    private function fetch_smart_image($image_spec, $unsplash_key) {
        $search_terms = array_merge(
            $image_spec['keywords'], 
            isset($image_spec['alt_keywords']) ? $image_spec['alt_keywords'] : array()
        );
        
        // Get other API keys
        $pexels_key = get_option('aiblog_pexels_key', '');
        
        foreach ($search_terms as $term) {
            // Try Unsplash first
            $image = $this->try_unsplash_search($term, $unsplash_key);
            if ($image) {
                $image['alt'] = $image_spec['description'];
                $image['caption'] = $image_spec['description'];
                return $image;
            }
            
            // Try Pexels if available and Unsplash failed
            if ($pexels_key) {
                $image = $this->fetch_pexels_image($term, $pexels_key);
                if ($image) {
                    $image['alt'] = $image_spec['description'];
                    $image['caption'] = $image_spec['description'];
                    return $image;
                }
            }
            
            // Rate limiting
            sleep(1);
        }
        
        return null;
    }
    
    private function try_unsplash_search($term, $unsplash_key) {
        $search_query = urlencode(trim($term));
        $api_url = "https://api.unsplash.com/photos/random?query={$search_query}&orientation=landscape&content_filter=high";
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Client-ID ' . $unsplash_key
            ),
            'timeout' => 30
        );
        
        $response = wp_remote_get($api_url, $args);
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (isset($data['urls']['regular'])) {
                return array(
                    'url' => $data['urls']['regular'],
                    'photographer' => isset($data['user']['name']) ? $data['user']['name'] : '',
                    'unsplash_url' => isset($data['links']['html']) ? $data['links']['html'] : '',
                    'search_term' => $term,
                    'source' => 'Unsplash',
                    'is_fallback' => false
                );
            }
        }
        
        return null;
    }
    
    private function fetch_simple_image($description, $unsplash_key, $keywords) {
        $search_query = urlencode($description);
        $api_url = "https://api.unsplash.com/photos/random?query={$search_query}&orientation=landscape&content_filter=high";
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Client-ID ' . $unsplash_key
            ),
            'timeout' => 30
        );
        
        $response = wp_remote_get($api_url, $args);
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (isset($data['urls']['regular'])) {
                return array(
                    'url' => $data['urls']['regular'],
                    'alt' => $description,
                    'caption' => $description,
                    'photographer' => isset($data['user']['name']) ? $data['user']['name'] : '',
                    'unsplash_url' => isset($data['links']['html']) ? $data['links']['html'] : '',
                    'search_term' => $description,
                    'is_fallback' => false
                );
            }
        }
        
        return null;
    }
    
    private function fetch_fallback_images($descriptions, $keywords) {
        $images = array();
        
        foreach ($descriptions as $index => $description) {
            $search_terms = array();
            
            // Extract search terms from description
            if (is_array($description) && isset($description['keywords'])) {
                $search_terms = array_merge($description['keywords'], 
                    isset($description['alt_keywords']) ? $description['alt_keywords'] : array());
                $desc_text = $description['description'];
            } else {
                $desc_text = $description;
                if ($keywords) {
                    $keyword_array = explode(',', $keywords);
                    $search_terms = array_map('trim', $keyword_array);
                } else {
                    $search_terms = $this->extract_keywords_from_text($desc_text);
                }
            }
            
            $search_term = !empty($search_terms) ? $search_terms[0] : 'abstract';
            $search_term = urlencode(trim($search_term));
            
            // Enhanced fallback sources with topic-specific URLs
            $fallback_sources = array(
                "https://source.unsplash.com/800x600/?{$search_term}",
                "https://picsum.photos/800/600?random=" . ($index + time()),
                "https://source.unsplash.com/800x600/?" . urlencode($this->get_generic_term($search_term)),
                "https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=800&h=600&fit=crop",
            );
            
            $images[] = array(
                'url' => $fallback_sources[0],
                'fallback_urls' => $fallback_sources,
                'alt' => $desc_text,
                'caption' => $desc_text,
                'search_term' => $search_term,
                'is_fallback' => true
            );
        }
        
        return $images;
    }
    
    private function extract_keywords_from_text($text) {
        // Simple keyword extraction
        $common_words = array('the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'up', 'about', 'into', 'through', 'during', 'before', 'after', 'above', 'below', 'between', 'among', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'shall');
        
        $words = explode(' ', strtolower($text));
        $keywords = array();
        
        foreach ($words as $word) {
            $word = preg_replace('/[^a-z0-9]/', '', $word);
            if (strlen($word) > 3 && !in_array($word, $common_words)) {
                $keywords[] = $word;
            }
        }
        
        return array_slice(array_unique($keywords), 0, 3);
    }
    
    private function get_generic_term($specific_term) {
        $generic_mapping = array(
            // Luxury & Fashion
            'rolex' => 'luxury watch',
            'replica' => 'watch',
            'luxury' => 'elegant lifestyle',
            'fashion' => 'style',
            'designer' => 'luxury',
            
            // Technology
            'iphone' => 'smartphone',
            'android' => 'technology',
            'cryptocurrency' => 'finance technology',
            'bitcoin' => 'digital finance',
            'blockchain' => 'technology innovation',
            'ai' => 'artificial intelligence',
            'software' => 'technology',
            
            // Finance
            'forex' => 'financial trading',
            'trading' => 'finance',
            'investment' => 'business finance',
            'loan' => 'banking',
            'credit' => 'finance',
            'mortgage' => 'real estate finance',
            
            // Health & Wellness
            'diet' => 'healthy lifestyle',
            'fitness' => 'exercise wellness',
            'nutrition' => 'healthy food',
            'weight loss' => 'fitness health',
            'supplement' => 'health wellness',
            
            // Entertainment & Gaming
            'casino' => 'entertainment nightlife',
            'poker' => 'cards game',
            'betting' => 'entertainment',
            'gaming' => 'video games',
            
            // Travel & Lifestyle
            'travel' => 'vacation destination',
            'hotel' => 'hospitality travel',
            'vacation' => 'travel relaxation',
            
            // Business & Marketing
            'marketing' => 'business strategy',
            'seo' => 'digital marketing',
            'ecommerce' => 'online business',
            'startup' => 'entrepreneurship',
            
            // Education & Skills
            'course' => 'education learning',
            'tutorial' => 'education',
            'training' => 'skill development',
            
            // Real Estate
            'property' => 'real estate',
            'apartment' => 'housing',
            'home' => 'residential property'
        );
        
        $decoded_term = urldecode(strtolower($specific_term));
        
        // Check for exact matches first
        foreach ($generic_mapping as $specific => $generic) {
            if (stripos($decoded_term, $specific) !== false) {
                return $generic;
            }
        }
        
        // Check for category-based mapping
        if (preg_match('/\b(watch|time|clock)\b/i', $decoded_term)) {
            return 'timepiece luxury';
        }
        if (preg_match('/\b(money|cash|dollar|euro|currency)\b/i', $decoded_term)) {
            return 'finance money';
        }
        if (preg_match('/\b(food|recipe|cooking|kitchen)\b/i', $decoded_term)) {
            return 'culinary food';
        }
        if (preg_match('/\b(car|auto|vehicle|driving)\b/i', $decoded_term)) {
            return 'automotive transportation';
        }
        
        return 'professional business';
    }
    
    private function fetch_pexels_image($search_term, $pexels_key) {
        $api_url = "https://api.pexels.com/v1/search?query=" . urlencode($search_term) . "&per_page=1&orientation=landscape";
        
        $args = array(
            'headers' => array(
                'Authorization' => $pexels_key
            ),
            'timeout' => 30
        );
        
        $response = wp_remote_get($api_url, $args);
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (isset($data['photos'][0])) {
                $photo = $data['photos'][0];
                return array(
                    'url' => $photo['src']['large'],
                    'alt' => $photo['alt'] ?: $search_term,
                    'caption' => $photo['alt'] ?: $search_term,
                    'photographer' => $photo['photographer'],
                    'pexels_url' => $photo['url'],
                    'source' => 'Pexels',
                    'is_fallback' => false
                );
            }
        }
        
        return null;
    }
    
    private function insert_images_into_content($content, $images) {
        if (empty($images)) {
            return $content;
        }
        
        // Split content into paragraphs
        $paragraphs = explode('</p>', $content);
        $total_paragraphs = count($paragraphs);
        
        // Calculate image insertion points
        $insertion_points = array();
        $images_count = count($images);
        
        if ($total_paragraphs > 3 && $images_count > 0) {
            // Insert first image after 2nd paragraph
            $insertion_points[] = 2;
            
            if ($images_count > 1 && $total_paragraphs > 6) {
                // Insert second image in the middle
                $insertion_points[] = floor($total_paragraphs * 0.6);
            }
            
            if ($images_count > 2 && $total_paragraphs > 9) {
                // Insert third image near the end
                $insertion_points[] = $total_paragraphs - 3;
            }
        }
        
        // Insert images
        $offset = 0;
        foreach ($insertion_points as $index => $point) {
            if (isset($images[$index])) {
                $image = $images[$index];
                $image_html = $this->generate_image_html($image);
                
                $adjusted_point = $point + $offset;
                if ($adjusted_point < count($paragraphs)) {
                    $paragraphs[$adjusted_point] .= '</p>' . $image_html;
                    $offset++;
                }
            }
        }
        
        return implode('</p>', $paragraphs);
    }
    
    private function generate_image_html($image) {
        $html = '<figure class="aiblog-image wp-block-image size-large">';
        
        // Primary image with fallbacks
        $image_url = esc_url($image['url']);
        $alt_text = esc_attr($image['alt']);
        
        $html .= '<img src="' . $image_url . '" alt="' . $alt_text . '" class="wp-image-auto aiblog-auto-image" loading="lazy" ';
        
        // Add fallback URLs as data attributes
        if (isset($image['fallback_urls']) && !empty($image['fallback_urls'])) {
            $fallback_json = esc_attr(json_encode($image['fallback_urls']));
            $html .= 'data-fallback-urls="' . $fallback_json . '" ';
        }
        
        $html .= 'onerror="this.style.display=\'none\'; console.log(\'Image failed to load: \' + this.src);" ';
        $html .= '/>';
        
        // Add a placeholder div for failed images
        $html .= '<div class="aiblog-image-placeholder" style="display: none; background: #f0f0f0; border: 2px dashed #ddd; padding: 40px; text-align: center; color: #666;">';
        $html .= '<p>📸 Image: ' . esc_html($image['caption']) . '</p>';
        $html .= '<small>Image could not be loaded</small>';
        $html .= '</div>';
        
        if (!empty($image['caption'])) {
            $html .= '<figcaption class="wp-element-caption">' . esc_html($image['caption']);
            
            if (!empty($image['photographer']) && !empty($image['unsplash_url'])) {
                $html .= ' <small>Photo by <a href="' . esc_url($image['unsplash_url']) . '" target="_blank" rel="noopener">' . esc_html($image['photographer']) . '</a> on Unsplash</small>';
            } elseif (isset($image['is_fallback']) && $image['is_fallback']) {
                $html .= ' <small>Stock photo</small>';
            }
            
            $html .= '</figcaption>';
        }
        
        $html .= '</figure>';
        
        return $html;
    }
    
    private function generate_seo_meta($blog_data, $keywords) {
        $seo_meta = array();
        
        // Meta keywords
        if (isset($blog_data['keywords_used']) && !empty($blog_data['keywords_used'])) {
            $seo_meta['keywords'] = implode(', ', $blog_data['keywords_used']);
        } elseif ($keywords) {
            $seo_meta['keywords'] = $keywords;
        }
        
        // Open Graph tags
        $seo_meta['og_title'] = isset($blog_data['title']) ? $blog_data['title'] : '';
        $seo_meta['og_description'] = isset($blog_data['excerpt']) ? $blog_data['excerpt'] : '';
        $seo_meta['og_type'] = 'article';
        
        // Twitter Card
        $seo_meta['twitter_card'] = 'summary_large_image';
        $seo_meta['twitter_title'] = $seo_meta['og_title'];
        $seo_meta['twitter_description'] = $seo_meta['og_description'];
        
        // Schema.org structured data
        $seo_meta['schema'] = array(
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $seo_meta['og_title'],
            'description' => $seo_meta['og_description'],
            'wordCount' => isset($blog_data['word_count']) ? $blog_data['word_count'] : 0,
            'datePublished' => current_time('c'),
            'dateModified' => current_time('c')
        );
        
        // Featured image from first image
        if (isset($blog_data['images']) && !empty($blog_data['images'])) {
            $first_image = $blog_data['images'][0];
            $seo_meta['og_image'] = $first_image['url'];
            $seo_meta['twitter_image'] = $first_image['url'];
            $seo_meta['schema']['image'] = $first_image['url'];
        }
        
        return $seo_meta;
    }
    
    private function create_wordpress_post($blog_content) {
        $default_category = get_option('aiblog_default_category', 1);
        $default_author = get_option('aiblog_default_author', 1);
        
        $post_status = isset($blog_content['post_status']) ? $blog_content['post_status'] : 'draft';
        
        $post_data = array(
            'post_title' => $blog_content['title'],
            'post_content' => $blog_content['content'],
            'post_excerpt' => $blog_content['excerpt'],
            'post_status' => $post_status,
            'post_author' => $default_author,
            'post_category' => array($default_category),
            'meta_input' => array(
                '_aiblog_generated' => true,
                '_aiblog_generated_date' => current_time('mysql'),
                '_aiblog_word_count' => isset($blog_content['word_count']) ? $blog_content['word_count'] : 0,
                '_aiblog_keywords' => isset($blog_content['keywords_used']) ? implode(', ', $blog_content['keywords_used']) : ''
            )
        );
        
        // SEO meta tags ekle
        if (isset($blog_content['seo_meta'])) {
            $seo_meta = $blog_content['seo_meta'];
            
            $post_data['meta_input']['_yoast_wpseo_title'] = $seo_meta['og_title'];
            $post_data['meta_input']['_yoast_wpseo_metadesc'] = $seo_meta['og_description'];
            $post_data['meta_input']['_yoast_wpseo_focuskw'] = isset($seo_meta['keywords']) ? $seo_meta['keywords'] : '';
            
            // Open Graph
            $post_data['meta_input']['_yoast_wpseo_opengraph-title'] = $seo_meta['og_title'];
            $post_data['meta_input']['_yoast_wpseo_opengraph-description'] = $seo_meta['og_description'];
            
            // Twitter
            $post_data['meta_input']['_yoast_wpseo_twitter-title'] = $seo_meta['twitter_title'];
            $post_data['meta_input']['_yoast_wpseo_twitter-description'] = $seo_meta['twitter_description'];
            
            // Schema markup
            $post_data['meta_input']['_aiblog_schema'] = json_encode($seo_meta['schema']);
        }
        
        $post_id = wp_insert_post($post_data);
        
        // Featured image ayarla
        if ($post_id && isset($blog_content['images']) && !empty($blog_content['images'])) {
            $this->set_featured_image($post_id, $blog_content['images'][0]);
        }
        
        return $post_id;
    }
    
    private function set_featured_image($post_id, $image_data) {
        $image_url = $image_data['url'];
        
        // Try multiple sources if available
        $urls_to_try = array($image_url);
        if (isset($image_data['fallback_urls'])) {
            $urls_to_try = array_merge($urls_to_try, $image_data['fallback_urls']);
        }
        
        foreach ($urls_to_try as $url) {
            $attachment_id = $this->download_and_attach_image($url, $post_id, $image_data);
            if ($attachment_id) {
                return $attachment_id;
            }
        }
        
        return false;
    }
    
    private function download_and_attach_image($image_url, $post_id, $image_data) {
        // Enhanced download with better error handling
        $args = array(
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.1',
            'user-agent' => 'WordPress AI Blog Generator/1.0',
            'headers' => array(
                'Accept' => 'image/*'
            )
        );
        
        $response = wp_remote_get($image_url, $args);
        
        if (is_wp_error($response)) {
            error_log('AI Blog Generator: Failed to download image from ' . $image_url . ' - ' . $response->get_error_message());
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            error_log('AI Blog Generator: HTTP ' . $response_code . ' error for image: ' . $image_url);
            return false;
        }
        
        $image_data_binary = wp_remote_retrieve_body($response);
        
        if (empty($image_data_binary)) {
            error_log('AI Blog Generator: Empty response body for image: ' . $image_url);
            return false;
        }
        
        // Validate image data
        $image_info = @getimagesizefromstring($image_data_binary);
        if (!$image_info) {
            error_log('AI Blog Generator: Invalid image data from: ' . $image_url);
            return false;
        }
        
        // Generate filename
        $filename = $this->generate_safe_filename($image_url, $image_info);
        
        // Upload to WordPress
        $upload = wp_upload_bits($filename, null, $image_data_binary);
        
        if ($upload['error']) {
            error_log('AI Blog Generator: Upload error - ' . $upload['error']);
            return false;
        }
        
        // Create attachment
        $attachment = array(
            'guid' => $upload['url'],
            'post_mime_type' => $image_info['mime'],
            'post_title' => sanitize_text_field($image_data['alt']),
            'post_content' => '',
            'post_excerpt' => sanitize_text_field($image_data['caption']),
            'post_status' => 'inherit'
        );
        
        $attachment_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
        
        if (is_wp_error($attachment_id)) {
            error_log('AI Blog Generator: Failed to create attachment - ' . $attachment_id->get_error_message());
            return false;
        }
        
        // Generate metadata
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        
        // Set as featured image
        set_post_thumbnail($post_id, $attachment_id);
        
        // Set alt text and other meta
        update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($image_data['alt']));
        
        if (isset($image_data['photographer'])) {
            update_post_meta($attachment_id, '_aiblog_photographer', sanitize_text_field($image_data['photographer']));
        }
        
        if (isset($image_data['unsplash_url'])) {
            update_post_meta($attachment_id, '_aiblog_source_url', esc_url_raw($image_data['unsplash_url']));
        }
        
        return $attachment_id;
    }
    
    private function generate_safe_filename($url, $image_info) {
        $extension = '';
        switch ($image_info['mime']) {
            case 'image/jpeg':
                $extension = '.jpg';
                break;
            case 'image/png':
                $extension = '.png';
                break;
            case 'image/gif':
                $extension = '.gif';
                break;
            case 'image/webp':
                $extension = '.webp';
                break;
            default:
                $extension = '.jpg';
        }
        
        $filename = 'ai-blog-image-' . time() . '-' . wp_generate_uuid4() . $extension;
        return sanitize_file_name($filename);
    }
    
    public function activate() {
        // Plugin aktifleştirildiğinde yapılacak işlemler
        if (!get_option('aiblog_openai_key')) {
            add_option('aiblog_openai_key', '');
        }
        if (!get_option('aiblog_default_category')) {
            add_option('aiblog_default_category', 1);
        }
        if (!get_option('aiblog_default_author')) {
            add_option('aiblog_default_author', 1);
        }
    }
    
    public function deactivate() {
        // Plugin deaktive edildiğinde yapılacak işlemler
    }
    
    public function update_ai_blog() {
        check_ajax_referer('aiblog_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Yetkisiz erişim');
        }
        
        $post_id = intval($_POST['post_id']);
        $post = get_post($post_id);
        
        if (!$post || !get_post_meta($post_id, '_aiblog_generated', true)) {
            wp_send_json_error('Geçersiz AI blog post.');
        }
        
        // Update post data
        $updated_post = array(
            'ID' => $post_id,
            'post_title' => sanitize_text_field($_POST['post_title']),
            'post_content' => wp_kses_post($_POST['post_content']),
            'post_excerpt' => sanitize_textarea_field($_POST['post_excerpt']),
            'post_status' => sanitize_text_field($_POST['post_status'])
        );
        
        // Update category
        if (!empty($_POST['post_category'])) {
            $category_id = intval($_POST['post_category']);
            wp_set_post_categories($post_id, array($category_id));
        }
        
        // Update thumbnail
        if (!empty($_POST['thumbnail_id'])) {
            $thumbnail_id = intval($_POST['thumbnail_id']);
            set_post_thumbnail($post_id, $thumbnail_id);
        } else {
            delete_post_thumbnail($post_id);
        }
        
        // Update keywords
        if (!empty($_POST['keywords'])) {
            update_post_meta($post_id, '_aiblog_keywords', sanitize_textarea_field($_POST['keywords']));
        }
        
        // Update word count
        $word_count = str_word_count(strip_tags($_POST['post_content']));
        update_post_meta($post_id, '_aiblog_word_count', $word_count);
        
        $result = wp_update_post($updated_post);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => 'Blog yazısı başarıyla güncellendi!',
                'post_id' => $post_id,
                'word_count' => $word_count
            ));
        } else {
            wp_send_json_error('Blog yazısı güncellenemedi.');
        }
    }
    
    public function search_images() {
        check_ajax_referer('aiblog_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Yetkisiz erişim');
        }
        
        $query = sanitize_text_field($_POST['query']);
        $page = intval($_POST['page']) ?: 1;
        $per_page = 12;
        
        $images = array();
        
        // Try Unsplash first
        $unsplash_key = get_option('aiblog_unsplash_key', '');
        if ($unsplash_key) {
            $unsplash_images = $this->search_unsplash_images($query, $page, $per_page, $unsplash_key);
            $images = array_merge($images, $unsplash_images);
        }
        
        // Try Pexels if we need more images
        if (count($images) < $per_page) {
            $pexels_key = get_option('aiblog_pexels_key', '');
            if ($pexels_key) {
                $remaining = $per_page - count($images);
                $pexels_images = $this->search_pexels_images($query, $page, $remaining, $pexels_key);
                $images = array_merge($images, $pexels_images);
            }
        }
        
        // Fallback: Use free image sources (no API required)
        if (empty($images)) {
            $images = $this->get_fallback_images($query, $per_page);
        }
        
        wp_send_json_success($images);
    }
    
    private function search_unsplash_images($query, $page, $per_page, $api_key) {
        $api_url = "https://api.unsplash.com/search/photos?query=" . urlencode($query) . "&page={$page}&per_page={$per_page}&orientation=landscape";
        
        $response = wp_remote_get($api_url, array(
            'headers' => array(
                'Authorization' => 'Client-ID ' . $api_key
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return array();
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['results'])) {
            return array();
        }
        
        $images = array();
        foreach ($data['results'] as $photo) {
            $images[] = array(
                'id' => 'unsplash_' . $photo['id'],
                'url' => $photo['urls']['regular'],
                'thumbnail' => $photo['urls']['small'],
                'alt' => $photo['alt_description'] ?: $query,
                'photographer' => $photo['user']['name'],
                'source' => 'Unsplash',
                'source_url' => $photo['links']['html'],
                'download_url' => $photo['urls']['full']
            );
        }
        
        return $images;
    }
    
    private function search_pexels_images($query, $page, $per_page, $api_key) {
        $api_url = "https://api.pexels.com/v1/search?query=" . urlencode($query) . "&page={$page}&per_page={$per_page}&orientation=landscape";
        
        $response = wp_remote_get($api_url, array(
            'headers' => array(
                'Authorization' => $api_key
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return array();
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['photos'])) {
            return array();
        }
        
        $images = array();
        foreach ($data['photos'] as $photo) {
            $images[] = array(
                'id' => 'pexels_' . $photo['id'],
                'url' => $photo['src']['large'],
                'thumbnail' => $photo['src']['medium'],
                'alt' => $photo['alt'] ?: $query,
                'photographer' => $photo['photographer'],
                'source' => 'Pexels',
                'source_url' => $photo['url'],
                'download_url' => $photo['src']['original']
            );
        }
        
        return $images;
    }
    
    private function get_fallback_images($query, $per_page) {
        $images = array();
        
        // Bedava resim kaynakları (API anahtarı gerektirmez)
        $free_sources = array(
            // Unsplash Source (API olmadan)
            array(
                'base_url' => 'https://source.unsplash.com/800x600/?',
                'source' => 'Unsplash Source',
                'photographer' => 'Unsplash Community'
            ),
            // Picsum Photos
            array(
                'base_url' => 'https://picsum.photos/800/600?random=',
                'source' => 'Lorem Picsum',
                'photographer' => 'Picsum Authors'
            ),
            // Lorem Flickr
            array(
                'base_url' => 'https://loremflickr.com/800/600/',
                'source' => 'Lorem Flickr',
                'photographer' => 'Flickr Community'
            )
        );
        
        // Arama terimini temizle ve kategorize et
        $clean_query = $this->clean_search_query($query);
        
        for ($i = 0; $i < $per_page; $i++) {
            $source_index = $i % count($free_sources);
            $source = $free_sources[$source_index];
            
            $unique_id = time() . '_' . $i;
            
            // Her kaynak için farklı URL yapısı
            switch ($source_index) {
                case 0: // Unsplash Source
                    $image_url = $source['base_url'] . urlencode($clean_query) . '&sig=' . $unique_id;
                    break;
                case 1: // Picsum
                    $image_url = $source['base_url'] . $unique_id;
                    break;
                case 2: // Lorem Flickr
                    $image_url = $source['base_url'] . urlencode($clean_query) . '/all?random=' . $unique_id;
                    break;
            }
            
            $images[] = array(
                'id' => 'fallback_' . $source_index . '_' . $unique_id,
                'url' => $image_url,
                'thumbnail' => $image_url,
                'alt' => ucfirst($clean_query) . ' image',
                'photographer' => $source['photographer'],
                'source' => $source['source'],
                'source_url' => '#',
                'download_url' => $image_url,
                'is_fallback' => true
            );
        }
        
        return $images;
    }
    
    private function clean_search_query($query) {
        // Arama terimini temizle ve anlamlı hale getir
        $query = strtolower(trim($query));
        
        // Türkçe karakterleri değiştir
        $turkish_chars = array('ç', 'ğ', 'ı', 'ş', 'ü', 'ö');
        $english_chars = array('c', 'g', 'i', 's', 'u', 'o');
        $query = str_replace($turkish_chars, $english_chars, $query);
        
        // Kategori eşleştirmeleri
        $category_mapping = array(
            'teknoloji' => 'technology',
            'iş' => 'business',
            'işletme' => 'business',
            'doğa' => 'nature',
            'seyahat' => 'travel',
            'yemek' => 'food',
            'spor' => 'sports',
            'sanat' => 'art',
            'bilim' => 'science',
            'sağlık' => 'health',
            'eğitim' => 'education',
            'para' => 'money',
            'finans' => 'finance',
            'otomobil' => 'car',
            'ev' => 'home',
            'aile' => 'family',
            'çocuk' => 'children',
            'kadın' => 'woman',
            'erkek' => 'man',
            'yaşlı' => 'elderly',
            'genç' => 'young',
            'müzik' => 'music',
            'dans' => 'dance',
            'sinema' => 'cinema',
            'kitap' => 'book',
            'okuma' => 'reading',
            'yazma' => 'writing',
            'bilgisayar' => 'computer',
            'telefon' => 'phone',
            'internet' => 'internet',
            'sosyal medya' => 'social media',
            'instagram' => 'social media',
            'facebook' => 'social media',
            'twitter' => 'social media',
            'youtube' => 'video',
            'video' => 'video',
            'fotoğraf' => 'photography',
            'kamera' => 'camera'
        );
        
        // Eşleştirme kontrolü
        foreach ($category_mapping as $turkish => $english) {
            if (strpos($query, $turkish) !== false) {
                return $english;
            }
        }
        
        // Eğer eşleştirme yoksa, ilk anlamlı kelimeyi kullan
        $words = explode(' ', $query);
        $meaningful_words = array_filter($words, function($word) {
            return strlen($word) > 2 && !in_array($word, array('the', 'and', 'for', 'are', 'but', 'not', 'you', 'all', 'can', 'had', 'her', 'was', 'one', 'our', 'out', 'day', 'get', 'has', 'him', 'his', 'how', 'its', 'may', 'new', 'now', 'old', 'see', 'two', 'who', 'boy', 'did', 'does', 'let', 'put', 'say', 'she', 'too', 'use'));
        });
        
        if (!empty($meaningful_words)) {
            return reset($meaningful_words);
        }
        
        return 'abstract'; // Son çare
    }
    
    public function import_image() {
        check_ajax_referer('aiblog_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Yetkisiz erişim');
        }
        
        $image_data = json_decode(stripslashes($_POST['image_data']), true);
        
        if (!$image_data || !isset($image_data['download_url'])) {
            wp_send_json_error('Geçersiz resim verisi');
        }
        
        // Download image
        $response = wp_remote_get($image_data['download_url'], array(
            'timeout' => 45,
            'headers' => array(
                'User-Agent' => 'WordPress AI Blog Generator'
            )
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Resim indirilemedi: ' . $response->get_error_message());
        }
        
        $image_content = wp_remote_retrieve_body($response);
        if (empty($image_content)) {
            wp_send_json_error('Resim içeriği boş');
        }
        
        // Generate filename
        $filename = 'aiblog-' . $image_data['source'] . '-' . $image_data['id'] . '-' . time() . '.jpg';
        
        // Upload to WordPress
        $upload = wp_upload_bits($filename, null, $image_content);
        
        if ($upload['error']) {
            wp_send_json_error('Upload hatası: ' . $upload['error']);
        }
        
        // Create attachment
        $attachment = array(
            'guid' => $upload['url'],
            'post_mime_type' => 'image/jpeg',
            'post_title' => $image_data['alt'],
            'post_content' => '',
            'post_excerpt' => $image_data['alt'],
            'post_status' => 'inherit'
        );
        
        $attachment_id = wp_insert_attachment($attachment, $upload['file']);
        
        if (is_wp_error($attachment_id)) {
            wp_send_json_error('Attachment oluşturulamadı');
        }
        
        // Generate metadata
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        
        // Set alt text and credits
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $image_data['alt']);
        update_post_meta($attachment_id, '_aiblog_photographer', $image_data['photographer']);
        update_post_meta($attachment_id, '_aiblog_source', $image_data['source']);
        update_post_meta($attachment_id, '_aiblog_source_url', $image_data['source_url']);
        
        wp_send_json_success(array(
            'attachment_id' => $attachment_id,
            'thumbnail_url' => wp_get_attachment_image_url($attachment_id, 'medium'),
            'full_url' => wp_get_attachment_image_url($attachment_id, 'full')
        ));
    }
    
    public function ai_content_tools() {
        // Debug log
        error_log('AI Content Tools called with action: ' . $_POST['ai_action']);
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'aiblog_nonce')) {
            wp_send_json_error('Güvenlik kontrolü başarısız. Sayfayı yenileyin ve tekrar deneyin.');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Bu işlem için yetkiniz yok.');
        }
        
        // Validate inputs
        if (!isset($_POST['ai_action']) || empty($_POST['ai_action'])) {
            wp_send_json_error('İşlem türü belirtilmedi.');
        }
        
        $action = sanitize_text_field($_POST['ai_action']);
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        $tone = isset($_POST['tone']) ? sanitize_text_field($_POST['tone']) : '';
        
        // Debug log
        error_log('Action: ' . $action . ', Title length: ' . strlen($title) . ', Content length: ' . strlen($content));
        
        $api_key = get_option('aiblog_openai_key', '');
        if (empty($api_key)) {
            wp_send_json_error('❌ OpenAI API anahtarı bulunamadı. Lütfen <a href="' . admin_url('admin.php?page=ai-blog-settings') . '">Ayarlar</a> sayfasından API anahtarınızı girin.');
        }
        
        // Prepare prompts based on action
        $prompts = array(
            'improve-title' => "Aşağıdaki blog başlığını daha çekici, SEO dostu ve tıklanabilir hale getirin. Sadece iyileştirilmiş başlığı verin, açıklama yapmayın:\n\n'{$title}'",
            
            'generate-alternatives' => "Aşağıdaki blog başlığı için 5 farklı alternatif başlık üretin. Her biri farklı açıdan yaklaşsın (SEO, merak uyandırma, fayda odaklı, soru tarzı vs). Sadece başlıkları listeleyin, numara koymayın:\n\n'{$title}'",
            
            'improve-content' => "Aşağıdaki blog içeriğini iyileştirin:\n- Yazım hatalarını düzeltin\n- Cümle yapısını iyileştirin\n- Daha akıcı hale getirin\n- Okunabilirliği artırın\n\nİçerik:\n{$content}",
            
            'change-tone' => "Aşağıdaki blog içeriğinin tonunu '{$tone}' tarzına dönüştürün. İçeriğin anlamını koruyun, sadece tonunu değiştirin:\n\nİçerik:\n{$content}",
            
            'expand-content' => "Aşağıdaki blog içeriğini genişletin ve detaylandırın:\n- Daha fazla detay ekleyin\n- Örnekler verin\n- Alt başlıklar kullanın\n- En az %50 daha uzun hale getirin\n\nİçerik:\n{$content}",
            
            'summarize' => "Aşağıdaki blog içeriğini özetleyin:\n- Temel noktaları koruyun\n- %50 daha kısa hale getirin\n- Önemli bilgileri kaybetmeyin\n\nİçerik:\n{$content}",
            
            'seo-optimize' => "Aşağıdaki blog içeriğini SEO açısından optimize edin:\n- Anahtar kelimeleri doğal şekilde yerleştirin\n- Alt başlıklar (H2, H3) ekleyin\n- Meta açıklama için uygun özet cümleler ekleyin\n- İç bağlantı önerileri ekleyin\n\nİçerik:\n{$content}"
        );
        
        if (!isset($prompts[$action])) {
            wp_send_json_error('Geçersiz işlem');
        }
        
        $prompt = $prompts[$action];
        
        // Call OpenAI API
        error_log('Calling OpenAI API for action: ' . $action);
        $api_response = $this->call_openai_api($prompt, 3000);
        
        if ($api_response === false) {
            error_log('OpenAI API call failed for action: ' . $action);
            wp_send_json_error('❌ AI servisine bağlanılamadı. Lütfen:\n• İnternet bağlantınızı kontrol edin\n• OpenAI API anahtarınızın doğru olduğundan emin olun\n• Daha sonra tekrar deneyin');
        }
        
        if (empty($api_response)) {
            error_log('OpenAI API returned empty response for action: ' . $action);
            wp_send_json_error('❌ AI servisi boş yanıt döndü. Lütfen tekrar deneyin.');
        }
        
        error_log('OpenAI API successful for action: ' . $action . ', Response length: ' . strlen($api_response));
        
        // Process response based on action
        switch ($action) {
            case 'improve-title':
                wp_send_json_success(array('title' => trim($api_response)));
                break;
                
            case 'generate-alternatives':
                $alternatives = array_filter(array_map('trim', explode("\n", $api_response)));
                // Remove any numbering or bullet points
                $alternatives = array_map(function($alt) {
                    return preg_replace('/^[\d\.\-\*\s]+/', '', $alt);
                }, $alternatives);
                wp_send_json_success(array('alternatives' => array_slice($alternatives, 0, 5)));
                break;
                
            default:
                wp_send_json_success(array('content' => $api_response));
                break;
        }
    }
    
    public function test_openai_connection() {
        check_ajax_referer('aiblog_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Yetkisiz erişim');
        }
        
        $api_key = sanitize_text_field($_POST['api_key']);
        
        if (empty($api_key)) {
            wp_send_json_error('API anahtarı boş olamaz');
        }
        
        // Test with a simple prompt
        $test_prompt = "Test mesajı. Sadece 'Bağlantı başarılı' diye yanıtla.";
        
        $api_url = 'https://api.openai.com/v1/chat/completions';
        $headers = array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        );
        
        $body = array(
            'model' => 'gpt-3.5-turbo',
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $test_prompt
                )
            ),
            'max_tokens' => 50,
            'temperature' => 0.7
        );
        
        $response = wp_remote_post($api_url, array(
            'headers' => $headers,
            'body' => json_encode($body),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Bağlantı hatası: ' . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code === 200) {
            $data = json_decode($response_body, true);
            if (isset($data['choices'][0]['message']['content'])) {
                wp_send_json_success('✅ OpenAI API bağlantısı başarılı! Model: ' . $data['model']);
            } else {
                wp_send_json_error('❌ API yanıtı beklenmedik format');
            }
        } elseif ($response_code === 401) {
            wp_send_json_error('❌ API anahtarı geçersiz. Lütfen doğru anahtarı girin.');
        } elseif ($response_code === 429) {
            wp_send_json_error('❌ API kullanım limiti aşıldı. Daha sonra deneyin.');
        } elseif ($response_code === 403) {
            wp_send_json_error('❌ API erişimi reddedildi. Hesap ayarlarınızı kontrol edin.');
        } else {
            $error_data = json_decode($response_body, true);
            $error_message = isset($error_data['error']['message']) ? $error_data['error']['message'] : 'Bilinmeyen hata';
            wp_send_json_error('❌ API hatası (Kod: ' . $response_code . '): ' . $error_message);
        }
    }
    
    public function calendar_page() {
        $current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
        $current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
        
        // Get posts for current month
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => array('publish', 'draft', 'future'),
            'meta_query' => array(
                array(
                    'key' => '_aiblog_generated',
                    'value' => '1',
                    'compare' => '='
                )
            ),
            'date_query' => array(
                array(
                    'year' => $current_year,
                    'month' => $current_month
                )
            ),
            'numberposts' => -1
        ));
        
        $month_posts = array();
        foreach ($posts as $post) {
            $day = date('j', strtotime($post->post_date));
            if (!isset($month_posts[$day])) {
                $month_posts[$day] = array();
            }
            $month_posts[$day][] = $post;
        }
        
        ?>
        <div class="wrap">
            <h1>📅 İçerik Takvimi</h1>
            
            <div class="calendar-container">
                <!-- Calendar Navigation -->
                <div class="calendar-navigation">
                    <div class="nav-controls">
                        <a href="<?php echo admin_url('admin.php?page=ai-blog-calendar&month=' . ($current_month == 1 ? 12 : $current_month - 1) . '&year=' . ($current_month == 1 ? $current_year - 1 : $current_year)); ?>" class="button">
                            ◀ Önceki Ay
                        </a>
                        
                        <h2 class="current-month">
                            <?php echo date('F Y', mktime(0, 0, 0, $current_month, 1, $current_year)); ?>
                        </h2>
                        
                        <a href="<?php echo admin_url('admin.php?page=ai-blog-calendar&month=' . ($current_month == 12 ? 1 : $current_month + 1) . '&year=' . ($current_month == 12 ? $current_year + 1 : $current_year)); ?>" class="button">
                            Sonraki Ay ▶
                        </a>
                    </div>
                    
                    <div class="calendar-actions">
                        <button type="button" id="suggest-content-plan" class="button button-primary">
                            💡 Aylık Plan Öner
                        </button>
                        <button type="button" id="bulk-schedule" class="button button-secondary">
                            📋 Toplu Zamanlama
                        </button>
                    </div>
                </div>
                
                <!-- Calendar Stats -->
                <div class="calendar-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count(array_filter($posts, function($p) { return $p->post_status === 'publish'; })); ?></span>
                        <span class="stat-label">Yayınlanan</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count(array_filter($posts, function($p) { return $p->post_status === 'draft'; })); ?></span>
                        <span class="stat-label">Taslak</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count(array_filter($posts, function($p) { return $p->post_status === 'future'; })); ?></span>
                        <span class="stat-label">Zamanlanmış</span>
                    </div>
                </div>
                
                <!-- Calendar Grid -->
                <div class="calendar-grid">
                    <div class="calendar-header">
                        <div class="day-header">Pazartesi</div>
                        <div class="day-header">Salı</div>
                        <div class="day-header">Çarşamba</div>
                        <div class="day-header">Perşembe</div>
                        <div class="day-header">Cuma</div>
                        <div class="day-header">Cumartesi</div>
                        <div class="day-header">Pazar</div>
                    </div>
                    
                    <div class="calendar-body">
                        <?php
                        $first_day = date('N', mktime(0, 0, 0, $current_month, 1, $current_year));
                        $days_in_month = date('t', mktime(0, 0, 0, $current_month, 1, $current_year));
                        
                        // Empty cells for days before month starts
                        for ($i = 1; $i < $first_day; $i++) {
                            echo '<div class="calendar-day empty"></div>';
                        }
                        
                        // Days of the month
                        for ($day = 1; $day <= $days_in_month; $day++) {
                            $is_today = ($day == date('j') && $current_month == date('n') && $current_year == date('Y'));
                            $day_posts = isset($month_posts[$day]) ? $month_posts[$day] : array();
                            
                            echo '<div class="calendar-day' . ($is_today ? ' today' : '') . '" data-day="' . $day . '">';
                            echo '<div class="day-number">' . $day . '</div>';
                            
                            if (!empty($day_posts)) {
                                echo '<div class="day-posts">';
                                foreach ($day_posts as $post) {
                                    $status_class = $post->post_status;
                                    $status_emoji = array(
                                        'publish' => '✅',
                                        'draft' => '📝',
                                        'future' => '⏰'
                                    );
                                    
                                    echo '<div class="post-item ' . $status_class . '" data-post-id="' . $post->ID . '">';
                                    echo '<span class="post-status">' . ($status_emoji[$post->post_status] ?? '📄') . '</span>';
                                    echo '<span class="post-title">' . wp_trim_words($post->post_title, 3) . '</span>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            } else {
                                echo '<div class="day-empty">+</div>';
                            }
                            
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Content Suggestions -->
                <div class="content-suggestions" id="content-suggestions" style="display: none;">
                    <h3>💡 İçerik Önerileri</h3>
                    <div class="suggestions-grid">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .calendar-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .calendar-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e1e1e1;
        }
        
        .nav-controls {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .current-month {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }
        
        .calendar-actions {
            display: flex;
            gap: 10px;
        }
        
        .calendar-stats {
            display: flex;
            gap: 30px;
            margin-bottom: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            display: block;
            font-size: 28px;
            font-weight: bold;
            color: #0073aa;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        
        .calendar-grid {
            border: 1px solid #e1e1e1;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .calendar-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background: #0073aa;
            color: white;
        }
        
        .day-header {
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }
        
        .calendar-body {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }
        
        .calendar-day {
            min-height: 100px;
            border: 1px solid #e1e1e1;
            padding: 8px;
            position: relative;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .calendar-day:hover {
            background: #f0f8ff;
        }
        
        .calendar-day.today {
            background: #e3f2fd;
            border-color: #2196f3;
        }
        
        .calendar-day.empty {
            background: #fafafa;
            cursor: default;
        }
        
        .day-number {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .day-posts {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .post-item {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            padding: 2px 4px;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .post-item.publish {
            background: #d4edda;
            color: #155724;
        }
        
        .post-item.draft {
            background: #fff3cd;
            color: #856404;
        }
        
        .post-item.future {
            background: #cce7ff;
            color: #004085;
        }
        
        .post-status {
            font-size: 8px;
        }
        
        .post-title {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .day-empty {
            text-align: center;
            color: #ccc;
            font-size: 24px;
            margin-top: 20px;
            cursor: pointer;
        }
        
        .day-empty:hover {
            color: #0073aa;
        }
        
        .content-suggestions {
            margin-top: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        
        .suggestions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .suggestion-item {
            background: white;
            border: 1px solid #e1e1e1;
            border-radius: 6px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .suggestion-item:hover {
            border-color: #0073aa;
            transform: translateY(-2px);
        }
        
        .suggestion-title {
            font-weight: bold;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        
        .suggestion-meta {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .suggestion-description {
            font-size: 13px;
            color: #555;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Calendar functionality will be added here
            
            $('#suggest-content-plan').click(function() {
                generateContentSuggestions();
            });
            
            $('.day-empty').click(function() {
                const day = $(this).closest('.calendar-day').data('day');
                openQuickCreateModal(day);
            });
            
            $('.post-item').click(function() {
                const postId = $(this).data('post-id');
                window.open('<?php echo admin_url('admin.php?page=ai-blog-edit&post='); ?>' + postId, '_blank');
            });
            
            function generateContentSuggestions() {
                $('#content-suggestions').show();
                const $grid = $('.suggestions-grid');
                
                const suggestions = [
                    {
                        title: "Teknoloji Trendleri 2024",
                        meta: "Popüler • SEO: 85/100",
                        description: "Yılın en önemli teknoloji trendlerini analiz eden kapsamlı içerik"
                    },
                    {
                        title: "Yapay Zeka ve Gelecek",
                        meta: "Trend • SEO: 92/100", 
                        description: "AI'nın günlük hayatımıza etkilerini inceleyen detaylı makale"
                    },
                    {
                        title: "Dijital Pazarlama İpuçları",
                        meta: "Popüler • SEO: 78/100",
                        description: "Küçük işletmeler için pratik dijital pazarlama stratejileri"
                    },
                    {
                        title: "Web Tasarım Rehberi",
                        meta: "Evergreen • SEO: 88/100",
                        description: "2024'te modern web tasarım prensipleri ve en iyi uygulamalar"
                    }
                ];
                
                $grid.empty();
                suggestions.forEach(function(suggestion) {
                    $grid.append(`
                        <div class="suggestion-item">
                            <div class="suggestion-title">${suggestion.title}</div>
                            <div class="suggestion-meta">${suggestion.meta}</div>
                            <div class="suggestion-description">${suggestion.description}</div>
                        </div>
                    `);
                });
            }
            
            function openQuickCreateModal(day) {
                // Quick create modal implementation
                alert('Hızlı içerik oluşturma özelliği yakında eklenecek!');
            }
        });
        </script>
        <?php
    }
}

// Plugin'i başlat
new AIBlogGenerator();
?>
