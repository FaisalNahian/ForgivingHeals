<?php 
    global $forgivingheals_tag_pages;
    // init number of tags perpage
    //$number     = apply_filters( 'forgivingheals_popular_tags_per_page' , ( 4*get_option( 'posts_per_page', 10 )) ) ;
    $number     = get_option( 'posts_per_page', 10 );
    $paged      = (get_query_var('paged')) ? get_query_var('paged') : 1;  
    // offset query
    $offset     = ($paged - 1) * $number;

    $args   = array(    'hide_empty' => false,
                        'orderby'    => 'count',
                        'order'      => 'DESC',
                        'number'     => $number,
                        'offset'     => $offset
            );

    $total_args =   array ('hide_empty' => 0 );
    if ( isset($_GET['tkey']) && $_GET['tkey'] != "" ) {
       $total_args['search']  = $args['search']    = $_GET['tkey'];
    }

    $tags   =   get_terms( 'forgivingheals_tag', $total_args );
    // get tags by query 
    $query      = get_terms( 'forgivingheals_tag', $args ); 

    if( !empty($query) ) {
        ?>
        <div class="col-md-12 col-xs-12">
            <div class="tags-wrapper">
                <ul class="tags">        
        <?php
        foreach ($query as $key => $tag) {
        ?>
                    <li>
                        <span class="tag"><a href="<?php echo get_term_link( $tag, 'forgivingheals_tag' ); ?>"><?php echo $tag->name ?></a>x <?php echo $tag->count ?></span>
                        <span class="time-tag"><?php echo forgivingheals_count_post_in_tags( $tag->slug ) ?></span>
                    </li>
        <?php 
        }
        ?>
                </ul>
            </div>
        </div>
        <?php
    }

    $forgivingheals_tag_pages  =   ceil( count($tags)/$number );
?>