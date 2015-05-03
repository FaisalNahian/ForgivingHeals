<?php
/**
 * Template Name: Search Stories Template
 *
 * @package ForgivingHeals
 * @since ForgivingHeals 1.0
 */
global $wp_query;
get_header();
$keyword = urldecode(get_query_var( 'keyword' ));
?>
    <?php get_sidebar( 'left' ); ?>
    <div class="col-md-8 main-content">
        <div class="row select-category">
            <div class="col-md-6 col-xs-6 current-category">
                <span><?php printf( __("Search Stories: <em>%s</em>", ET_DOMAIN ), esc_attr( $keyword ) ) ; ?></span>
            </div>
            <div class="col-md-6 col-xs-6">
                <?php forgivingheals_tax_dropdown() ?>
            </div>
        </div><!-- END SELECT-CATEGORY -->
        <?php forgivingheals_template_filter_stories(); ?>
        <div class="main-stories-list">
            <ul id="main_stories_list">
                <?php
                    $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
                    $args  = array(
                            'post_type' => 'story',
                            'paged'     => $paged,
                            's'         => $keyword
                        );

                    if( isset($_GET['numbers']) && $_GET['numbers'])
                        $args['posts_per_page'] = $_GET['numbers'];

                    $search_query = new WP_Query($args);
                    if($search_query->have_posts()){
                        while($search_query->have_posts()){
                            $search_query->the_post();
                            get_template_part( 'template/story', 'loop' );
                        }
                    } else {
                        echo '<h2>';
                        _e('No results for keyword:', ET_DOMAIN);
                        echo '<strong><em>'.esc_attr( $keyword ).'</em></strong>';
                        echo '</h2>';
                    }
                    wp_reset_query();
                ?>
            </ul>
        </div><!-- END MAIN-STORIES-LIST -->
        <div class="row paginations home">
            <div class="col-md-12">
                <?php
                    forgivingheals_template_paginations($search_query, $paged);
                ?>
            </div>
        </div><!-- END MAIN-PAGINATIONS -->
    </div>
    <?php get_sidebar( 'right' ); ?>
<?php get_footer() ?>