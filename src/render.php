<?php
/**
 * Render callback for the sightsee-html-sitemap block.
 * 
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
    <?php
    $sitemap_cache = get_transient('my_sitemap_cache');

    if (!$sitemap_cache) {
        ob_start(); // Start output buffering

        // Sitemap generation code
        echo '<h2>' . esc_html__('Pages', 'sightsee-html-sitemap') . '</h2>';
        echo '<ul>';
        wp_list_pages(array('exclude' => '', 'title_li' => ''));
        echo '</ul>';

        $cats = get_categories(array('exclude' => ''));
        foreach ($cats as $cat) {
            echo '<h2>' . esc_html($cat->cat_name) . '</h2>';
            echo '<ul>';

            $cat_query = new WP_Query(array(
                'posts_per_page' => -1,
                'cat'            => $cat->cat_ID,
                'fields'         => 'ids'
            ));

            if ($cat_query->have_posts()) {
                foreach ($cat_query->posts as $post_id) {
                    echo '<li>';
                    echo '<a href="' . esc_url(get_permalink($post_id)) . '">' . esc_html(get_the_title($post_id)) . '</a>';
                    echo ' - ' . get_the_modified_date('F j, Y', $post_id);
                    echo '</li>';
                }
            }
            echo '</ul>';
            wp_reset_postdata();
        }

        $sitemap_cache = ob_get_clean(); // Get output and clean buffer
        set_transient('my_sitemap_cache', $sitemap_cache, 86400); // Cache for 1 day
    }

    echo $sitemap_cache; // Output the sitemap
    ?>
</div>