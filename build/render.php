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

        // Exclude 'noindex' pages
        $all_pages = get_posts(array(
            'post_type'   => 'page',
            'numberposts' => -1,
            'fields'      => 'ids',
            'post_status' => 'publish'  // Only fetch published pages
        ));

        $excluded_pages = array();
        foreach ($all_pages as $page_id) {
            $yoast_noindex = get_post_meta($page_id, '_yoast_wpseo_meta-robots-noindex', true);
            $seopress_index = get_post_meta($page_id, '_seopress_robots_index', true);
            $rank_math_robots = get_post_meta($page_id, 'rank_math_robots', true);

            if ($yoast_noindex == '1' || $seopress_index != 'yes' || (is_array($rank_math_robots) && in_array('noindex', $rank_math_robots))) {
                $excluded_pages[] = $page_id;
            }
        }

        echo '<h2>' . esc_html__('Pages', 'sightsee-html-sitemap') . '</h2>';
        echo '<ul>';
        wp_list_pages(array(
            'exclude'  => implode(',', $excluded_pages),
            'title_li' => ''
        ));
        echo '</ul>';

        // Posts by category
        $cats = get_categories(array('exclude' => ''));
        foreach ($cats as $cat) {
            echo '<h2>' . esc_html($cat->cat_name) . '</h2>';
            echo '<ul>';

            // Exclude 'noindex' posts
            $cat_query = new WP_Query(array(
                'posts_per_page' => -1,
                'cat'            => $cat->cat_ID,
                'fields'         => 'ids',
                'post_status'    => 'publish',  // Only fetch published posts
                'meta_query'     => array(
                    'relation' => 'OR',
                    array(
                        'key'     => '_yoast_wpseo_meta-robots-noindex',
                        'value'   => '1',
                        'compare' => '!='
                    ),
                    array(
                        'key'     => '_seopress_robots_index',
                        'value'   => 'yes',
                        'compare' => '='
                    ),
                    array(
                        'key'     => 'rank_math_robots',
                        'value'   => 'index',
                        'compare' => 'LIKE'
                    )
                )
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