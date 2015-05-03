<?php
    global $post, $forgivingheals_reaction, $forgivingheals_story, $current_user;
   
    if($post->post_type == 'story') {
        $object =   $forgivingheals_story;
    }else {
        $object =   $forgivingheals_reaction;
    }

    $vote_up_class  =  'action vote vote-up img-circle ' ;
    $vote_up_class  .= ($object->voted_up) ? 'active' : '';
    $vote_up_class  .= ($object->voted_down) ? 'disabled' : ''; 

    $vote_down_class = 'action vote vote-down img-circle ';
    $vote_down_class .= ($object->voted_down) ? 'active' : '';
    $vote_down_class .= ($object->voted_up) ? 'disabled' : ''; 

    /**
     * check privileges
    */
    $privi  =   forgivingheals_get_privileges();
    $vote_up_prover     =   '';
    $vote_down_prover   =   '';

    if( !forgivingheals_user_can('vote_up') && isset( $privi->vote_up ) ) {
        $content          = sprintf(__("You must have %d points to vote up.", ET_DOMAIN), $privi->vote_up )   ;
        $vote_up_prover =   'data-container="body" data-toggle="popover" data-content="'. $content .'"';
    }        

    if( !forgivingheals_user_can('vote_down') && isset( $privi->vote_down ) ) {
        $content          = sprintf(__("You must have %d points to vote down.", ET_DOMAIN), $privi->vote_down )   ;
        $vote_down_prover = ' data-container="body" data-toggle="popover" data-content="'. $content .'"';
    }

?>
<div class="col-md-2 col-xs-2 vote-block">
	<!-- vote group -->
    <ul>    
        <!-- vote up -->
        <li title="<?php _e("This is useful.", ET_DOMAIN); ?>">
        	<a <?php echo $vote_up_prover ?>  href="javascript:void(0)" data-name="vote_up"  
                class="<?php echo $vote_up_class; ?>" >
        		<i class="fa fa-chevron-up"></i>
        	</a>
        </li>
        <!--// vote up -->

        <!--vote point -->
        <li>
        	<span class="vote-count"><?php echo $object->et_vote_count ?></span>
        </li>
        <!--// vote point -->
        <!-- vote down -->
        <li title="<?php _e("This is not useful", ET_DOMAIN); ?>">
        	<a <?php echo $vote_down_prover ?>  href="javascript:void(0)" data-name="vote_down" 
                class="<?php echo $vote_down_class; ?>">
        		<i class="fa fa-chevron-down"></i>
        	</a>
        </li>	
        <!--// vote down -->
		<?php
            if( is_singular( 'story' ) ){

        		if($post->post_type == 'reaction' )  {
                    $active    =  ( $forgivingheals_story->et_best_reaction == $forgivingheals_reaction->ID ) ? 'active' : '';

                    if( $current_user->ID == $forgivingheals_story->post_author || $active != '' ){

                        $reaction_authorname  =   get_the_author_meta('display_name', $forgivingheals_reaction->post_author  );

                        $data_name = 'data-name="'. ($forgivingheals_story->et_best_reaction == $forgivingheals_reaction->ID ? 'un-accept-reaction' : 'accept-reaction' ). '"';
                    
            		?>
                    <li title="<?php _e('Mark as best reaction', ET_DOMAIN );//printf(__("Agree with %s", ET_DOMAIN), $reaction_authorname ); ?>" >
                    	<a  href="javascript:void(0)" 
                    	   <?php echo $data_name; ?> class="action accept-reaction img-circle <?php echo $active  ?>">
                    		<i class="fa fa-check"></i>
                    	</a>
                    </li>
            		<?php
                    } 
                }
            }
        ?>
    </ul>
    <!--// vote group -->
</div>