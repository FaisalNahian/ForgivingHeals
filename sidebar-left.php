    <div class="col-md-2 disable-mobile left-sidebar">

        <div class="widget widget-btn">
        	<button type="button" data-toggle="modal" class="action share-story">
                <i class="fa fa-plus"></i> <?php _e("SHARE A STORY",ET_DOMAIN) ?>
            </button>
        </div><!-- END BUTTON MODAL STORY -->
        
        <div class="widget widget-menus">
            <?php
                if(has_nav_menu('et_left')){
                    wp_nav_menu( array(
                        'theme_location' => 'et_left',
                        'depth'          => '1',
                        'walker'         => new ForgivingHeals_Custom_Walker_Nav_Menu()
                    ) );
                }
            ?>
        </div><!-- END LEFT MENU -->

        <?php
            if ( is_front_page() && is_home() ) {
                dynamic_sidebar( 'forgivingheals-left-sidebar' );
            } elseif ( is_front_page() ) {
                dynamic_sidebar( 'forgivingheals-left-sidebar' );
            } elseif ( is_home() || is_singular( 'post' ) ) {
                dynamic_sidebar( 'forgivingheals-blog-left-sidebar' );
            } else {
                dynamic_sidebar( 'forgivingheals-left-sidebar' );
            }        
        ?>

        <div class="copyright">
        	&copy;<?php echo date('Y') ?> <?php echo ae_get_option( 'copyright' ); ?> <br>
			Developed by <a href="heyfaisal.com">Faisal Nahian</a>
            <br><a href="<?php echo et_get_page_link("term"); ?>"><?php _e("Terms & Privacy", ET_DOMAIN) ?></a>
        </div>
    </div><!-- END LEFT-SIDEBAR -->