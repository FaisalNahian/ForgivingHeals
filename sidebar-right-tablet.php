    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 right-sidebar">
                <?php
					if ( is_front_page() && is_home() ) {
					    dynamic_sidebar( 'forgivingheals-right-sidebar' );
					} elseif ( is_front_page() ) {
					  	dynamic_sidebar( 'forgivingheals-right-sidebar' );
					} elseif ( is_home() || is_singular( 'post' ) ) {
					  	dynamic_sidebar( 'forgivingheals-blog-right-sidebar' );
					} else {
					  	dynamic_sidebar( 'forgivingheals-right-sidebar' );
					} 
					//this is for single quesiton only
					if(is_singular( 'story' )){
						dynamic_sidebar( 'forgivingheals-story-right-sidebar' );
					}					
                ?>       
            </div><!-- END RIGHT-SIDEBAR -->
            
        </div>
    </div>