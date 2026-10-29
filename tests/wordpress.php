<?php
$_SERVER['HTTP_HOST'] = 'localhost:8080';
require '/var/www/html/wp-load.php';
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
$admin = get_user_by('login', 'atelier_admin');
check((bool) $admin, 'Development administrator missing');
wp_set_current_user($admin->ID);
$test_page_id = wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Atelier smoke test', 'post_content' => 'Fallback content'));
check(!is_wp_error($test_page_id) && $test_page_id > 0, 'Page creation failed');
$layout = array(
 array('type' => 'heading', 'content' => 'Atelier works', 'size' => 48),
 array('type' => 'text', 'content' => '<script>alert(1)</script>Our own canvas.'),
 array('type' => 'button', 'content' => 'Explore', 'url' => 'https://example.com'),
 array('type' => 'image', 'content' => 'Image description', 'url' => 'https://example.com/image.jpg'),
 array('type' => 'spacer', 'size' => 32),
);
$_POST['atelier_nonce'] = wp_create_nonce('atelier_save');
$_POST['atelier_layout'] = wp_slash(wp_json_encode($layout));
wp_update_post(array('ID' => $test_page_id, 'post_title' => 'Atelier smoke test'));
$saved = get_post_meta($test_page_id, '_atelier_layout', true);
check(count($saved) === 5, 'Layout save failed');
check(strpos($saved[1]['content'], '<script>') === false, 'Content sanitization failed');
$_POST['atelier_nonce'] = 'invalid';
$_POST['atelier_layout'] = '[]';
wp_update_post(array('ID' => $test_page_id));
check(count(get_post_meta($test_page_id, '_atelier_layout', true)) === 5, 'Invalid nonce changed layout');
$subscriber = wp_insert_user(array('user_login' => 'atelier_test_' . wp_generate_password(8, false), 'user_pass' => wp_generate_password(32), 'role' => 'subscriber'));
check(!is_wp_error($subscriber), 'Subscriber setup failed');
wp_set_current_user($subscriber);
$_POST['atelier_nonce'] = wp_create_nonce('atelier_save');
do_action('save_post_page', $test_page_id);
check(count(get_post_meta($test_page_id, '_atelier_layout', true)) === 5, 'Subscriber changed layout');
wp_set_current_user($admin->ID);
require_once ABSPATH . 'wp-admin/includes/user.php';
wp_delete_user($subscriber);
unset($_POST['atelier_nonce'], $_POST['atelier_layout']);
$GLOBALS['wp_query'] = new WP_Query(array('page_id' => $test_page_id));
$GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
while (have_posts()) {
 the_post();
 $html = apply_filters('the_content', get_the_content());
 check(strpos($html, 'Atelier works') !== false && strpos($html, 'atelier-page') !== false, 'Layout render failed');
 check(strpos($html, '<script>') === false, 'Unsafe output');
 check(strpos($html, 'https://example.com') !== false && strpos($html, 'Image description') !== false, 'Link or image missing');
 delete_post_meta($test_page_id, '_atelier_layout');
 check(strpos(apply_filters('the_content', get_the_content()), 'Fallback content') !== false, 'Empty layout fallback failed');
 update_post_meta($test_page_id, '_atelier_layout', $saved);
}
check(atelier_clean_layout(array(array('type' => 'unknown'))) === array(), 'Unknown widget accepted');
$unsafe = atelier_clean_layout(array(array('type'=>'button', 'url'=>'javascript:alert(1)', 'size'=>9999, 'padding'=>-1, 'color'=>'invalid', 'align'=>'unsafe')))[0];
check($unsafe['url'] === '' && $unsafe['size'] === 96 && $unsafe['padding'] === 0 && $unsafe['color'] === '#222222' && $unsafe['align'] === 'left', 'Validation bounds failed');
echo "PASS: saving, nonce rejection, subscriber rejection, sanitization, rendering, fallback, and bounds.\n";
echo 'SMOKE_PAGE_ID=' . $test_page_id . "\n";
