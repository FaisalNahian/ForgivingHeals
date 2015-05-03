<?php

/**
 * Contains some quick method for post type
 */
class ET_PostType extends AE_Base {

	static $instance;
	public $name;
	public $args;
	public $taxonomy_args;
	public $meta_data;

	function __construct($name, $args, $taxonomy_args, $meta_data){
		$this->name 			= $name;
		$this->args 			= $args;
		$this->taxonomy_args 	= $taxonomy_args;
		$this->meta_data 		= $meta_data;
	}

	/**
	 * Init post type by registering post type and taxonomy
	 */
	static public function _init($name, $args, $taxonomy_args){
		// register post type
		register_post_type(
			$name,
			$args
		);
		// register taxonomies
		if (!empty($taxonomy_args)){
			foreach ($taxonomy_args as $tax_name => $args) {
				register_taxonomy( $tax_name, array($name), $args );
			}
		}
	}

	protected function trip_meta($data){
        // trip meta datas
        $args = $data;
		$meta = array();
		foreach ($args as $key => $value) {
			if ( in_array($key, $this->meta_data) ){
				$meta[$key] = $value;
				unset($args[$key]);
			}
		}

		return array(
			'args' 	=> $args,
			'meta' 	=> $meta
		);
	}

	/**
	 * Insert post type data into database
	 */
	protected function _insert($args){
		global $current_user;

		$args = wp_parse_args( $args, array(
            'post_type'     => $this->name,
            'post_status'   => 'pending',
        ) );

        if(isset($args['author']) && !empty($args['author'])) $args['post_author'] = $args['author'];

		if ( empty($args['post_author']) ) return new WP_Error('missing_author', __('Missing Author', ET_DOMAIN));

        // filter args
        $args = apply_filters( 'et_pre_insert_' . $this->name, $args );

        $data = $this->trip_meta($args);

        $result = wp_insert_post( $data['args'], true );
        /**
         * update custom field and tax
        */
        $this->update_custom_field ($result, $data, $args );

        // do action here
        do_action('et_insert_' . $this->name, $result);
        return $result;
	}

	/**
	 * Update post type data in database
	 */
	protected function _update($args){
		global $current_user;

		$args = wp_parse_args( $args );

		// filter args
        $args = apply_filters( 'et_pre_update_' . $this->name, $args );

		// if missing ID, return errors
        if (empty($args['ID'])) return new WP_Error('et_missing_ID', __('Thread not found!', ET_DOMAIN));

        // separate default data and meta data
        $data = $this->trip_meta($args);

    	// insert into database
        $result = wp_update_post( $data['args'], true );
        /**
         * update custom field and tax
        */
		$this->update_custom_field ($result, $data, $args );
        // make an action so develop can modify it
        do_action('et_update_' . $this->name, $result);
        return $result;
	}

	/**
	 * update post meta and taxonomy
	 * @param object $result post
	 * @param array $data post data
	 * @param array $args
	 * @author Dakachi
	 * @since version 1.0
	*/
	public function update_custom_field( $result, $data ,  $args ) {

		if ( !($result instanceof WP_Error) ){
			foreach ($this->taxonomy_args as $tax_name => $tax_args) {
				//if ( isset($args['tax_input'][$tax_name]) && term_exists($args['tax_input'][$tax_name], $tax_name) ){
				if ( isset($args['tax_input'][$tax_name]) ){
					$terms = wp_set_object_terms( $result, $args['tax_input'][$tax_name], $tax_name );
				}
			}
		}

        if ($result != false || !is_wp_error( $result )){
        	foreach ($data['meta'] as $key => $value) {
        		update_post_meta( $result, $key, $value );
        	}
        }
	}

	protected function _delete($ID, $force_delete = false){
		if ( $force_delete ){
			$result = wp_delete_post( $ID, true );
		} else {
			$result = wp_trash_post( $ID );
		}
		if ( $result )
			do_action('et_delete_' . $this->name, $ID);

		return $result;
	}

	protected function _update_field($id, $field_name, $value){
		update_post_meta( $id, $field_name, $value );
	}

	protected function _get_field($id, $field_name){
		return get_post_meta( $id, $field_name, true );
	}

	/**
	 * Get post type data by ID
	 */
	public function _get($id, $raw = false){
		$post = get_post($id);
		if ( $raw )
			return $raw;
		else
			return $this->_convert($post);
	}

	public function _convert($post, $taxonomy = true, $meta = true){
		$result = (array)$post;
		// echo '<pre>';
		// print_r($result);
		// echo '</pre>';
		// generate taxonomy
		if ( $taxonomy ){
			foreach ($this->taxonomy_args as $name => $args) {
				$result[$name]	 = wp_get_object_terms( $result['ID'], $name );
			}
		}

		// generate meta data
		if ( $meta ){
			foreach ($this->meta_data as $key) {
				$result[$key] 	= get_post_meta( $result['ID'], $key, true );
			}
		}
		$result['id']	=	$result['ID'];
		return (object)$result;
	}
	public static function vote($id, $type){
		global $current_user;

		$type = ($type == 'vote_up') ? 'vote_up' : 'vote_down';
		$vote_up_authors 	= (array) get_post_meta( $id, 'et_vote_up_authors', true);
		$vote_down_authors 	= (array) get_post_meta( $id, 'et_vote_down_authors', true);
		$vote_up 			= 0;
		$vote_down 			= 0;

		$comment_up = get_comments( array(
                'post_id'       => $id,
                'parent'        => 0,
                'status'        => 'approve',
                'post_status'   => 'publish',
                'order'         => 'ASC',
                'type'  		=> 'vote_up'
			) );
		$comment_down = get_comments( array(
                'post_id'       => $id,
                'parent'        => 0,
                'status'        => 'approve',
                'post_status'   => 'publish',
                'order'         => 'ASC',
                'type'  		=> 'vote_down'
			) );

		if ( in_array( $current_user->ID , $vote_up_authors ) ){

			$pos = array_search( $current_user->ID , $vote_up_authors );
			unset($vote_up_authors[$pos]);

			if ( $type == 'vote_down'){
				$vote_down_authors[] = $current_user->ID;
				array_unique($vote_down_authors);
			}
			if(!empty($comment_up)){
				$user_cmt = get_comments( array(
				                'post_id'       => $id,
				                'parent'        => 0,
				                'status'        => 'approve',
				                'post_status'   => 'publish',
				                'order'         => 'ASC',
				                'type'  		=> 'vote_up',
				                'user_id'		=> $current_user->ID
							) );
				wp_delete_comment( $user_cmt[0]->comment_ID, true );
			}
			else
				wp_insert_comment(array(
						'comment_post_ID' => $id,
						'comment_content' => $type,
						'comment_type' 	  => 'vote_up',
						'user_id'		  => $current_user->ID
					));

		} else if ( in_array( $current_user->ID , $vote_down_authors ) ){

			$pos = array_search( $current_user->ID , $vote_down_authors );
			unset($vote_down_authors[$pos]);

			if ( $type == 'vote_up'){
				$vote_up_authors[] = $current_user->ID;
				array_unique($vote_up_authors);
			}
			if(!empty($comment_down)){
				$user_cmt = get_comments( array(
				                'post_id'       => $id,
				                'parent'        => 0,
				                'status'        => 'approve',
				                'post_status'   => 'publish',
				                'order'         => 'ASC',
				                'type'  		=> 'vote_down',
				                'user_id'		=> $current_user->ID
							) );
				wp_delete_comment( $user_cmt[0]->comment_ID, true );
			}
			else
				wp_insert_comment(array(
						'comment_post_ID' => $id,
						'comment_content' => $type,
						'comment_type' 	  => 'vote_down',
						'user_id'		  => $current_user->ID
					));

		} else {
			/*================ INSERT COMMENT VOTE ================ */
			wp_insert_comment(array(
					'comment_post_ID' => $id,
					'comment_content' => $type,
					'comment_type' 	  => $type,
					'user_id'		  => $current_user->ID
				));
			/*================ INSERT COMMENT VOTE ================ */
			if ( $type == 'vote_up' ){
				$vote_up_authors[] 	= $current_user->ID;
			} else {
				$vote_down_authors[] 	= $current_user->ID;
			}
		}

		// remove empty item
		foreach ($vote_up_authors as $key => $value) {
			if ( $value === '' ){
				unset($vote_up_authors[$key]);
			}
		}

		// remove empty item
		foreach ($vote_down_authors as $key => $value) {
			if ( $value === '' ){
				unset($vote_down_authors[$key]);
			}
		}

		$comment_up = get_comments( array(
                'post_id'       => $id,
                'parent'        => 0,
                'status'        => 'approve',
                'post_status'   => 'publish',
                'order'         => 'ASC',
                'type'  		=> 'vote_up'
			) );
		$comment_down = get_comments( array(
                'post_id'       => $id,
                'parent'        => 0,
                'status'        => 'approve',
                'post_status'   => 'publish',
                'order'         => 'ASC',
                'type'  		=> 'vote_down'
			) );

		$vote_up 		= count($comment_up);
		$vote_down 		= count($comment_down);

		// save authors
		update_post_meta( $id, 'et_vote_up_authors', $vote_up_authors );
		update_post_meta( $id, 'et_vote_down_authors', $vote_down_authors );

		// save vote count
		update_post_meta( $id, 'et_vote_count' , $vote_up - $vote_down );
		//var_dump($vote_up .'-'. $vote_down);
	}
}

/**
 * Class ForgivingHeals_Stories
 */
class ForgivingHeals_Stories extends ET_PostType {
	CONST POST_TYPE = 'story';

	static $instance = null;

	public function __construct(){
		$this->name = self::POST_TYPE;
		$this->args = array(
			'labels' => array(
				'name'               => __('Stories', ET_DOMAIN ),
				'singular_name'      => __('Story', ET_DOMAIN ),
				'add_new'            => __('Add New', ET_DOMAIN ),
				'add_new_item'       => __('Add New Story', ET_DOMAIN ),
				'edit_item'          => __('Edit Story', ET_DOMAIN ),
				'new_item'           => __('New Story', ET_DOMAIN ),
				'all_items'          => __('All Stories', ET_DOMAIN ),
				'view_item'          => __('View Story', ET_DOMAIN ),
				'search_items'       => __('Search Stories', ET_DOMAIN ),
				'not_found'          => __('No stories found', ET_DOMAIN ),
				'not_found_in_trash' => __('No stories found in Trash', ET_DOMAIN ),
				'parent_item_colon'  => '',
				'menu_name'          => __('Stories', ET_DOMAIN )
			),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => ae_get_option('story_slug', 'story')),
			'capability_type'    => 'post',
			'has_archive'        => 'stories',
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array(
				'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields',  'revisions'
			)
		);
		$this->taxonomies =  array(
			'story_category' => array(
				'hierarchical'      => true,
				'labels'            => array(
					'name'              => __( 'Story Categories', ET_DOMAIN ),
					'singular_name'     => __( 'Category', ET_DOMAIN ),
					'search_items'      => __( 'Search Categories', ET_DOMAIN ),
					'all_items'         => __( 'All Categories', ET_DOMAIN ),
					'parent_item'       => __( 'Parent Category', ET_DOMAIN ),
					'parent_item_colon' => __( 'Parent Category:', ET_DOMAIN ),
					'edit_item'         => __( 'Edit Category' , ET_DOMAIN),
					'update_item'       => __( 'Update Category', ET_DOMAIN ),
					'add_new_item'      => __( 'Add New Category' , ET_DOMAIN),
					'new_item_name'     => __( 'New Category Name', ET_DOMAIN ),
					'menu_name'         => __( 'Category' , ET_DOMAIN),
				),
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite' 			=> array( 'slug' => ae_get_option('category_slug', 'story-category')),
			),
			'forgivingheals_tag'  => array(
				'hierarchical'          => false,
				'labels'                => array(
					'name'                       => _x( 'Tags', 'taxonomy general name' ),
					'singular_name'              => _x( 'Tag', 'taxonomy singular name' ),
					'search_items'               => __( 'Search Tags',ET_DOMAIN ),
					'popular_items'              => __( 'Popular Tags',ET_DOMAIN ),
					'all_items'                  => __( 'All Tags',ET_DOMAIN ),
					'parent_item'                => null,
					'parent_item_colon'          => null,
					'edit_item'                  => __( 'Edit Tag',ET_DOMAIN ),
					'update_item'                => __( 'Update Tag',ET_DOMAIN ),
					'add_new_item'               => __( 'Add New Tag',ET_DOMAIN ),
					'new_item_name'              => __( 'New Tag Name',ET_DOMAIN ),
					'separate_items_with_commas' => __( 'Separate tags with commas',ET_DOMAIN ),
					'add_or_remove_items'        => __( 'Add or remove tags',ET_DOMAIN ),
					'choose_from_most_used'      => __( 'Choose from the most used tags',ET_DOMAIN ),
					'not_found'                  => __( 'No tags found.',ET_DOMAIN ),
					'menu_name'                  => __( 'Tags',ET_DOMAIN ),
				),
				'show_ui'               => true,
				'show_admin_column'     => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var'             => true,
				'rewrite'               => array( 'slug' => ae_get_option('tag_slug', 'forgivingheals-tag') ),
			)
		);
		$this->meta_data = apply_filters( 'story_meta_fields', array(
			'et_vote_count',
			'et_view_count',
			'et_reactions_count',
			'et_users_follow',
			'et_reaction_authors',
			'et_last_author',
			'et_vote_up_authors',
			'et_vote_down_authors',
			'et_best_reaction'
		));
		parent::__construct( self::POST_TYPE , $this->args, $this->taxonomies, $this->meta_data );
	}

	/**
	 *
	 */
	static public function init(){
		$instance = self::get_instance();

		// register post type
		ET_PostType::_init( self::POST_TYPE , $instance->args, $instance->taxonomies);
	}

	static public function get_instance(){
		if ( self::$instance == null){
			self::$instance = new ForgivingHeals_Stories();
		}
		return self::$instance;
	}

	public static function insert($data){
		// required login
		global $user_ID;
		if ( !is_user_logged_in() )
			return new WP_Error('user_logged_required', __('Required Log In', ET_DOMAIN));
		$data['post_author'] = $user_ID;

		$instance = self::get_instance();
		$return = $instance->_insert($data);

		return $return;
	}
	static public function toggle_follow($story_id, $user_id){

		$users_follow_arr = explode(',',get_post_meta($story_id,'et_users_follow',true));
		$follow_stories = (array) get_user_meta( $user_id, 'forgivingheals_following_stories', true );

		//check story_id is in meta user follow
		if(!in_array($story_id, $follow_stories)){
			array_push($follow_stories, $story_id);
		} else {
			foreach ($follow_stories as $key => $value) {
				if ( $story_id == $value ){
					unset($follow_stories[$key]);
					break;
				}
			}
		}

		$follow_stories = array_unique(array_filter($follow_stories));
		update_user_meta( $user_id, 'forgivingheals_following_stories', $follow_stories );

		//check user_id is in meta follow story
		if(!in_array($user_id, $users_follow_arr)){
			array_push($users_follow_arr, $user_id);
		} else {
			foreach ($users_follow_arr as $key => $value) {
				if ( $user_id == $value ){
					unset($users_follow_arr[$key]);
					break;
				}
			}
		}

		$users_follow_arr = array_unique(array_filter($users_follow_arr));
		$users_follow     = implode(',', $users_follow_arr);

		ForgivingHeals_Stories::update_field($story_id, 'et_users_follow', $users_follow);

		return $users_follow_arr;
	}
	/**
	 * update story
	*/
	public static function update($data){

		global $current_user;

		if( isset($data['post_author']) && $current_user->ID != $data['post_author'] && !forgivingheals_user_can('edit_story') ) { // check user cap with edit story
			/**
			 * get site privileges
			*/
			$privileges	=	forgivingheals_get_privileges();
			return new WP_Error('cannot_edit', sprintf( __("You must have %d points to edit story.", ET_DOMAIN), $privileges->edit_story) );

		}

		// update story category
		if ( isset($data['story_category']) && isset($data['forgivingheals_tag']) ){

			$data['tax_input'] = array(
				'story_category' => $data['story_category'],
				'forgivingheals_tag'			=> $data['forgivingheals_tag']
			);
		}

		$instance = self::get_instance();
		$return = $instance->_update($data);

		return $return;
	}

	/**
	 * Delete a story + reaction of this story
	 */
	public static function delete($id, $force_delete = false){
		$instance 	= self::get_instance();
		$post 		= get_post($id);
		$story 	= ForgivingHeals_Stories::convert($post);
		$reactions 	= get_posts( array(
				'post_type'   => 'reaction',
				'post_parent' => $story->ID
			) );

		if(is_array($reactions) && count($reactions) > 0){
			foreach ($reactions as $reaction) {
				$deleted = wp_trash_post( $reaction->ID, $force_delete );
				if($deleted){
					//update reaction count
					$count = et_count_user_posts($reaction->post_author, 'reaction');
					update_user_meta( $reaction->post_author, 'et_reaction_count', $count );
				}
			}
		}

		$success = $instance->_delete($id, $force_delete);

		if($success){
			//update story count
			$count = et_count_user_posts($story->post_author, 'story');
			update_user_meta( $story->post_author, 'et_story_count', $count );
		}

		return $success;
	}

	public static function get($id){
		return	self::get_instance()->_get($id);
	}

	public static function convert($post){
		global $current_user;
		$result = self::get_instance()->_convert($post);

		$result->forgivingheals_tag 				= wp_get_object_terms( $post->ID, 'forgivingheals_tag' );

		$result->et_vote_up_authors 	= is_array($result->et_vote_up_authors) ? $result->et_vote_up_authors : array();
		$result->et_vote_down_authors 	= is_array($result->et_vote_down_authors) ? $result->et_vote_down_authors : array();
		$result->voted_down 			= in_array($current_user->ID, (array)$result->et_vote_down_authors);
		$result->voted_up 				= in_array($current_user->ID, (array)$result->et_vote_up_authors);
		$result->et_vote_count 			= get_post_meta( $post->ID, 'et_vote_count', true ) ? get_post_meta( $post->ID, 'et_vote_count', true ) : 0;
		$result->user_badge 		= forgivingheals_user_badge( $post->post_author, false );
		$result->et_reactions_count 	= et_count_reaction($post->ID);
		$result->et_view_count 		= $result->et_view_count ? $result->et_view_count : 0;
		$result->et_reaction_authors 	= is_array($result->et_reaction_authors) ? $result->et_reaction_authors : array();
		$result->reacted 			= in_array($current_user->ID, (array)$result->et_reaction_authors);
		$result->has_category 		= !empty($result->story_category);
		$result->content_filter		= apply_filters( 'the_content', $post->post_content );
		$result->content_edit       = et_the_content_edit($post->post_content);
		$result->author_name 		= get_the_author_meta('display_name', $post->post_author);
		$result->followed			= in_array($current_user->ID, (array)$result->et_users_follow);
		$result->reported  			= in_array($current_user->ID,(array)get_post_meta($post->ID, 'et_users_report', true ));

		return $result;
	}

	/**
	 * Refresh story's meta
	 */
	public static function update_meta($id){
		// refresh last update
		$last_reactions = get_posts(array(
			'post_type' 	=> 'reaction',
			'post_parent' 	=> $id,
			'numberposts' 	=> 1
		));

		if ( isset($last_reactions[0]) ){
			$last_reaction = $last_reactions[0];

			// update last reaction author
			update_post_meta( $id, 'et_last_author', $last_reaction->post_author );
		} else {
			delete_post_meta( $id, 'et_last_author' );
		}
	}

	/**
	 * Additional methods in theme
	 */
	public static function change_status($id, $new_status){
		$available_statuses = array('pending', 'publish', 'trash');

		if (in_array($new_status, $available_statuses))
			return self::update(array(
				'ID' => $id,
				'post_status' => $new_status
			));
		else
			return false;
	}

	// add new story
	public static function insert_story($title, $content, $cats, $status = "publish" , $author = 0){
		global $current_user;

		if ( empty($cats) ) return new WP_Error(__('Category must not empty', ET_DOMAIN));

		$data = array(
			'post_title' 		=> $title,
			'post_content' 		=> $content,
			'post_type' 		=> self::POST_TYPE,
			'post_author' 		=> !$author ? $current_user->ID : $author,
			'post_status' 		=> $status,
			'tax_input'			=> $cats,
			'et_updated_date' 	=> current_time( 'mysql' ),
		);

		$story_id = self::insert($data);

		//update story count
		$count = et_count_user_posts($current_user->ID, 'story');
		update_user_meta( $current_user->ID, 'et_story_count', $count );

		//update following stories
		$follow_stories = (array) get_user_meta( $current_user->ID, 'forgivingheals_following_stories', true );
		if(!in_array($story_id, $follow_stories))
			array_push($follow_stories, $story_id);
		$follow_stories = array_unique(array_filter($follow_stories));
		update_user_meta( $current_user->ID, 'forgivingheals_following_stories', $follow_stories );

		return $story_id;
	}

	// add like into database
	public static function toggle_like($story_id, $author = false){
		global $current_user;
		// required logged in
		if ( !$current_user->ID ) return false;

		// auto author
		if ( !$author ) $author = $current_user->ID;

		// get current likes list
		$likes = get_post_meta( $story_id, 'et_likes', true );

		// clear array
		if (!is_array($likes)) $likes = array();

		// add new author id
		$index = array_search($author, $likes);

		if ( $index === false){
			//$likes[] = $author;
			array_unshift($likes, $author);
			fe_update_user_likes($story_id);
		} else {
			foreach ($likes as $i => $id) {
				if ( $id == $author )
					unset($likes[$i]);
			}
			fe_update_user_likes($story_id,'unlike');
		}

		// update to database
		update_post_meta( $story_id, 'et_likes', $likes);
		update_post_meta( $story_id, 'et_like_count', count($likes));

		return $likes;
	}

	public static function report($story_id){
		global $current_user;
		// required logged in
		if ( !$current_user->ID ) return false;

		// get reports list
		$reports = ForgivingHeals_Stories::get_field($story_id, 'et_reports');

		//
		if ( !is_array($reports) ) $reports = array();

		if ( !in_array($current_user->ID, $reports) )
			$reports[] = $current_user->ID;

		ForgivingHeals_Stories::update_field($story_id, 'et_reports', $reports);
		return true;
	}

	public static function close($story_id){
		global $current_user;

		if ( !current_user_can( 'close_stories' ) ) return new WP_Error('permission_denied', __('Permission denied', ET_DOMAIN));

		//
		$result = ForgivingHeals_Stories::update( array(
			'ID' 			=> $story_id,
			'post_status' 	=> 'closed'
		) );

		return $result;
	}

	/**
	 * Retrieve comment number of a story and save to database
	 */
	public static function count_comments($story_id){
		global $wpdb;

		$sql 	= "SELECT count(*) FROM {$wpdb->posts} WHERE post_parent = $story_id AND post_type = 'reaction' AND post_status = 'publish'";
		$count 	= $wpdb->get_var($sql);

		// save
		update_post_meta($story_id, 'et_reactions_count', (int) $count);

		return $count;
	}

	public static function update_field($id, $key, $value){
		$instance = self::get_instance();

		$instance->_update_field($id, $key, $value);
	}

	public static function get_field($id, $key){
		$instance = self::get_instance();

		return $instance->_get_field($id, $key);
	}

	// search
	public static function search($data){

		$data = wp_parse_args( $data, array(
			'post_type' 	=> array(self::POST_TYPE),
			'post_status' 	=> array('publish','closed')
		) );

		if ($data['s']){
			global $et_query;
			$et_query['s'] = explode(' ', $data['s']);
			//unset($data['s']);
		}

		$query = new WP_Query($data);

		return $query;
	}

	/**
	 * Add a story category and colors
	 * @param $name category name
	 * @param $color category color, a hex code
	 * @param $parent parent category id, this is optional
	 * @return return array of term id and taxonomy
	 */
	public static function add_category($name, $color, $parent = 0){
		if ( $parent )
			$result = wp_insert_term( $name, 'story_category', array('parent' => $parent));
		else
			$result = wp_insert_term( $name, 'story_category');

		if ( !is_wp_error( $result ) ){
			$colors 					= get_option('et_category_colors', array());
			$colors[$result['term_id']] = (int)$color;

			update_option('et_category_colors', $colors);
		}

		return $result;
	}

	/**
	 * Edit a story category
	 * @param int $id term id
	 * @param array $args argument contain new values (name and color)
	 */
	public static function update_category($id, array $args){
		if (!empty($args)){
			// update normal params
			if ( !empty($args['name']) ){
				wp_update_term( $id, 'story_category', array('name' => $args['name']) );
			}

			// update color
			if ( !empty($args['color']) ){
				$colors 					= get_option('et_category_colors', array());
				$colors[$result['term_id']] = $color;

				update_option('et_category_colors', $colors);
			}
		}
	}

	public static function get_stories($args = array()){
		$args = wp_parse_args(  $args, array(
			'post_type'   => 'story',
		) );
		$query = new WP_Query($args);
		return $query;
	}

	/**
	 * static function callto set story reaction
	 * @param int $story_id
	 * @param int $reaction_id
	*/
	static public function mark_reaction( $story_id, $reaction_id ) {
		global $user_ID;
		/**
		 * get story pre reaction
		*/
		$pre_reaction	=	self::get_field( $story_id, 'et_best_reaction' );
		/**
		 * update story's reaction
		*/
		self::update_field( $story_id, 'et_best_reaction', $reaction_id);
		/**
		 * set reaction id is best reaction
		*/
		ForgivingHeals_Reactions::update_field( $reaction_id , 'et_is_best_reaction',  current_time('mysql') );

		/**
		 * delete pre reaction
		*/
		if( $pre_reaction ) {
			delete_post_meta( $pre_reaction, 'et_is_best_reaction' );
			/**
			 * do action when an reaction was unmark best reaction
			*/
			do_action('forgivingheals_remove_reaction', $pre_reaction );
		}
		/**
		 * do action when an story was mark reacted
		*/
		do_action( 'forgivingheals_mark_reaction', $story_id , $reaction_id );
	}

}
// end ForgivingHeals_Stories
/**
 * Class ForgivingHeals_Reactions
 */
class ForgivingHeals_Reactions extends ET_PostType {
	CONST POST_TYPE = 'reaction';

	static $instance = null;

	public function __construct(){
		$this->name = self::POST_TYPE;
		$this->args = array(
			'labels' => array(
			    'name' => __('Reactions', ET_DOMAIN),
			    'singular_name' => __('Reaction', ET_DOMAIN),
			    'add_new' => __('Add New', ET_DOMAIN),
			    'add_new_item' => __('Add New Reaction', ET_DOMAIN),
			    'edit_item' => __('Edit Reaction', ET_DOMAIN),
			    'new_item' => __('New Reaction', ET_DOMAIN),
			    'all_items' => __('All Reactions', ET_DOMAIN),
			    'view_item' => __('View Reaction', ET_DOMAIN),
			    'search_items' => __('Search Reactions', ET_DOMAIN),
			    'not_found' =>  __('No reactions found', ET_DOMAIN),
			    'not_found_in_trash' => __('No reactions found in Trash', ET_DOMAIN),
			    'parent_item_colon' => '',
			    'menu_name' => __('Reactions', ET_DOMAIN)
			),
		    'public' => true,
		    'publicly_queryable' => true,
		    'show_ui' => true,
		    'show_in_menu' => true,
		    'query_var' => true,
		    'rewrite' => array( 'slug' => apply_filters( 'fe_reaction_slug' , 'reaction' )),
		    'capability_type' => 'post',
		    'has_archive' => 'reactions',
		    'hierarchical' => false,
		    'menu_position' => null,
		    'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields',  'revisions' )
		);
		$this->taxonomies = array();
		$this->meta_data = apply_filters( 'reaction_meta_fields', array(
			'et_vote_count',
			'et_reaction_authors',
			'et_reactions_count',
			'et_vote_up_authors',
			'et_vote_down_authors',
			'et_is_best_reaction'
		));

		parent::__construct( self::POST_TYPE , $this->args, $this->taxonomies, $this->meta_data );
	}

	static public function init(){
		$instance = self::get_instance();
		parent::_init( self::POST_TYPE , $instance->args, $instance->taxonomies);
	}

	/**
	 * Action trigger after delete post
	 */
	public function action_delete_post($post_id){
		$post = get_post($post_id);
		if ( !empty($post) && $post->post_status == self::POST_TYPE )
			$this->action_update_counter($post_id, $post);
	}

	public function action_update_counter($post_id, $post){
		if ( $post->post_type != self::POST_TYPE ) return;

		// get reaction parent
		$reaction_parent 	= get_post_meta( $post_id, 'et_reaction_parent', true );

		if ( $reaction_parent ){
			ForgivingHeals_Reactions::count_comments($reaction_parent);
		} else {
			ForgivingHeals_Stories::count_comments($post->post_parent);
		}
	}

	static public function get_instance(){
		if ( self::$instance == null){
			self::$instance = new ForgivingHeals_Reactions();
		}
		return self::$instance;
	}

	public static function insert($data){

		global $user_ID;
		$data['post_author'] 	= isset($data['post_author']) ? $data['post_author'] : $user_ID;

		// perform action
		$instance 	= self::get_instance();
		$result 	=  $instance->_insert($data);

		return $result;
	}

	/**
	 * update_reaction
	*/
	public static function update($data){
		global $current_user;
		if(isset($data['post_author']) && $current_user->ID != $data['post_author'] && !forgivingheals_user_can('edit_reaction')) { // check user cap with edit reaction
			/**
			 * get site privileges
			*/
			$privileges	=	forgivingheals_get_privileges();

			return new WP_Error('cannot_edit', sprintf( __("You must have %d points to edit reaction.", ET_DOMAIN), $privileges->edit_reaction) );

		}

		$instance = self::get_instance();
		$result =  $instance->_update($data);

		return $result;
	}
	/**
	 * Delete a reaction and child reactions
	 * @param int $id
	 * @param bool $force_delete
	 * @return bool $success
	 */
	public static function delete($id, $force_delete = false){
		$instance = self::get_instance();

		$reaction   = get_post($id);
		$story = get_post( $reaction->post_parent );
		$reaction   = ForgivingHeals_Reactions::convert($reaction);
		$story = ForgivingHeals_Stories::convert($story);

		/* also delete story likes */
		$comments = get_comments(array(
	            'post_id'       => $id,
	            'parent'        => 0,
	            'status'        => 'approve',
	            'post_status'   => 'publish',
			));

		if (is_array($comments) && count($comments) > 0) {

		    foreach($comments as $comment){
		    	wp_delete_comment( $comment->comment_ID, $force_delete );
		    }
		}

		$success = $instance->_delete($id, $force_delete);

		if($success){

			//update reaction count
			$count = et_count_user_posts($reaction->post_author, 'reaction');
			update_user_meta( $reaction->post_author, 'et_reaction_count', $count );

			//update status reacted for story:
			$is_best_reaction = get_post_meta( $id, 'et_is_best_reaction', true );
			if($is_best_reaction){
				delete_post_meta( $story->ID, 'et_best_reaction' );
			}
		}

		return $success;
	}

	public static function get($id){
		return	self::get_instance()->_get($id);
	}

	public static function convert($post){
		global $current_user;
		$result = self::get_instance()->_convert($post);
		$parent = get_post($result->post_parent);

		$result->et_vote_up_authors 	= is_array($result->et_vote_up_authors) ? $result->et_vote_up_authors : array();
		$result->et_vote_down_authors 	= is_array($result->et_vote_down_authors) ? $result->et_vote_down_authors : array();
		$result->voted_down 			= in_array($current_user->ID, (array)$result->et_vote_down_authors);
		$result->voted_up 				= in_array($current_user->ID, (array)$result->et_vote_up_authors);
		$result->et_vote_count 			= get_post_meta( $post->ID, 'et_vote_count', true ) ? get_post_meta( $post->ID, 'et_vote_count', true ) : 0;
		$result->user_badge 		= forgivingheals_user_badge( $post->post_author, false, et_load_mobile() );
		$result->et_reactions_count 	= $result->et_reactions_count ? $result->et_reactions_count : 0;
		$result->et_reaction_authors 	= is_array($result->et_reaction_authors) ? $result->et_reaction_authors : array();
		$result->avatar 			= et_get_avatar( $result->post_author , 30 );
		$result->new_nonce			= wp_create_nonce( 'insert_comment' );
		$result->human_date 		= et_the_time(strtotime($result->post_date));
		$result->content_filter 	= apply_filters( 'the_content', $result->post_content );
		$result->content_edit       = et_the_content_edit($post->post_content);
		$result->parent_author		= $parent->post_author;
		$result->comments			= sprintf( __( 'Comment(%d) ', ET_DOMAIN ), $result->comment_count);
		$result->author_name 		= get_the_author_meta('display_name', $post->post_author);
		$result->author_url 		= get_author_posts_url($post->post_author);
		$result->reported  			= in_array($current_user->ID,(array)get_post_meta($post->ID, 'et_users_report', true ));
		return $result;
	}

	/**
	 * Additional methods in theme
	 */

	public static function insert_reaction($story_id, $content, $author = false, $reaction_id = 0){
		$instance = self::get_instance();

		global $current_user;

		if(!$current_user->ID)
			return new WP_Error('logged_in_required', __('Login Required',ET_DOMAIN));

		if($author == false)
			$author = $current_user->ID;

		$story = get_post($story_id);

		$content  = preg_replace('/\[quote\].*(<br\s*\/?>\s*).*\[\/quote\]/', '', $content);
		//strip all tag for mobile
		if(et_load_mobile())
			$content = strip_tags($content, '<p><br>');
		$data     = array(
			'post_title'       => 'RE: ' . $story->post_title,
			'post_content'     => $content,
			'post_parent'      => $story_id,
			'author'           => $author,
			'post_type'        => 'reaction',
			'post_status'      => ae_get_option('pending_reactions') && !(current_user_can( 'manage_options' ) || forgivingheals_user_can( 'approve_reaction' )) ? 'pending' : 'publish',
			'et_reaction_parent' => $reaction_id
		);

		$result = $instance->_insert($data);

		// if item is inserted successfully, update statistic
		if ($result){
			//update user reactions count
			$count = et_count_user_posts($current_user->ID, 'reaction');
			update_user_meta( $current_user->ID, 'et_reaction_count', $count );

			//update user following stories
			$follow_stories = (array) get_user_meta( $current_user->ID, 'forgivingheals_following_stories', true );
			if(!in_array($story_id, $follow_stories))
				array_push($follow_stories, $story_id);
			$follow_stories = array_unique(array_filter($follow_stories));
			update_user_meta( $current_user->ID, 'forgivingheals_following_stories', $follow_stories );

			// update story's update date
			update_post_meta( $story_id , 'et_updated_date', current_time( 'mysql' ));

			// update last update author
			update_post_meta( $story_id , 'et_last_author', $author);

			// update reaction_authors
			$reaction_authors = get_post_meta( $story_id , 'et_reaction_authors', true );
			$reaction_authors = is_array($reaction_authors) ? $reaction_authors : array();
			if ( !in_array($author, $reaction_authors) ){
				$reaction_authors[] = $author;
				update_post_meta( $story_id, 'et_reaction_authors', $reaction_authors );
			}
			// update reaction author for reaction
			if ( $reaction_id ){
				$reaction_authors = get_post_meta( $reaction_id , 'et_reaction_authors', true );
				$reaction_authors = is_array($reaction_authors) ? $reaction_authors : array();
				if ( !in_array($author, $reaction_authors) ){
					$reaction_authors[] = $author;
					update_post_meta( $reaction_id, 'et_reaction_authors', $reaction_authors );
				}
			}

			if ( $reaction_id == 0 ){
				ForgivingHeals_Stories::count_comments($story->ID);
			} else {
				ForgivingHeals_Reactions::count_comments($reaction_id);
			}
		}
		return $result;
	}

	/**
	 * Retrieve comment number of a story and save to database
	 */
	public static function count_comments($parent){
		global $wpdb;

		$sql 	= "SELECT count(*) FROM {$wpdb->posts}
					INNER JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id AND {$wpdb->postmeta}.meta_key = 'et_reaction_parent'
					WHERE {$wpdb->postmeta}.meta_value = $parent AND {$wpdb->posts}.post_type = 'reaction' AND {$wpdb->posts}.post_status = 'publish' ";

		$count 	= $wpdb->get_var($sql);

		// save
		update_post_meta($parent, 'et_reactions_count', (int) $count);

		return $count;
	}

	public static function update_field($id, $key, $value){
		$instance = self::get_instance();

		$instance->_update_field($id, $key, $value);
	}
}

/**
 * Class ForgivingHeals_Comments
 */
class ForgivingHeals_Comments {

	public static function convert($comment){
		global $current_user;
		$result = (object) $comment;
		$childs = get_children( array('post_parent'=> $result->comment_ID) );
		$author = get_user_by( 'id', $comment->user_id );

		$result->id             = $result->comment_ID;
		$result->et_votes       = get_comment_meta( $result->comment_ID, 'et_votes');
		$result->et_votes_count = !empty($result->et_votes) ? count($result->et_votes) : 0;
		$result->content_filter = apply_filters( 'the_content', $result->comment_content );
		$result->content_edit   = et_the_content_edit($comment->comment_content);
		$result->avatar         = et_get_avatar( $result->user_id ? $result->user_id : $result->comment_author_email , 30 );
		$result->human_date     = et_the_time(strtotime($result->comment_date));
		$result->total_childs   = sprintf( __( 'Comment(%d) ', ET_DOMAIN ), count($childs));
		$result->new_nonce      = wp_create_nonce( 'insert_comment' );
		$result->author         = $author->display_name;
		$result->author_url 	= get_author_posts_url($author->ID);

		return $result;
	}
	public static function insert($data){
		$meta_data 	= array('et_votes','et_votes_count','et_reaction_authors','et_reactions_count');
		//strip all tag for mobile
		if(et_load_mobile())
			$data['comment_content'] = strip_tags($data['comment_content'], '<p><br>');
		$result = wp_insert_comment($data);
		foreach ($meta_data as $key => $value) {
			add_comment_meta( $result, $value, '');
		}
		return $result;
	}
	public static function update($data){
		remove_filter( 'pre_comment_content', 'wp_filter_kses');
		return wp_update_comment( $data );
	}
	public static function delete($data){
	}
}

/**
 * Get last page of story
 */
function et_get_last_page($post_id){

	$number     = get_option( 'comments_per_page' );

	$all_comments       = get_comments( array(
	    'post_id' 	  => $post_id,
	    'parent'  	  => 0,
	    'status' 	  => 'approve',
	    'post_status' => 'publish',
	    'type'		  => 'reaction',
	    'order' 	  => 'ASC'
	 ) );

	$total_comments = count($all_comments);
	$total_pages    = ceil($total_comments / $number);

	if(!get_option( 'et_infinite_scroll' ) && $total_pages > 1 )
		return add_query_arg(array('page'=> $total_pages ),get_permalink( $post_id ));
	else
		return get_permalink( $post_id );
}
