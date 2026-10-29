# Atelier Page Builder
I built Atelier as a standard WordPress plugin using plain PHP, JavaScript, and CSS. There was no separate PHP framework such as Laravel or Symfony—WordPress itself provides the APIs, database access, permissions, and editor integration.

Here’s how I developed it.
1. Defined a small first version
Elementor is a large product, so I started with its basic workflow:
- Add content blocks.
- Select a block and change its settings.
- Reorder blocks.
- Save the layout.
- Display that layout on the published page.
Atelier uses original code and styling. I didn’t copy Elementor’s source code.
2. Created the plugin structure
The main files are:
atelier-builder/
├── atelier-builder.php    WordPress integration and rendering
├── assets/
│   ├── editor.js          Visual editor interactions
│   └── editor.css         Editor appearance
├── tests/                 Editor and WordPress tests
└── dev/                   Local development and packaging tools
The comment header in atelier-builder.php tells WordPress the plugin’s name, version, requirements, and license. That is what makes it appear in your Plugins dashboard.
3. Connected PHP to WordPress
I used WordPress hooks—functions that run at specific points in WordPress’s lifecycle.
Hook or API	What Atelier uses it for
register_post_meta()	Registers the page’s layout data
add_meta_box()	Adds the builder panel to the page editor
admin_enqueue_scripts	Loads the editor’s JavaScript and CSS
save_post_page	Saves the layout when you save a page
the_content	Displays the saved layout on the published page


The layout is stored in WordPress’s existing post metadata, under _atelier_layout. I didn’t create a custom database table.
4. Built the editor with JavaScript
The editor uses vanilla JavaScript, meaning JavaScript without React, Vue, or another frontend framework.

Each block is a JavaScript object containing settings such as:
{
  "type": "heading",
  "content": "Welcome to my website",
  "align": "center",
  "color": "#222222",
  "size": 48,
  "padding": 24
}
All the blocks form an array. When you add, edit, duplicate, delete, or reorder a block, JavaScript updates that array and redraws the canvas.

It also writes the layout into a hidden form field as JSON. When you click WordPress’s Update or Publish, that field is submitted with the page.
5. Added saving and security checks
PHP reads the submitted JSON, validates it, and stores the cleaned layout.

Before saving, it checks:
- A WordPress nonce, which helps protect against forged requests.
- Whether the current user can edit that page.
- Whether the block types and settings are allowed.
- Whether URLs, colors, sizes, and spacing are valid.
When displaying the page, text and attributes are escaped so content cannot become executable HTML or JavaScript.
6. Converted saved blocks into HTML
On the public page, PHP reads the saved layout and renders each block:
- Heading → <h2>
- Text → <p>
- Button → <a>
- Image → <img>
- Spacer → an element with a defined height
If the layout is empty, WordPress displays the page’s normal content.

The overall flow is:

Editor controls → JavaScript layout → JSON form field → PHP validation → WordPress metadata → HTML output
7. Tested it locally
These were the development tools:
Tool	Purpose
Codex cloud workspace and terminal	Writing files and running commands
Node.js and npm	Running JavaScript tests
jsdom	Testing editor interactions in a simulated browser DOM
Docker Compose	Running a local WordPress development site
WordPress with PHP 8.3	Testing the actual plugin integration
MariaDB	Providing WordPress’s database
Git and GitHub	Saving and uploading version history
Python’s ZIP library	Building the installable plugin ZIP


I tested adding and editing blocks, reordering, duplication, deletion, saving, permission checks, sanitization, and frontend rendering. I also requested a published page over HTTP to verify that WordPress actually returned the builder’s output.

The JavaScript tests used a simulated DOM; I didn’t perform a full visual browser review.
8. Packaged and uploaded it
Finally, I created a ZIP containing the runtime plugin files inside an atelier-builder folder. Development dependencies such as node_modules were excluded.

Then I committed the source and ZIP to GitHub and added the version tag v0.1.0.

For learning, I’d suggest starting with a tiny plugin that adds one text field to a page, saves it as metadata, and displays it on the frontend. That teaches the same WordPress foundations Atelier uses. Once that works, add JavaScript controls and multiple block types.


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
