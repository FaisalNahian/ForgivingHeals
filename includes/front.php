<?php

class ForgivingHeals_Front extends ForgivingHeals{

	/**
	 * Init
	 */
	public function __construct(){
		parent::__construct();

		new ForgivingHeals_FrontPost();
		// set timeout for send mail to user's following story
		$time_send_mail = apply_filters( 'forgivingheals_time_send_mail' , 120 );

		if(ae_get_option( 'forgivingheals_send_following_mail' )){
			wp_schedule_single_event( time() + $time_send_mail, 'forgivingheals_send_following_mail' );
		} else {
			wp_clear_scheduled_hook( 'forgivingheals_send_following_mail' );
		}

		$this->add_filter( 'query_vars'					, 'query_vars' );
		$this->add_filter( 'mce_external_plugins'		, 'tinymce_add_plugins');
		$this->add_filter( 'request'					, 'filter_request_feed');


		$this->add_action( 'init'					, 'frontend_init');
		$this->add_action( 'wp_footer'				, 'scripts_in_footer', 100);
		$this->add_action( 'pre_get_posts'			, 'custom_query' );
		$this->add_filter( 'comment_post_redirect'	, 'forgivingheals_comment_redirect' );
	}
	function forgivingheals_comment_redirect( $location )
	{
		global $post;
	    return get_permalink( $post->ID );
	}
	public function filter_request_feed($request) {
	    if (isset($request['feed']) && !isset($request['post_type'])):
	        $request['post_type'] = array("story");
	    endif;

	    return $request;
	}

	public function custom_query($query){
		global $wp_query;

		if (!$query->is_main_query())
			return $query;

		if( is_search() ){
			$query->set('post_type', 'post');
		}

		if(is_author()){
			if( !isset($_GET['type'])  || $_GET['type'] == "following")
				$query->set("post_type","story");
			else
				$query->set("post_type",$_GET['type']);
		}

		if( isset($_GET["sort"])){
			if( $_GET["sort"] == "vote" ){

				$this->add_filter("posts_join"		, "_post_vote_join");
				$this->add_filter("posts_orderby"	, "_post_vote_orderby");

			} elseif ($_GET["sort"] == "unreacted") {

				$this->add_filter("posts_join"		, "_post_unreacted_join");
				$this->add_filter("posts_orderby"	, "_post_unreacted_orderby");

			} elseif ($_GET["sort"] == "oldest") {

				$query->set('order', 'ASC');

			}
		}
		if( isset($_GET['numbers']) ){

			$query->set('posts_per_page', $_GET['numbers']);

		}
		return $query;

	}
	public static function _post_vote_join($join){
		global $wpdb;
		$join .= " LEFT JOIN {$wpdb->postmeta} as order_vote ON order_vote.post_id = {$wpdb->posts}.ID AND ( order_vote.meta_key = 'et_vote_count' ) ";
		$join .= " LEFT JOIN {$wpdb->postmeta} as order_best ON order_best.post_id = {$wpdb->posts}.ID AND ( order_best.meta_key = 'et_is_best_reaction' ) ";
		return $join;
	}

	public static function _post_vote_orderby($orderby){
		global $wpdb;
		$orderby = " order_best.meta_value DESC, CAST(order_vote.meta_value AS SIGNED) DESC, {$wpdb->posts}.post_date ASC";
		return $orderby;
	}

	public static function _post_unreacted_join($join){
		global $wpdb;
		$join .= " LEFT JOIN {$wpdb->postmeta} as reaction_count ON reaction_count.post_id = {$wpdb->posts}.ID AND reaction_count.meta_key = 'et_reactions_count'";
		return $join;
	}

	public static function _post_unreacted_orderby($orderby){
		global $wpdb;
		$orderby = " CAST(reaction_count.meta_value AS SIGNED) ASC, {$wpdb->posts}.post_date DESC";
		return $orderby;
	}
	public function frontend_init($wp_rewrite){
		global $wp_rewrite, $current_user;
		// modify the "search stories" link
		$search_slug = apply_filters( 'search_story_slug'			, 'search-stories' );
		add_rewrite_rule( $search_slug . '/([^/]+)/?$'					, 'index.php?pagename=search&keyword=$matches[1]', 'top' );
		add_rewrite_rule( $search_slug . '/([^/]+)/page/([0-9]{1,})/?$'	, 'index.php?pagename=search&keyword=$matches[1]&paged=$matches[2]', 'top' );

	    $author_slug = apply_filters( 'forgivingheals_member_slug', 'member' ); // change slug name
	    $wp_rewrite->author_base = $author_slug;

		$rules = get_option( 'rewrite_rules' );
		if ( !isset($rules[$search_slug . '/([^/]+)/?$']) ){
			$wp_rewrite->flush_rules();
		}
		// check ban user
		$user_factory = ForgivingHeals_Member::get_instance();
		if ( $current_user->ID && $user_factory->is_ban( $current_user->ID ) ){
			wp_logout();
			wp_clear_auth_cookie();
		}
	}
	public function query_vars($vars){
		$vars[] = 'keyword';
		return $vars;
	}

	public function scripts_in_footer(){
		global $current_user;
		?>
		<script type="text/javascript">
            _.templateSettings = {
                evaluate: /\<\#(.+?)\#\>/g,
                interpolate: /\{\{=(.+?)\}\}/g,
                escape: /\{\{-(.+?)\}\}/g
            };
        </script>
		<script type="text/javascript" id="frontend_scripts">
			(function ($) {
				$(document).ready(function(){

					<?php if(!et_load_mobile()){ ?>

					if(typeof ForgivingHeals.Views.Front != 'undefined') {
						ForgivingHeals.App = new ForgivingHeals.Views.Front();
					}

					if(typeof ForgivingHeals.Views.Intro != 'undefined') {
						ForgivingHeals.Intro = new ForgivingHeals.Views.Intro();
					}

					if(typeof ForgivingHeals.Views.UserProfile != 'undefined') {
						ForgivingHeals.UserProfile = new ForgivingHeals.Views.UserProfile();
					}

					if(typeof ForgivingHeals.Views.Single_Story != 'undefined') {
						ForgivingHeals.Single_Story = new ForgivingHeals.Views.Single_Story();
					}

					<?php if( is_page_template( 'page-pending.php' ) ) { ?>
					if(typeof ForgivingHeals.Views.PendingStories != 'undefined') {
						ForgivingHeals.PendingStories = new ForgivingHeals.Views.PendingStories();
					}
					<?php } ?>

					/*======= Open Reset Password Form ======= */
					<?php if( isset($_GET['user_login']) && isset($_GET['key']) && !is_user_logged_in() ){ ?>
						var resetPassModal = new ForgivingHeals.Views.ResetPassModal({ el: $("#reset_password") });
						resetPassModal.openModal();
					<?php } ?>

					/*======= Open Reset Password Form ======= */
					<?php if( isset($_GET['confirm']) && $_GET['confirm'] == 0 ){ ?>
						AE.pubsub.trigger('ae:notification', {
							msg: "<?php _e("You need to verify your account to view the content.",ET_DOMAIN)  ?>",
							notice_type: 'error',
						});
					<?php } ?>

					/*======= Open Confirmation Message Modal ======= */
					<?php
						global $forgivingheals_confirm;
						if( $forgivingheals_confirm ){
					?>
						AE.pubsub.trigger('ae:notification', {
							msg: "<?php _e("Your account has been confirmed successfully!",ET_DOMAIN)  ?>",
							notice_type: 'success',
						});
					<?php } ?>

					<?php } ?>
				});
			})(jQuery);
		</script>
		<script type="text/javascript" id="current_user">
		 	currentUser = <?php
		 	if ($current_user->ID)
		 		echo json_encode(ForgivingHeals_Member::convert($current_user));
		 	else
		 		echo json_encode(array('id' => 0, 'ID' => 0));
		 	?>
		</script>
		<?php
		echo '<!-- GOOGLE ANALYTICS CODE -->';
        $google = ae_get_option('google_analytics');
        $google = implode("",explode("\\",$google));
        echo stripslashes(trim($google));
		echo '<!-- END GOOGLE ANALYTICS CODE -->';
	}

	public function on_add_scripts(){
		parent::on_add_scripts();

		// default scripts: jquery, backbone, underscore
		$this->add_existed_script('jquery');
		$this->add_existed_script('underscore');
		$this->add_existed_script('backbone');

		$this->add_script('site-core', 			ae_get_url(). '/assets/js/appengine.js',array('jquery', 'backbone', 'underscore','plupload'));
		$this->add_script('site-functions', 	TEMPLATEURL . '/js/functions.js',array('jquery', 'backbone', 'underscore'));

		$this->add_script('bootstrap', 			TEMPLATEURL . '/js/libs/bootstrap.min.js');
		$this->add_script('modernizr', 			TEMPLATEURL . '/js/libs/modernizr.js', array('jquery'));
		//$this->add_script('adjector', 			TEMPLATEURL . '/js/libs/adjector.js','jquery');
		$this->add_script('rotator', 			TEMPLATEURL . '/js/libs/jquery.simple-text-rotator.min.js','jquery');
		$this->add_script('jquery-validator', 	TEMPLATEURL . '/js/libs/jquery.validate.min.js','jquery');

		$this->add_existed_script('jquery-ui-autocomplete');

		if(et_load_mobile()){
			return;
		} else {

			if( ae_get_option('forgivingheals_live_notifications') ){
				$this->add_existed_script('heartbeat');
			}

			$this->add_script('waypoints', 			TEMPLATEURL . '/js/libs/waypoints.min.js', array('jquery'));
			$this->add_script('waypoints-sticky', 	TEMPLATEURL . '/js/libs/waypoints-sticky.js', array('jquery', 'waypoints'));
			$this->add_script('chosen', 			TEMPLATEURL . '/js/libs/chosen.jquery.min.js', array('jquery'));
			$this->add_script('classie', 			TEMPLATEURL . '/js/libs/classie.js', array('jquery'));
			$this->add_script('site-script', 		TEMPLATEURL . '/js/scripts.js', 'jquery');
			$this->add_script('site-front', 		TEMPLATEURL . '/js/front.js', array('jquery', 'underscore', 'backbone', 'site-functions'));

			//localize scripts
			wp_localize_script( 'site-front', 'forgivingheals_front', forgivingheals_static_texts() );

			if( is_singular( 'story' ) || is_singular( 'reaction' ) ){
				$this->add_script('forgivingheals-shcore', TEMPLATEURL . '/js/libs/syntaxhighlighter/shCore.js', array('jquery'));
				$this->add_script('forgivingheals-brush-js', TEMPLATEURL . '/js/libs/syntaxhighlighter/shBrushJScript.js', array('jquery', 'forgivingheals-shcore'));
				$this->add_script('forgivingheals-brush-php', TEMPLATEURL . '/js/libs/syntaxhighlighter/shBrushPhp.js', array('jquery', 'forgivingheals-shcore'));
				$this->add_script('forgivingheals-brush-css', TEMPLATEURL . '/js/libs/syntaxhighlighter/shBrushCss.js', array('jquery', 'forgivingheals-shcore'));
				$this->add_script('single-story', 	TEMPLATEURL . '/js/single-story.js', array('jquery', 'underscore', 'backbone', 'site-functions','site-front'));
			}

			if(is_page_template( 'page-intro.php' )){
				$this->add_script('intro', 		TEMPLATEURL . '/js/intro.js', array('jquery', 'underscore', 'backbone', 'site-functions', 'site-front'));
			}

			if(is_author()){
				$this->add_existed_script('plupload_all');
				$this->add_script('profile', 		TEMPLATEURL . '/js/profile.js', array('jquery', 'underscore', 'backbone', 'site-functions', 'site-front'));
			}
			if( is_page_template( 'page-pending.php' ) ){
				$this->add_script('pending', 		TEMPLATEURL . '/js/pending.js', array('jquery', 'underscore', 'backbone', 'site-functions', 'site-front'));
			}
		}
	}

	public function on_add_styles(){
		parent::on_add_styles();

		$this->add_style( 'bootstrap'		, TEMPLATEURL.'/css/libs/bootstrap.min.css' );
		$this->add_style( 'font-awesome'	, TEMPLATEURL.'/css/libs/font-awesome.min.css' );

		if(et_load_mobile()){
			return;
		} else {
			$this->add_style( 'main-style'		, TEMPLATEURL.'/css/main.css',array('bootstrap') );
			$this->add_style( 'editor-style'	, TEMPLATEURL.'/css/editor.css' );
			$this->add_style( 'push-menu'		, TEMPLATEURL.'/css/libs/push-menu.css' );
			$this->add_style( 'chosen'			, TEMPLATEURL.'/css/libs/chosen.css' );
			$this->add_style( 'custom-style'	, TEMPLATEURL.'/css/custom.css' );

			if(is_singular( 'story' ))
				$this->add_style('forgivingheals-shstyle', TEMPLATEURL . '/css/shCoreDefault.css');

			$this->add_style( 'style' 			, get_stylesheet_uri() );
		}

		do_action('forgivingheals_after_print_styles');
	}

	/**
	 * Add new plugin for TinyMCE
	 */
	public function tinymce_add_plugins($plugin_array){
		$forgivinghealsimage    = TEMPLATEURL . '/js/plugins/feimage/editor_plugin_src.js';
		$autoresize = TEMPLATEURL . '/js/plugins/autoresize/editor_plugin.js';
		$autolink   = TEMPLATEURL . '/js/plugins/autolink/plugin.min.js';
		$forgivinghealscode     = TEMPLATEURL . '/js/plugins/fecode/editor_plugin.js';

		$plugin_array['forgivinghealsimage']    = $forgivinghealsimage;
		$plugin_array['forgivinghealscode']     = $forgivinghealscode;
		$plugin_array['autoresize'] = $autoresize;
		$plugin_array['autolink']   = $autolink;

	    return $plugin_array;
	}
}

/**
 * Handle post data
 */
class ForgivingHeals_FrontPost extends AE_Base{

	public function __construct(){
		$this->add_action('template_redirect', 'handle_posts');
	}

	public function handle_posts(){
		global $current_user;
		/**
		*
		* - PREVENT USERS ACCESS TO PENDING PAGE EXCEPT ADMIN
		* -
		* - @package ForgivingHeals
		* - @version 1.0
		*
		**/
		if( is_page_template( 'page-pending.php' ) && !current_user_can( 'manage_options' ) ){
			wp_redirect( home_url() );
			exit();
		}
		/**
		*
		* - PREVENT USERS ACCESS TO CONTENT PAGE IF OPTION IS ACTIVE
		* -
		* - @package ForgivingHeals
		* - @version 1.0
		*
		**/
		if( ae_get_option("login_view_content") ){
			//var_dump(!is_page_template( 'page-intro.php' ) && !is_user_logged_in());
			if( !is_page()  && !is_singular( 'post' ) && !is_user_logged_in() ){
				wp_redirect( et_get_page_link('intro') );
				exit();
			}
		}

		/**
		*
		* - REDIRECT USERS TO STORIES LIST PAGE IF ALREADY LOGGED IN
		* -
		* - @package ForgivingHeals
		* - @version 1.0
		*
		**/

		if(is_page_template( 'page-intro.php' )){
			if(is_user_logged_in()){
				wp_redirect( get_post_type_archive_link( 'story' ) );
				exit();
			}
		}

		/**
		*
		* - REDIRECT TO SEARCH PAGE
		* -
		* - @package ForgivingHeals
		* - @version 1.0
		*
		**/
		if ( isset($_REQUEST['keyword']) ){
			$keyword = str_replace('.php', ' php', $_REQUEST['keyword']);
			$link = forgivingheals_search_link( esc_attr( $keyword ) );
			wp_redirect( $link );
			exit;
		}
		/**
		*
		* - COUNT STORY VIEW
		* -
		* - @package ForgivingHeals
		* - @version 1.0
		*
		**/
	    if( is_singular( 'story' )) {
	        global $post,$user_ID;

	        if( ae_get_option("login_view_content") && get_user_meta( $user_ID, 'register_status', true ) == "unconfirm" ){
	        	wp_redirect( add_query_arg( array('confirm' => 0), home_url() ) );
	        	exit();
	        }

	        if($post->post_status == 'publish') {

	            $views  =   (int) ForgivingHeals_Stories::get_field($post->ID, 'et_view_count');
	            $key    =   "et_post_".$post->ID."_viewed";

	            if(!isset($_COOKIE[$key]) ||  $_COOKIE[$key] != 'on') {
	                ForgivingHeals_Stories::update_field($post->ID, 'et_view_count', $views + 1 ) ;
	                setcookie($key, 'on', time()+3600, "/");
	            }
	        }
	    }
		/**
		*
		* - INSERT A STORY
		* - @param string $post_title
		* - @param string $post_content
		* - @param string $story_category
		* - @package ForgivingHeals
		* - @version 1.0
		*
		**/
		if ( isset($_POST['forgivingheals_nonce']) && wp_verify_nonce( $_POST['forgivingheals_nonce'], 'insert_story' ) ){
			global $current_user;

			$cats = array(
				'forgivingheals_tag' 			=> $_POST['tags'],
				'story_category' => $_POST['story_category']
			);

			$result = ForgivingHeals_Stories::insert_story($_POST['post_title'],$_POST['post_content'],$cats);

			do_action( 'forgivingheals_insert_story', $result );

			if(!is_wp_error( $result )){
				wp_redirect( get_permalink( $result ) );
				exit;
			}
		}
		/**
		*
		* - INSERT A COMMENT TO STORY
		* - @param int $post_id
		* - @param array $author_data
		* - @param array $comment_data
		* - @package ForgivingHeals
		* - @version 1.0
		*
		**/
		if ( isset($_POST['forgivingheals_nonce']) && wp_verify_nonce( $_POST['forgivingheals_nonce'], 'insert_comment') ){
			global $current_user;
			$result = ForgivingHeals_Comments::insert( array(
					'comment_post_ID' => $_POST['comment_post_ID'],
					'comment_content' => $_POST['post_content'],
				));

			do_action( 'forgivingheals_insert_comment', $result );

			if(!is_wp_error( $result )){
				wp_redirect( et_get_last_page( $_POST['comment_post_ID'] ) );
				exit;
			}
		}
		/**
		 * Confirm User
		 */
		if(isset($_GET['act']) && $_GET['act'] == "confirm" && $_GET['key'] ){
			$user = get_users(array( 'meta_key' => 'key_confirm', 'meta_value' => $_GET['key'] ));
			global $forgivingheals_confirm;
			$forgivingheals_confirm = update_user_meta( $user[0]->ID, 'register_status', '' );

			$user_email		=	$user[0]->user_email;

			$message		=	ae_get_option('confirmed_mail_template');

			$message	=	et_filter_authentication_placeholder ( $message, $user[0]->ID );
			$subject	=	__("Congratulations! Your account has been confirmed successfully.",ET_DOMAIN);

			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
			$headers .= "From: ".get_option('blogname')." < ".get_option('admin_email') ."> \r\n";

			if($forgivingheals_confirm && $user_email)
				wp_mail($user_email, $subject , $message, $headers) ;
		}
	}
}


class ForgivingHeals_Ajax extends AE_Base{

	public function __construct(){
		$this->add_ajax('et_post_sync'			  , 'sync_post');
		$this->add_ajax('et_upload_images'		  , 'upload_images', true, true);
		$this->add_ajax('et_get_nonce'			  , 'get_nonce', true, false);
		$this->add_ajax('et_search'				  , 'search_stories');
	}

	public function get_nonce(){
		global $user_ID;
		if($user_ID){
			$resp = array(
				'success' 	=> true,
				'msg' => 'success',
				'data' => array(
					'ins' => wp_create_nonce( 'insert_story' ),
					'up'  => wp_create_nonce( 'et_upload_images' ),
					)
				);
		} else {
			$resp = array(
				'success' 	=> false,
				'msg' 		=> 'failed'
			);
		}
		wp_send_json( $resp );
	}

	public function sync_post(){
		$method = $_POST['method'];

		switch ($method) {

			case 'report':
				$resp = $this->report();
				break;

			case 'create':
				$resp = $this->create();
				break;

			case 'update':
				$resp = $this->update_post();
				break;

			case 'remove':
				$resp = $this->delete();
				break;

			default:
				# code...
				break;
		}

		wp_send_json( $resp );

	}

	public function create(){
		try{

			$args = $_POST['content'];
			global $current_user;

			if( !is_user_logged_in() )
				throw new Exception(__("You must log in to post story.", ET_DOMAIN));

			if( isset($args['post_title']) && $args['post_title'] != strip_tags($args['post_title']) )
				throw new Exception(__("Post title should not contain any HTML Tag.", ET_DOMAIN));

			if( isset($args['forgivingheals_nonce']) && wp_verify_nonce( $args['forgivingheals_nonce'], 'insert_comment' )) {

				if(!forgivingheals_user_can('add_comment'))
					throw new Exception(__("You don't have enough point to add a comment.", ET_DOMAIN));

				$args['comment_content']      = $args['post_content'];
				$args['comment_author']       = $current_user->user_login;
				$args['comment_author_email'] = $current_user->user_email;

				$result 	= ForgivingHeals_Comments::insert($args);
				$comment  	= ForgivingHeals_Comments::convert(get_comment($result));

				if(is_wp_error( $result )){
					$resp = array(
						'success' 	=> false,
						'msg' 		=> __('An error occur when created comment.',ET_DOMAIN)
					);
				} else {
					$resp = array(
						'success' 	=> true,
						'msg' 		=> __('Comment has been created successfully.',ET_DOMAIN),
						'data'		=> $comment
					);
				}

			} elseif (isset($args['forgivingheals_nonce']) && wp_verify_nonce( $args['forgivingheals_nonce'], 'insert_reaction' )){

				$result 	= ForgivingHeals_Reactions::insert_reaction($args['post_parent'], $args['post_content']);
							  ForgivingHeals_Reactions::update_field($result, "et_vote_count", 0);
				$reaction  	= ForgivingHeals_Reactions::convert(get_post($result));

				if(is_wp_error( $result )){
					$resp = array(
						'success' 	=> false,
						'msg' 		=> __('An error occur when created reaction.',ET_DOMAIN)
					);
				} else {
					$msg = ae_get_option('pending_reactions') && !(current_user_can( 'manage_options' ) || forgivingheals_user_can( 'approve_reaction' )) ? __('Your reaction has been created successfully and need to be approved by Admin before displayed!', ET_DOMAIN) : __('Reaction has been created successfully.', ET_DOMAIN);
					$resp = array(
						'success' 	=> true,
						'redirect'	=> get_permalink($reaction->post_parent),
						'msg' 		=> $msg,
						'data'		=> $reaction
					);
				}

			} elseif (isset($args['forgivingheals_nonce']) && wp_verify_nonce( $args['forgivingheals_nonce'], 'insert_story' )){

				$cats = array(
					'forgivingheals_tag' 			=> isset($args['tags']) ? $args['tags'] : array(),
					'story_category' => $args['story_category']
				);

				$status = ae_get_option("pending_stories") && !current_user_can( 'manage_options' ) ? "pending" : "publish";

				$result = ForgivingHeals_Stories::insert_story($args['post_title'], $args['post_content'], $cats, $status);
						  ForgivingHeals_Stories::update_field($result, "et_vote_count", 0);
						  ForgivingHeals_Stories::update_field($result, "et_reactions_count", 0);
				$post 	= ForgivingHeals_Stories::convert(get_post($result));

				$msg 	= ae_get_option("pending_stories") && !current_user_can( 'manage_options' ) ? __('Your story has been created successfully. It\'ll appear right after being approved by admin.',ET_DOMAIN) : __('Story has been created successfully.',ET_DOMAIN);
				$redirect = ae_get_option("pending_stories") && !current_user_can( 'manage_options' ) ? home_url() : get_permalink( $result );

				if(is_wp_error( $result )){
					$resp = array(
						'success' 	=> false,
						'msg' 		=> __('An error occur when created story.',ET_DOMAIN)
					);
				} else {
					$resp = array(
						'success' 	=> true,
						'redirect'	=> $redirect,
						'msg' 		=> $msg,
						'data'		=> $post
					);
				}

			}else {
				throw new Exception("Error Processing Request", 1);

			}

		} catch (Exception $e) {
			$resp = array(
				'success' 	=> false,
				'msg' 		=> $e->getMessage()
			);
		}
		return $resp;
	}

	public function update_post(){
		try {
			global $current_user;

			if(!isset($_POST['do_action'])) {
				throw new Exception(__("Invalid action", ET_DOMAIN));
			}

			if( isset($_POST['content']['post_title']) && $_POST['content']['post_title'] != strip_tags($_POST['content']['post_title']) ){
				throw new Exception(__("Post title should not contain any HTML Tag.", ET_DOMAIN));
			}

			$action	=	$_POST['do_action'];
			switch ( $action ) {
				case 'vote_down':
					if(!forgivingheals_user_can('vote_down')) throw new Exception(__("You don't have enough point to vote up.", ET_DOMAIN));
				case 'vote_up':
					if(!forgivingheals_user_can('vote_up')) throw new Exception(__("You don't have enough point to vote down.", ET_DOMAIN));

					ForgivingHeals_Stories::vote( $_POST['ID'], $action );
					$post = ForgivingHeals_Stories::convert(get_post( $_POST['ID'] ));
					$resp = array(
						'success' 	=> true,
						'data' 		=> $post
					);
					break;

				case 'accept-reaction':
				case 'un-accept-reaction':
					$story = get_post( $_POST['post_parent'] );
					$reactionID  = $action == "accept-reaction" ? $_POST['ID'] : 0;

					if( $current_user->ID != $story->post_author ){
						throw new Exception(__("Only story author can mark reacted.", ET_DOMAIN));
						return false;
					}

					ForgivingHeals_Stories::mark_reaction( $_POST['post_parent'], $reactionID );

					do_action( 'forgivingheals_accept_reaction', $reactionID, $action );

					$resp = array(
						'success' 	=> true
					);
					break;

				case 'saveComment':
					$data = array();
					$data['comment_ID'] 	 = $_POST['comment_ID'];
					$data['comment_content'] = $_POST['comment_content'];

					$success = ForgivingHeals_Comments::update($data);
					$comment = ForgivingHeals_Comments::convert(get_comment( $_POST['comment_ID'] ));

					$resp = array(
						'success' 	=> true,
						'data' 		=> $comment
					);
					break;
				case 'savePost':

					$data                 = array();
					$data['ID']           = $_POST['ID'];
					$data['post_content'] = $_POST['post_content'];
					$data['post_author']  = $_POST['post_author'];

					$success = ForgivingHeals_Reactions::update($data);
					$post    = ForgivingHeals_Reactions::convert(get_post( $_POST['ID'] ));

					if( $success &&  !is_wp_error( $success ) ) {

						$resp = array(
							'success' 	=> true,
							'data' 		=> $post
						);
					}else {
						$resp = array(
							'success' 	=> false,
							'data' 		=> $post,
							'msg'		=> $success->get_error_message()
						);
					}

					break;

				case 'saveStory':

					$data                = $_POST['content'];
					$data['ID']          = $_POST['ID'];
					$data['forgivingheals_tag']      = isset($data['tags']) ? $data['tags'] : array() ;
					$data['post_author'] = $_POST['post_author'];
					unset($data['tags']);

					$success = ForgivingHeals_Stories::update($data);

					$post    = ForgivingHeals_Stories::convert(get_post( $_POST['ID'] ));

					if( $success &&  !is_wp_error( $success ) ) {
						$resp = array(
							'success'  => true,
							'data'     => $post,
							'msg'      => __('Story has been saved successfully.', ET_DOMAIN),
							'redirect' => get_permalink( $_POST['ID'] )
						);
					}else {
						$resp = array(
							'success' 	=> false,
							'data' 		=> $post,
							'msg'		=> $success->get_error_message()
						);
					}

					break;
				case 'approve':

					$id      = $_POST['ID'];
					$success = ForgivingHeals_Stories::change_status($id, "publish");
					$post    = ForgivingHeals_Stories::convert(get_post( $id ));
					$point   = forgivingheals_get_badge_point();
					//store story id to data for send mail
					ForgivingHeals::forgivingheals_stories_new_reaction($id);

					if( $success &&  !is_wp_error( $success ) ) {
						if($post->post_type == "story"){
							//add points to user
							if( !empty( $point->create_story ) ) forgivingheals_update_user_point( $post->post_author, $point->create_story );
							// set transient for new story
							set_transient( 'forgivingheals_notify_' . mt_rand( 100000, 999999 ), array(
								'title'   =>		__( 'New Story', ET_DOMAIN ),
								'content' =>	 	__( "There's a new post, why don't you give a look at", ET_DOMAIN ) .
								' <a href ="' . get_permalink( $id ) . '">' . get_the_title( $id ) . '</a>',
								'type'    =>		'update',
								'user'    =>	md5( $current_user->user_login )
							), 20 );

							$resp = array(
								'success' 	=> true,
								'data' 		=> $post,
								'msg'		=> __("You've just successfully approved a story.", ET_DOMAIN),
								'redirect'	=> get_permalink( $id )
							);
						} else if($post->post_type == "reaction"){
							//add point to user
							if( !empty( $point->post_reaction ) ) forgivingheals_update_user_point( $post->post_author, $point->post_reaction );
							$resp = array(
								'success' 	=> true,
								'data' 		=> $post,
								'msg'		=> __("You've just successfully approved an reaction.", ET_DOMAIN),
								'redirect'	=> get_permalink( $id )
							);
						}

					} else {
						$resp = array(
							'success' 	=> false,
							'data' 		=> $post,
							'msg'		=> $success->get_error_message()
						);
					}

					break;
				case 'follow':
				case 'unfollow':
					if ( !$current_user->ID ){
						throw new Exception(__('Login required', ET_DOMAIN));
					}

					$result = ForgivingHeals_Stories::toggle_follow($_POST['ID'], $current_user->ID);

					if (!is_array($result))
						throw new Exception(__('Error occurred', ET_DOMAIN));

					if(in_array($current_user->ID, $result)){
						$msg = __( 'You have started following this story.', ET_DOMAIN );
					} else {
						$msg = __( 'You have stopped following this story.', ET_DOMAIN );
					}

					$resp = array(
						'success' 	=> true,
						'msg' => $msg,
						'data' 		=> array(
							'isFollow' 	=> in_array($current_user->ID, $result),
							'following' => $result
						)
					);
					break;
				case 'report':
					$id = $_POST['ID'];
					if(!isset($_POST) || !$id){
						throw new Exception(__("Invalid post", ET_DOMAIN));
					}
					else{
						$fl = $this->report($id, $_POST['data']['message']);
						if($fl){
							$resp = array(
								'success' 	=> true,
								'msg' 		=> __("You have reported successfully", ET_DOMAIN)
							);
						}
						else{
							$resp = array(
								'success' 	=> false,
								'msg' 		=> __("Error when reported!", ET_DOMAIN)
							);
						}
					}
					break;
				default:
					throw new Exception(__("Invalid action", ET_DOMAIN));
					break;
			}

		} catch (Exception $e) {
			$resp = array(
				'success' 	=> false,
				'msg' 		=> $e->getMessage()
			);
		}
		return $resp;
	}

	/**
	 * Upload Images via TinyMCE
	 */
	public function upload_images(){
		try{
			if ( !check_ajax_referer( 'et_upload_images', '_ajax_nonce', false ) ){
				throw new Exception( __('Security error!', ET_DOMAIN ) );
			}

			// check fileID
			if(!isset($_POST['fileID']) || empty($_POST['fileID']) ){
				throw new Exception( __('Missing image ID', ET_DOMAIN ) );
			}
			else {
				$fileID	= $_POST["fileID"];
			}

			if(!isset($_FILES[$fileID])){
				throw new Exception( __('Uploaded file not found',ET_DOMAIN) );
			}

			if($_FILES[$fileID]['size'] > 1024*1024){
				throw new Exception( __('Image file size is too big.Size must be less than < 1MB.',ET_DOMAIN) );
			}

			// handle file upload
			$attach_id = et_process_file_upload( $_FILES[$fileID], 0 , 0, array());

			if ( is_wp_error($attach_id) ){
				throw new Exception( $attach_id->get_error_message() );
			}

			$image_link = wp_get_attachment_image_src( $attach_id , 'full');

			// no errors happened, return success response
			$res	= array(
				'success'	=> true,
				'msg'		=> __('The file was uploaded successfully', ET_DOMAIN),
				'data'		=> $image_link[0]
			);
		}
		catch(Exception $e){
			$res	= array(
				'success'	=> false,
				'msg'		=> $e->getMessage()
			);
		}
		wp_send_json( $res );
	}

	/**
	 * Report a story or reaction
	 */
	public function report($id, $report_data){
		global $current_user;
		// required logged in
		if ( !$current_user->ID || !$id  || !$report_data) return false;

		$post_type = get_post_type($id);
		$post      = get_post($id);
		$report_tx = '';
		if(!empty($post_type)){
			$report_tx = 'report-'.$post_type;
		}
		switch ($post_type) {
			case 'story':
				$title     = $post->post_title;
				$link      = $post->guid;
				$report_tx = $report_tx;
				break;
			case 'reaction':
				$thread    = get_post($post->post_parent);
				$title     = $thread->post_title;
				$link      = $thread->guid;
				$report_tx = $report_tx;
				break;
			default:
				$report_tx = 'untaxonomy';
				break;
		}

		$content = '<p>Post Content:</p>
					<p>'.$post->post_content.'</p>
					<p>|----------------------------------------------|</p>
					<p>Message:</p>
					<p>'.$report_data.'</p>';

		$my_post = array(
			  'post_title'    => 'REPORT#',
			  'post_content'  => $content,
			  'post_status'   => 'publish',
			  'post_author'   => $current_user->ID,
			  'post_type'     => 'report',
			);

		// Insert the post into the database
		$users_report = (array)get_post_meta( $id, 'et_users_report',true);
		if(!in_array($current_user->ID,$users_report)){
			$result = wp_insert_post( $my_post );
			$m_post = array(
					'ID'         => $result,
					'post_title' => '[REPORT#'.$result.']'.$title
				 );
			wp_update_post($m_post);
			wp_set_object_terms( $result, $report_tx, 'report-taxonomy' );
			if($result){
				array_push($users_report, $current_user->ID);
				update_post_meta( $id, 'et_users_report', $users_report );
				update_post_meta( $result, '_link_report', $link );
			}
			do_action('et_after_reported', $id, $report_data);
		} else {
			$result = false;
		}

		if($result)
			return true;
		else
			return false;
	}
	public function delete(){
		try {

			if ( empty($_POST['ID']) && empty($_POST['comment_ID']) ) throw new Exception( __('Error occurred', ET_DOMAIN) );

			if( isset($_POST['do_action']) && $_POST['do_action'] == "deleteComment"){

				$msg  = __( "Comment deleted successfully!", ET_DOMAIN );
				$post = get_comment($_POST['ID']);
				wp_delete_comment($_POST['comment_ID']);

			} else {

				if( isset($_POST['post_type']) && $_POST['post_type'] == "reaction") {

					$msg  = __( "Reaction deleted successfully!", ET_DOMAIN );
					$post = get_post($_POST['ID']);
					ForgivingHeals_Reactions::delete($_POST['ID']);

				} else {

					$msg  = __( "Story deleted successfully!", ET_DOMAIN );
					$post = get_post($_POST['ID']);
					ForgivingHeals_Stories::delete($_POST['ID']);
				}
			}

			$resp = array(
				'success' 	=> true,
				'msg' 		=> $msg,
				'redirect'		=> get_post_type_archive_link( 'story' ),
				'data' 		=> $post
			);

		} catch (Exception $e) {
			$resp = array(
				'success' => false,
				'msg' => $e->getMessage()
			);
		}
		return $resp;
	}

	/**
	 * AJAX search stories by keyword (next version)
	 *
	 */
	public function search_stories(){
		try {
			$query = ForgivingHeals_Stories::search($_POST['content']);
			$data  = array();
			foreach ($query->posts as $post) {
				$story            = ForgivingHeals_Stories::convert($post);
				$story->et_avatar = ForgivingHeals_Member::get_avatar_urls($post->post_author, 30);
				$story->permalink = get_permalink( $post->ID );

				$data[] = $story;
			}

			$resp = array(
				'success' 	=> true,
				'msg' 		=> '',
				'data' 		=> array(
					'stories' 	=> $data,
					'total' 		=> $query->found_posts,
					'count' 		=> $query->post_count,
					'pages' 		=> $query->max_num_pages,
					'search_link' 	=> forgivingheals_search_link( $_POST['content']['s'] ),
					'search_term' 	=> $_POST['content']['s'],
					'test' 			=> $query
				)
			);
		} catch (Exception $e) {
			$resp = array(
				'success' => false,
				'msg' 	=> $e->getMessage()
			);
		}
		wp_send_json($resp);
	}

} // end class ET_ForumAjax

?>