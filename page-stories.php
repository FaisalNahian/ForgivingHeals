<?php
/**
 * Template Name: Stories List Template
 * version 1.0
 * @author: MaxWeb
 **/
get_header();
?>
    <?php get_sidebar( 'left' ); ?>
    <div class="col-md-8 main-content">
        <?php do_action( 'forgivingheals_top_stories_listing' ); ?>

        <div class="clearfix"></div>

        <div class="row select-category">
            <div class="col-md-6 col-xs-6 current-category">
                <span><?php _e("All Stories", ET_DOMAIN ); ?></span>
            </div>
            <div class="col-md-6 col-xs-6">
                <?php forgivingheals_tax_dropdown() ?>
            </div>
        </div><!-- END SELECT-CATEGORY -->
        <?php forgivingheals_template_filter_stories(); ?>
        <div class="main-stories-list">
            <ul id="main_stories_list">
                <?php

                    if(get_query_var( 'page' )){
                        $paged = get_query_var( 'page' ) ? get_query_var( 'page' ) : 1;
                    } else {
                        $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
                    }

                    $args  = array(
                            'post_type' => 'story',
                            'paged'     => $paged
                        );

                    if( isset($_GET['numbers']) && $_GET['numbers'])
                        $args['posts_per_page'] = $_GET['numbers'];

                    // if ( isset($_GET['sort']) && $_GET["sort"] == "unreacted" ) {
                    //     add_filter("posts_join"      , array("ForgivingHeals_Front", "_post_unreacted_join") );
                    //     add_filter("posts_orderby"   , array("ForgivingHeals_Front", "_post_unreacted_where") );
                    // }

                    $query = ForgivingHeals_Stories::get_stories($args);

                    if($query->have_posts()){
                        while($query->have_posts()){
                            $query->the_post();
                            get_template_part( 'template/story', 'loop' );
                        }
                    }
                    wp_reset_query();
                ?>
            </ul>
        </div><!-- END MAIN-STORIES-LIST -->
        <div class="row paginations home">
            <div class="col-md-12">
                <?php
                    forgivingheals_template_paginations($query, $paged);
                ?>
            </div>
        </div><!-- END MAIN-PAGINATIONS -->

        <div class="clearfix"></div>

        <?php do_action( 'forgivingheals_btm_stories_listing' ); ?>
    </div>
    <?php get_sidebar( 'right' ); ?>
<?php get_footer() ?>