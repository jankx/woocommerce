<?php
/**
 * WordPress Functions for Testing
 * 
 * Define tất cả WordPress functions trong global namespace
 */

// ============================================
// Options API
// ============================================

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return \Jankx\WooCommerce\Tests\Helpers\WordPressMocks::getOption($option, $default);
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {
        return \Jankx\WooCommerce\Tests\Helpers\WordPressMocks::updateOption($option, $value);
    }
}

if (!function_exists('delete_option')) {
    function delete_option($option) {
        return \Jankx\WooCommerce\Tests\Helpers\WordPressMocks::deleteOption($option);
    }
}

// ============================================
// Transients API
// ============================================

if (!function_exists('get_transient')) {
    function get_transient($transient) {
        return \Jankx\WooCommerce\Tests\Helpers\WordPressMocks::getTransient($transient);
    }
}

if (!function_exists('set_transient')) {
    function set_transient($transient, $value, $expiration = 0) {
        return \Jankx\WooCommerce\Tests\Helpers\WordPressMocks::setTransient($transient, $value, $expiration);
    }
}

if (!function_exists('delete_transient')) {
    function delete_transient($transient) {
        return \Jankx\WooCommerce\Tests\Helpers\WordPressMocks::deleteTransient($transient);
    }
}

// ============================================
// Hooks API
// ============================================

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $args = 1) {
        return \Jankx\WooCommerce\Tests\Helpers\WordPressMocks::addAction($hook, $callback, $priority, $args);
    }
}

if (!function_exists('do_action')) {
    function do_action($hook, ...$args) {
        return \Jankx\WooCommerce\Tests\Helpers\WordPressMocks::doAction($hook, ...$args);
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $args = 1) {
        return \Jankx\WooCommerce\Tests\Helpers\WordPressMocks::addFilter($hook, $callback, $priority, $args);
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($hook, $value, ...$args) {
        return \Jankx\WooCommerce\Tests\Helpers\WordPressMocks::applyFilters($hook, $value, ...$args);
    }
}

if (!function_exists('did_action')) {
    function did_action($hook_name) {
        return \Jankx\WooCommerce\Tests\Helpers\WordPressMocks::didAction($hook_name);
    }
}

// ============================================
// Escaping
// ============================================

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}

// ============================================
// Translation
// ============================================

if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('_e')) {
    function _e($text, $domain = 'default') {
        echo $text;
    }
}

if (!function_exists('_n')) {
    function _n($single, $plural, $number, $domain = 'default') {
        return $number === 1 ? $single : $plural;
    }
}

if (!function_exists('esc_html_e')) {
    function esc_html_e($text, $domain = 'default') {
        echo htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr_e')) {
    function esc_attr_e($text, $domain = 'default') {
        echo htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default') {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr__')) {
    function esc_attr__($text, $domain = 'default') {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

// ============================================
// Filesystem
// ============================================

if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($target) {
        if (file_exists($target)) {
            return @is_dir($target);
        }
        return @mkdir($target, 0755, true);
    }
}

// ============================================
// JSON
// ============================================

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0, $depth = 512) {
        return json_encode($data, $options, $depth);
    }
}

// ============================================
// Sanitization
// ============================================

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return strip_tags($str);
    }
}

// ============================================
// Post Meta
// ============================================

if (!function_exists('get_post_meta')) {
    function get_post_meta($post_id, $key = '', $single = false) {
        return \Jankx\WooCommerce\Tests\Helpers\WordPressMocks::getPostMeta($post_id, $key, $single);
    }
}

if (!function_exists('update_post_meta')) {
    function update_post_meta($post_id, $meta_key, $meta_value) {
        return \Jankx\WooCommerce\Tests\Helpers\WordPressMocks::updatePostMeta($post_id, $meta_key, $meta_value);
    }
}

// ============================================
// User
// ============================================

if (!function_exists('get_current_user_id')) {
    function get_current_user_id() {
        return 1;
    }
}

if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() {
        return true;
    }
}

// ============================================
// Template
// ============================================

if (!function_exists('get_template_directory')) {
    function get_template_directory() {
        return sys_get_temp_dir() . '/theme';
    }
}

if (!function_exists('get_stylesheet_directory')) {
    function get_stylesheet_directory() {
        return sys_get_temp_dir() . '/theme';
    }
}

if (!function_exists('is_child_theme')) {
    function is_child_theme() {
        return false;
    }
}

// ============================================
// Misc
// ============================================

if (!function_exists('wp_kses_post')) {
    function wp_kses_post($data) {
        return strip_tags($data);
    }
}

if (!function_exists('size_format')) {
    function size_format($bytes, $decimals = 0) {
        return number_format($bytes / 1024, $decimals) . ' KB';
    }
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action) {
        return md5($action);
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action) {
        return $nonce === md5($action);
    }
}

if (!function_exists('check_ajax_referer')) {
    function check_ajax_referer($action, $query_arg = false, $die = true) {
        return true;
    }
}

if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data = null) {
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '', $scheme = 'admin') {
        return 'http://example.com/wp-admin/' . $path;
    }
}

if (!function_exists('selected')) {
    function selected($selected, $current = true, $echo = true) {
        $result = ((string) $selected === (string) $current) ? 'selected="selected"' : '';
        if ($echo) {
            echo $result;
        }
        return $result;
    }
}

if (!function_exists('checked')) {
    function checked($checked, $current = true, $echo = true) {
        $result = ((string) $checked === (string) $current) ? 'checked="checked"' : '';
        if ($echo) {
            echo $result;
        }
        return $result;
    }
}

if (!function_exists('current_time')) {
    function current_time($type, $gmt = 0) {
        return date($type);
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return true;
    }
}

// ============================================
// Terms
// ============================================

// Define WordPress constants
if (!defined('OBJECT')) {
    define('OBJECT', 'OBJECT');
}

if (!defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}

if (!defined('ARRAY_N')) {
    define('ARRAY_N', 'ARRAY_N');
}

if (!function_exists('get_term')) {
    function get_term($term, $taxonomy = '', $output = OBJECT, $filter = 'raw') {
        return \Jankx\WooCommerce\Tests\Helpers\WordPressMocks::getTerm($term, $taxonomy);
    }
}

if (!function_exists('get_terms')) {
    function get_terms($args = []) {
        return \Jankx\WooCommerce\Tests\Helpers\WordPressMocks::getTerms($args);
    }
}

if (!function_exists('get_term_children')) {
    function get_term_children($term_id, $taxonomy) {
        return \Jankx\WooCommerce\Tests\Helpers\WordPressMocks::getTermChildren($term_id, $taxonomy);
    }
}

if (!function_exists('get_term_link')) {
    function get_term_link($term, $taxonomy = '') {
        if (is_object($term)) {
            return 'http://example.com/category/' . $term->slug;
        }
        return 'http://example.com/category/' . $term;
    }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) {
        return $thing instanceof \WP_Error;
    }
}

if (!function_exists('get_term_meta')) {
    function get_term_meta($term_id, $key = '', $single = false) {
        return '';
    }
}

// ============================================
// WordPress Media
// ============================================

if (!function_exists('wp_get_attachment_image')) {
    function wp_get_attachment_image($attachment_id, $size = 'thumbnail', $icon = false, $attr = '') {
        return '<img src="https://via.placeholder.com/150" alt="placeholder" />';
    }
}

// ============================================
// WooCommerce Functions
// ============================================

if (!function_exists('wc_placeholder_img_src')) {
    function wc_placeholder_img_src($size = 'woocommerce_thumbnail') {
        return 'https://via.placeholder.com/300';
    }
}

if (!function_exists('wc_get_products')) {
    function wc_get_products($args = []) {
        return []; // Return empty array for tests
    }
}

