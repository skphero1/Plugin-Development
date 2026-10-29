<?php
// Run inside the dedicated development container, never on a production site.
define('WP_INSTALLING', true);
$_SERVER['HTTP_HOST'] = 'localhost:8080';
require '/var/www/html/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
if (!is_blog_installed()) {
    wp_install('Atelier Development', 'atelier_admin', wp_generate_password(32), 'atelier@example.test', false, '', wp_generate_password(32));
}
update_option('siteurl', 'http://localhost:8080');
update_option('home', 'http://localhost:8080');
require_once ABSPATH . 'wp-admin/includes/plugin.php';
$result = activate_plugin('atelier-builder/atelier-builder.php');
if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
echo "WordPress development site initialized; Atelier active.\n";
