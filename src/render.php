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

        $all_pages = get_posts(array(
            'post_type'   => 'page',
            'numberposts' => -1,
            'fields'      => 'ids',
            'post_status' => 'publish'
        ));
        
        $indexed_pages = array();
        foreach ($all_pages as $page_id) {
            $page = get_post($page_id);
        
            // Skip password protected pages
            if (!empty($page->post_password)) {
                continue;
            }
            
            $yoast_noindex = get_post_meta($page_id, '_yoast_wpseo_meta-robots-noindex', true);
            $seopress_index = get_post_meta($page_id, '_seopress_robots_index', true);
            $rank_math_robots = get_post_meta($page_id, 'rank_math_robots', true);
        
            // Assume the page is indexed by default
            $is_indexed = true;
        
            // Check if Yoast SEO has set the page to 'noindex'
            if ($yoast_noindex == '1') {
                $is_indexed = false;
            }
        
            // Check if SEO Press has explicitly set the page to 'noindex'
            if ($seopress_index === 'yes') {
                $is_indexed = false;
            }
        
            // Check Rank Math's setting for 'noindex' (considering serialized data issues)
            if (is_array($rank_math_robots) && in_array('noindex', $rank_math_robots)) {
                $is_indexed = false;
            }
        
            // Add the page to the array only if it is indexed
            if ($is_indexed) {
                $indexed_pages[] = $page_id;
            }
        }
        
        echo '<h2>' . esc_html__('Pages', 'sightsee-html-sitemap') . '</h2>';
        echo '<ul>';
        foreach ($indexed_pages as $page_id) {
            echo '<li><a href="' . esc_url(get_permalink($page_id)) . '">' . esc_html(get_the_title($page_id)) . '</a></li>';
        }
        echo '</ul>';

// Posts by category
$cats = get_categories(array('exclude' => ''));

foreach ($cats as $cat) {
    // Prepare the query for posts in this category
    $cat_query = new WP_Query(array(
        'posts_per_page' => -1,
        'cat'            => $cat->cat_ID,
        'fields'         => 'ids',
        'post_status'    => 'publish',
                'meta_query'     => array(
                    'relation' => 'OR',
                    array(
                        'key'     => '_yoast_wpseo_meta-robots-noindex',
                        'value'   => '1',
                        'compare' => '!='
                    ),
                    array(
                        'key'     => '_seopress_robots_index',
                        'compare' => 'NOT EXISTS'
                    ),
                    array(
                        'key'     => 'rank_math_robots',
                        'value'   => 'index',
                        'compare' => 'LIKE'
                    )
                )
            ));

            if ($cat_query->have_posts()) {
                echo '<h2>' . esc_html($cat->cat_name) . '</h2>';
                echo '<ul>';
                
                foreach ($cat_query->posts as $post_id) {
                    echo '<li>';
                    echo '<a href="' . esc_url(get_permalink($post_id)) . '">' . esc_html(get_the_title($post_id)) . '</a>';
                    echo ' - ' . get_the_modified_date('F j, Y', $post_id);
                    echo '</li>';
                }
                
                echo '</ul>';
            }
        
            wp_reset_postdata();
        }

        $sitemap_cache = ob_get_clean(); // Get output and clean buffer
        set_transient('my_sitemap_cache', $sitemap_cache, 86400); // Cache for 1 day
    }

    echo $sitemap_cache; // Output the sitemap
    ?>
</div>