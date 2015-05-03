<?php
/**
 * The template for displaying story pages
 *
 * @package ForgivingHeals
 * @since ForgivingHeals 1.0
 */
global $wp_query;
get_header();
?>
    <?php get_sidebar( 'left' ); ?>
    <div class="col-md-8 main-content">
        <div class="row select-category">
            <div class="col-md-6 col-xs-6 current-category">
                <span><?php _e("All Stories", ET_DOMAIN ); ?></span>
            </div>
            <div class="col-md-6 col-xs-6">
                <?php forgivingheals_tax_dropdown() ?>
            </div>          
        </div><!-- END SELECT-CATEGORY -->
        <?php forgivingheals_template_filter_stories() ?>
        <div class="main-stories-list">
            <ul id="main_stories_list">
                <?php
                    if(have_posts()){
                        while(have_posts()){
                            the_post();
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
                    $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
                    echo paginate_links( array(
                        'base'      => str_replace('99999', '%#%', esc_url(get_pagenum_link( 99999 ))),
                        'format'    => $wp_rewrite->using_permalinks() ? 'page/%#%' : '?paged=%#%',
                        'current'   => max(1, $paged),
                        'total'     => $wp_query->max_num_pages,
                        'mid_size'  => 1,
                        'prev_text' => '<',
                        'next_text' => '>',
                        'type'      => 'list'
                    ) ); 
                ?>                
            </div>
        </div><!-- END MAIN-PAGINATIONS -->      
    </div>
    <?php get_sidebar( 'right' ); ?>
<?php get_footer() ?>