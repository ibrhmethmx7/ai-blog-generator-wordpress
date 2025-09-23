jQuery(document).ready(function($) {
    'use strict';
    
    // Form elements
    const $form = $('#ai-blog-form');
    const $generateBtn = $('#generate-btn');
    const $loading = $('#loading');
    const $result = $('#blog-result');
    const $publishBtn = $('#publish-btn');
    const $editBtn = $('#edit-btn');
    const $regenerateBtn = $('#regenerate-btn');
    
    // Generated content elements
    const $generatedTitle = $('#generated-title');
    const $generatedContent = $('#generated-content');
    
    // Current blog data
    let currentBlogData = null;
    
    // Form submission
    $form.on('submit', function(e) {
        e.preventDefault();
        generateBlog();
    });
    
    // Regenerate button
    $regenerateBtn.on('click', function() {
        generateBlog();
    });
    
    // Publish button
    $publishBtn.on('click', function() {
        publishBlog();
    });
    
    // Edit button
    $editBtn.on('click', function() {
        openWordPressEditor();
    });
    
    /**
     * Generate AI blog post
     */
    function generateBlog() {
        const formData = {
            action: 'generate_ai_blog',
            nonce: aiblog_ajax.nonce,
            topic: $('#blog_topic').val(),
            style: $('#blog_style').val(),
            length: $('#blog_length').val(),
            audience: $('#target_audience').val(),
            keywords: $('#keywords').val(),
            auto_publish: $('#auto_publish').is(':checked')
        };
        
        // Validation
        if (!formData.topic.trim()) {
            showMessage('Lütfen bir konu girin.', 'error');
            return;
        }
        
        // UI updates
        showLoading();
        $generateBtn.prop('disabled', true);
        
        // AJAX request
        $.ajax({
            url: aiblog_ajax.ajax_url,
            type: 'POST',
            data: formData,
            timeout: 120000, // 2 minutes
            success: function(response) {
                hideLoading();
                $generateBtn.prop('disabled', false);
                
                if (response.success) {
                    currentBlogData = response;
                    displayBlogResult(response);
                    
                    if (response.post_id) {
                        showMessage(
                            `Blog yazısı başarıyla oluşturuldu ve yayınlandı! <a href="${response.post_url}" target="_blank">Görüntüle</a>`,
                            'success'
                        );
                    } else {
                        showMessage('Blog yazısı başarıyla oluşturuldu!', 'success');
                    }
                } else {
                    showMessage(response.message || 'Bir hata oluştu.', 'error');
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                $generateBtn.prop('disabled', false);
                
                let errorMessage = 'Bağlantı hatası oluştu.';
                if (status === 'timeout') {
                    errorMessage = 'İşlem zaman aşımına uğradı. Lütfen tekrar deneyin.';
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        // JSON parse error, use default message
                    }
                }
                
                showMessage(errorMessage, 'error');
            }
        });
    }
    
    /**
     * Publish blog to WordPress
     */
    function publishBlog() {
        if (!currentBlogData) {
            showMessage('Yayınlanacak blog yazısı bulunamadı.', 'error');
            return;
        }
        
        const publishData = {
            action: 'publish_ai_blog',
            nonce: aiblog_ajax.nonce,
            title: currentBlogData.title,
            content: currentBlogData.content,
            excerpt: currentBlogData.excerpt
        };
        
        $publishBtn.prop('disabled', true).text('Yayınlanıyor...');
        
        $.ajax({
            url: aiblog_ajax.ajax_url,
            type: 'POST',
            data: publishData,
            success: function(response) {
                $publishBtn.prop('disabled', false).text('Yayınla');
                
                if (response.success) {
                    showMessage(
                        `Blog yazısı başarıyla yayınlandı! <a href="${response.post_url}" target="_blank">Görüntüle</a>`,
                        'success'
                    );
                    $publishBtn.hide();
                } else {
                    showMessage(response.message || 'Yayınlama sırasında hata oluştu.', 'error');
                }
            },
            error: function() {
                $publishBtn.prop('disabled', false).text('Yayınla');
                showMessage('Yayınlama sırasında bağlantı hatası oluştu.', 'error');
            }
        });
    }
    
    /**
     * Open WordPress editor with generated content
     */
    function openWordPressEditor() {
        if (!currentBlogData) {
            showMessage('Düzenlenecek blog yazısı bulunamadı.', 'error');
            return;
        }
        
        // Create a form to submit to post-new.php
        const $form = $('<form>', {
            method: 'POST',
            action: 'post-new.php'
        });
        
        $form.append($('<input>', {
            type: 'hidden',
            name: 'post_title',
            value: currentBlogData.title
        }));
        
        $form.append($('<input>', {
            type: 'hidden',
            name: 'content',
            value: currentBlogData.content
        }));
        
        $form.append($('<input>', {
            type: 'hidden',
            name: 'excerpt',
            value: currentBlogData.excerpt
        }));
        
        $('body').append($form);
        $form.submit();
    }
    
    /**
     * Display blog result
     */
    function displayBlogResult(data) {
        $generatedTitle.text(data.title);
        $generatedContent.html(formatContent(data.content));
        $result.show();
        
        // Initialize image fallback system
        initializeImageFallbacks();
        
        // Display statistics if available
        if (data.word_count || data.keywords_used) {
            displayContentStats(data);
        }
        
        // Scroll to result
        $('html, body').animate({
            scrollTop: $result.offset().top - 50
        }, 500);
        
        // Show publish button if not auto-published
        if (!data.post_id) {
            $publishBtn.show();
        } else {
            $publishBtn.hide();
        }
    }
    
    /**
     * Format content for display
     */
    function formatContent(content) {
        // Simple formatting - convert line breaks to paragraphs
        return content
            .split('\n\n')
            .map(paragraph => paragraph.trim())
            .filter(paragraph => paragraph.length > 0)
            .map(paragraph => `<p>${paragraph}</p>`)
            .join('');
    }
    
    /**
     * Show loading state
     */
    function showLoading() {
        $loading.show();
        $result.hide();
        hideMessages();
    }
    
    /**
     * Hide loading state
     */
    function hideLoading() {
        $loading.hide();
    }
    
    /**
     * Show message
     */
    function showMessage(message, type = 'success') {
        hideMessages();
        
        const $message = $('<div>', {
            class: `aiblog-message ${type}`,
            html: message
        });
        
        $form.after($message);
        
        // Auto-hide after 5 seconds for success messages
        if (type === 'success') {
            setTimeout(() => {
                $message.fadeOut(() => $message.remove());
            }, 5000);
        }
    }
    
    /**
     * Hide all messages
     */
    function hideMessages() {
        $('.aiblog-message').remove();
    }
    
    /**
     * Auto-save form data to localStorage
     */
    function saveFormData() {
        const formData = {
            topic: $('#blog_topic').val(),
            style: $('#blog_style').val(),
            length: $('#blog_length').val(),
            audience: $('#target_audience').val(),
            keywords: $('#keywords').val(),
            auto_publish: $('#auto_publish').is(':checked')
        };
        
        localStorage.setItem('aiblog_form_data', JSON.stringify(formData));
    }
    
    /**
     * Restore form data from localStorage
     */
    function restoreFormData() {
        try {
            const savedData = localStorage.getItem('aiblog_form_data');
            if (savedData) {
                const formData = JSON.parse(savedData);
                
                $('#blog_topic').val(formData.topic || '');
                $('#blog_style').val(formData.style || 'formal');
                $('#blog_length').val(formData.length || 'medium');
                $('#target_audience').val(formData.audience || '');
                $('#keywords').val(formData.keywords || '');
                $('#auto_publish').prop('checked', formData.auto_publish || false);
            }
        } catch (e) {
            // Ignore errors in restoring form data
        }
    }
    
    /**
     * Form change handler for auto-save
     */
    $form.on('change input', 'input, select', function() {
        saveFormData();
    });
    
    // Initialize
    restoreFormData();
    
    // Add AJAX handler for publish action
    $(document).on('click', '#publish-btn', function() {
        publishBlog();
    });
    
    // Character counter for topic field
    $('#blog_topic').on('input', function() {
        const length = $(this).val().length;
        const maxLength = 200;
        
        if (length > maxLength) {
            $(this).val($(this).val().substring(0, maxLength));
        }
        
        // Add visual feedback
        if (length > maxLength * 0.9) {
            $(this).css('border-color', '#d63638');
        } else {
            $(this).css('border-color', '');
        }
    });
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + Enter to generate
        if ((e.ctrlKey || e.metaKey) && e.which === 13) {
            e.preventDefault();
            if (!$generateBtn.prop('disabled')) {
                generateBlog();
            }
        }
    });
    
    // Form validation enhancement
    $form.on('submit', function(e) {
        const topic = $('#blog_topic').val().trim();
        
        if (topic.length < 3) {
            e.preventDefault();
            showMessage('Konu en az 3 karakter olmalıdır.', 'error');
            $('#blog_topic').focus();
            return false;
        }
        
        if (topic.length > 200) {
            e.preventDefault();
            showMessage('Konu 200 karakteri geçmemelidir.', 'error');
            $('#blog_topic').focus();
            return false;
        }
    });
});

// Add publish action to WordPress AJAX
jQuery(document).ready(function($) {
    $(document).on('click', '.publish-generated-blog', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const blogData = $button.data('blog');
        
        $button.prop('disabled', true).text('Yayınlanıyor...');
        
        $.ajax({
            url: aiblog_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'publish_ai_blog',
                nonce: aiblog_ajax.nonce,
                blog_data: JSON.stringify(blogData)
            },
            success: function(response) {
                if (response.success) {
                    $button.text('Yayınlandı').removeClass('button-primary').addClass('button-secondary');
                    alert('Blog yazısı başarıyla yayınlandı!');
                } else {
                    $button.prop('disabled', false).text('Yayınla');
                    alert('Hata: ' + (response.message || 'Bilinmeyen hata'));
                }
            },
            error: function() {
                $button.prop('disabled', false).text('Yayınla');
                alert('Bağlantı hatası oluştu.');
            }
        });
    });
    
    /**
     * Initialize image fallback system
     */
    function initializeImageFallbacks() {
        $('.aiblog-auto-image').each(function() {
            const $img = $(this);
            const fallbackUrls = $img.data('fallback-urls');
            
            if (fallbackUrls && Array.isArray(fallbackUrls)) {
                $img.on('error', function() {
                    tryFallbackImage($img, fallbackUrls, 0);
                });
            }
            
            // Test if image loads
            const testImg = new Image();
            testImg.onload = function() {
                // Image loaded successfully
                $img.show();
                $img.siblings('.aiblog-image-placeholder').hide();
            };
            testImg.onerror = function() {
                // Image failed to load, try fallbacks
                if (fallbackUrls && Array.isArray(fallbackUrls)) {
                    tryFallbackImage($img, fallbackUrls, 0);
                } else {
                    showImagePlaceholder($img);
                }
            };
            testImg.src = $img.attr('src');
        });
    }
    
    /**
     * Try fallback images
     */
    function tryFallbackImage($img, fallbackUrls, index) {
        if (index >= fallbackUrls.length) {
            showImagePlaceholder($img);
            return;
        }
        
        const testImg = new Image();
        testImg.onload = function() {
            $img.attr('src', fallbackUrls[index]);
            $img.show();
            $img.siblings('.aiblog-image-placeholder').hide();
        };
        testImg.onerror = function() {
            // Try next fallback
            setTimeout(() => {
                tryFallbackImage($img, fallbackUrls, index + 1);
            }, 1000);
        };
        testImg.src = fallbackUrls[index];
    }
    
    /**
     * Show image placeholder
     */
    function showImagePlaceholder($img) {
        $img.hide();
        $img.siblings('.aiblog-image-placeholder').show();
    }
    
    /**
     * Display content statistics
     */
    function displayContentStats(data) {
        let statsHtml = '<div class="aiblog-stats">';
        
        if (data.word_count) {
            statsHtml += '<div class="stat-item">';
            statsHtml += '<span class="stat-number">' + data.word_count + '</span>';
            statsHtml += '<span class="stat-label">Words</span>';
            statsHtml += '</div>';
        }
        
        if (data.keywords_used && data.keywords_used.length > 0) {
            statsHtml += '<div class="stat-item">';
            statsHtml += '<span class="stat-number">' + data.keywords_used.length + '</span>';
            statsHtml += '<span class="stat-label">Keywords</span>';
            statsHtml += '</div>';
        }
        
        if (data.images && data.images.length > 0) {
            statsHtml += '<div class="stat-item">';
            statsHtml += '<span class="stat-number">' + data.images.length + '</span>';
            statsHtml += '<span class="stat-label">Images</span>';
            statsHtml += '</div>';
        }
        
        statsHtml += '</div>';
        
        // Insert stats before the blog preview
        $('.blog-preview').before(statsHtml);
    }
    
    /**
     * Enhanced image loading with retry mechanism
     */
    function enhancedImageLoading() {
        $(document).on('error', '.aiblog-auto-image', function() {
            const $img = $(this);
            const retryCount = $img.data('retry-count') || 0;
            
            if (retryCount < 3) {
                // Retry loading the same image
                setTimeout(() => {
                    $img.data('retry-count', retryCount + 1);
                    const originalSrc = $img.attr('src');
                    $img.attr('src', '').attr('src', originalSrc + '?retry=' + retryCount);
                }, 2000);
            } else {
                // Give up and show placeholder
                showImagePlaceholder($img);
            }
        });
    }
    
    // Initialize enhanced image loading
    enhancedImageLoading();
});
