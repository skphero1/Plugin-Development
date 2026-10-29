<?php
/**
 * Plugin Name: Atelier Page Builder
 * Description: An original visual page builder with a focused canvas and reusable content blocks.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * Text Domain: atelier-builder
 */
if (!defined('ABSPATH')) { exit; }

function atelier_clean_layout($value) {
    if (!is_array($value)) { return array(); }
    $result = array();
    foreach (array_slice($value, 0, 200) as $block) {
        if (!is_array($block) || !isset($block['type']) || !in_array($block['type'], array('heading', 'text', 'button', 'image', 'spacer'), true)) { continue; }
        $result[] = array(
            'type' => $block['type'],
            'content' => sanitize_textarea_field($block['content'] ?? ''),
            'url' => esc_url_raw($block['url'] ?? '', array('http', 'https')),
            'align' => in_array($block['align'] ?? '', array('left', 'center', 'right'), true) ? $block['align'] : 'left',
            'color' => sanitize_hex_color($block['color'] ?? '') ?: '#222222',
            'background' => sanitize_hex_color($block['background'] ?? '') ?: '#ffffff',
            'size' => max(12, min(96, intval($block['size'] ?? 24))),
            'padding' => max(0, min(120, intval($block['padding'] ?? 24))),
        );
    }
    return $result;
}
add_action('init', function () {
    register_post_meta('page', '_atelier_layout', array(
        'type' => 'array', 'single' => true, 'default' => array(),
        'sanitize_callback' => 'atelier_clean_layout',
        'auth_callback' => function ($allowed, $key, $post_id) { return current_user_can('edit_post', $post_id); },
        'show_in_rest' => false,
    ));
});
add_action('add_meta_boxes_page', function () {
    add_meta_box('atelier-editor', 'Atelier Page Builder', function ($post) {
        wp_nonce_field('atelier_save', 'atelier_nonce');
        $layout = atelier_clean_layout(get_post_meta($post->ID, '_atelier_layout', true));
        echo '<p>Build a custom layout below. An empty canvas uses your normal page content.</p>';
        echo '<input type="hidden" id="atelier-layout" name="atelier_layout" value="' . esc_attr(wp_json_encode($layout)) . '">';
        echo '<div id="atelier-app"></div>';
    }, 'page', 'normal', 'high');
});
add_action('admin_enqueue_scripts', function ($hook) {
    if (!in_array($hook, array('post.php', 'post-new.php'), true) || get_current_screen()->post_type !== 'page') { return; }
    wp_enqueue_style('atelier-editor', plugins_url('assets/editor.css', __FILE__), array(), '0.1.0');
    wp_enqueue_script('atelier-editor', plugins_url('assets/editor.js', __FILE__), array(), '0.1.0', true);
});
add_action('save_post_page', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || wp_is_post_revision($post_id)) { return; }
    if (!isset($_POST['atelier_nonce'], $_POST['atelier_layout']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['atelier_nonce'])), 'atelier_save') || !current_user_can('edit_post', $post_id)) { return; }
    $layout = json_decode(wp_unslash($_POST['atelier_layout']), true);
    if (is_array($layout)) { update_post_meta($post_id, '_atelier_layout', atelier_clean_layout($layout)); }
});
add_filter('the_content', function ($content) {
    if (!is_singular('page') || !is_main_query() || get_the_ID() !== get_queried_object_id()) { return $content; }
    $layout = atelier_clean_layout(get_post_meta(get_the_ID(), '_atelier_layout', true));
    if (!$layout) { return $content; }
    $html = '<div class="atelier-page">';
    foreach ($layout as $block) {
        $style = sprintf('text-align:%s;color:%s;background:%s;font-size:%dpx;padding:%dpx;', $block['align'], $block['color'], $block['background'], $block['size'], $block['padding']);
        $html .= '<div class="atelier-block" style="' . esc_attr($style) . '">';
        switch ($block['type']) {
            case 'heading': $html .= '<h2 style="font-size:inherit;color:inherit;margin:0">' . esc_html($block['content']) . '</h2>'; break;
            case 'text': $html .= '<p style="font-size:inherit;color:inherit;margin:0;white-space:pre-wrap">' . esc_html($block['content']) . '</p>'; break;
            case 'button': $html .= '<a style="display:inline-block;background:currentColor;padding:12px 22px;border-radius:8px" href="' . esc_url($block['url']) . '"><span style="color:#fff">' . esc_html($block['content']) . '</span></a>'; break;
            case 'image': if ($block['url']) { $html .= '<img style="max-width:100%;height:auto" src="' . esc_url($block['url']) . '" alt="' . esc_attr($block['content']) . '">'; } break;
            case 'spacer': $html .= '<div style="height:' . intval($block['size']) . 'px" aria-hidden="true"></div>'; break;
        }
        $html .= '</div>';
    }
    return $html . '</div>';
});
