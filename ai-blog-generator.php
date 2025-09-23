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
            'Ayarlar',
            'Ayarlar',
            'manage_options',
            'ai-blog-settings',
            array($this, 'settings_page')
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'ai-blog') !== false) {
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
                                    <label for="auto_publish">Otomatik Yayınla</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="auto_publish" name="auto_publish" />
                                        Oluşturulduktan sonra otomatik olarak yayınla
                                    </label>
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
                                <p class="description">
                                    Get your OpenAI API key from <a href="https://platform.openai.com/api-keys" target="_blank">here</a>.
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
                                    Get your Unsplash access key from <a href="https://unsplash.com/developers" target="_blank">Unsplash Developers</a>. 
                                    Primary source for high-quality images.
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
                                    Get your Pexels API key from <a href="https://www.pexels.com/api/" target="_blank">Pexels API</a>. 
                                    Used as fallback when Unsplash doesn't have suitable images.
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
            });
            </script>
        </div>
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
        $auto_publish = isset($_POST['auto_publish']) ? true : false;
        
        $blog_content = $this->call_openai_api($topic, $style, $length, $audience, $keywords);
        
        if ($blog_content) {
            $response = array(
                'success' => true,
                'title' => $blog_content['title'],
                'content' => $blog_content['content'],
                'excerpt' => $blog_content['excerpt']
            );
            
            if ($auto_publish) {
                $post_id = $this->create_wordpress_post($blog_content);
                $response['post_id'] = $post_id;
                $response['post_url'] = get_permalink($post_id);
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
        $blog_data = json_decode($content, true);
        
        if (!$blog_data) {
            // JSON parse hatası varsa, basit metin olarak döndür
            return array(
                'title' => 'About ' . $topic,
                'content' => $content,
                'excerpt' => substr(strip_tags($content), 0, 150) . '...',
                'keywords_used' => array(),
                'image_suggestions' => array(),
                'word_count' => str_word_count(strip_tags($content))
            );
        }
        
        // Resimleri fetch et ve içeriğe ekle
        if (isset($blog_data['image_suggestions']) && !empty($blog_data['image_suggestions'])) {
            $images = $this->fetch_unsplash_images($blog_data['image_suggestions'], $keywords);
            if (!empty($images)) {
                $blog_data['content'] = $this->insert_images_into_content($blog_data['content'], $images);
                $blog_data['images'] = $images;
            }
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
        
        $post_data = array(
            'post_title' => $blog_content['title'],
            'post_content' => $blog_content['content'],
            'post_excerpt' => $blog_content['excerpt'],
            'post_status' => 'publish',
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
}

// Plugin'i başlat
new AIBlogGenerator();
?>
