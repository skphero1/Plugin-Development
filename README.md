# Atelier Page Builder

An original WordPress page builder prototype, inspired by the visual editing workflow of Elementor. No Elementor code or assets are used.

## Download

Download the **installable plugin ZIP** from [`dist/atelier-builder.zip`](dist/atelier-builder.zip?raw=true). The first version is also preserved as [`dist/atelier-builder-0.1.0.zip`](dist/atelier-builder-0.1.0.zip?raw=true), and release history is in [CHANGELOG.md](CHANGELOG.md).

In GitHub, open the ZIP and click **Download raw file** if it does not download automatically. Upload this plugin ZIP directly to WordPress; GitHub's repository source ZIP includes development files and is not the prepared plugin package.

## Install and use

Requires WordPress 6.4+ and PHP 7.4+. Copy this directory into `wp-content/plugins/atelier-builder`, then activate **Atelier Page Builder**. Edit a WordPress **Page** and open the Atelier Page Builder panel below the editor.

Add headings, text, buttons, images (using HTTPS URLs), and spacers. Select blocks to edit content, colors, alignment, typography, and padding. Drag to reorder or use Move up / Move down; duplicate and remove controls are also available. Desktop and Mobile controls constrain the editing canvas for inspection. Click WordPress **Update** or **Publish** to save the layout. An empty layout falls back to the normal page content.

## Scope

This is an initial working implementation, not an Elementor replacement. It uses a linear block layout and the active theme's page template. It does not yet include nested columns, breakpoint-specific styling, template libraries, media-library integration, revision restoration for layouts, or inline text editing. Mobile preview changes canvas width only. Layout edits are saved by explicit WordPress page saves, not autosaves.

## Development

The plugin uses plain PHP, JavaScript, and CSS with no build dependencies. Run `npm ci && npm test` for editor interaction tests and `php -l atelier-builder.php` for PHP syntax. Functional validation requires a WordPress installation: activate the plugin, create a page, add and reorder blocks, save, reload the editor, and verify the published page. Check that removing all blocks restores the normal page content. Test as an editor and subscriber to verify WordPress permission boundaries.

License: GPL-2.0-or-later.

## Local WordPress development

Docker with Compose is required. From this checkout:

```sh
docker compose -p atelier -f dev/compose.yaml up -d db wordpress
# Wait for the database and WordPress files to initialize, then:
docker compose -p atelier -f dev/compose.yaml exec -T wordpress php /var/www/html/wp-content/plugins/atelier-builder/dev/bootstrap.php
docker compose -p atelier -f dev/compose.yaml exec -T wordpress php /var/www/html/wp-content/plugins/atelier-builder/tests/wordpress.php
```

The development server binds only to loopback port 8080. The database has a passwordless root account on its isolated Docker network: this configuration is exclusively for local development. Do not deploy it. Bootstrap creates an administrator with a random password; use WordPress's password reset or a local administration tool before signing into the editor. The integration test creates published smoke-test pages. Stop services with `docker compose -p atelier -f dev/compose.yaml stop`; volumes retain site data. Never run bootstrap against an existing production site.

For repeatable cloud setup, run `bash dev/setup.sh`. To restart an installed development environment, run `bash dev/start.sh`. In the managed cloud, use the local Docker socket as these scripts do; sandbox escalation may be required.
