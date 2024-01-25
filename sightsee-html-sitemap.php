<?php
/**
 * Plugin Name:       Sightsee HTML Sitemap
 * Description:       An easy HTML Sitemap plugin created by the team at Sightsee Design.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.1
 * Author:            Sightsee Design
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sightsee-html-sitemap
 *
 * @package           create-block
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function sightsee_html_sitemap_sightsee_html_sitemap_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'sightsee_html_sitemap_sightsee_html_sitemap_block_init' );

/**
 * Clear the sitemap cache whenever a post or page is saved.
 */
function sightsee_html_sitemap_clear_cache_on_save_post($post_id) {
    // Skip clearing cache on autosaves
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Skip clearing cache on bulk edits
    if (isset($_REQUEST['bulk_edit'])) {
        return;
    }

    delete_transient('my_sitemap_cache');
}

add_action('save_post', 'sightsee_html_sitemap_clear_cache_on_save_post');