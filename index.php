<?php
/*
Plugin Name: Comment Rating
Version: 1.0
Plugin URI: https://okantastan.com
Description: Comment rating plugin for task
Author: Okan Taştan
Author URI: https://okantastan.com
*/


// Add custom meta (ratings) fields to the default comment form
add_filter('comment_form_default_fields','custom_fields');
function custom_fields($fields) {
	$commenter = wp_get_current_commenter();
	$req = get_option( 'require_name_email' );
	$aria_req = ( $req ? " aria-required='true'" : '' );

	$fields[ 'author' ] = '<p class="comment-form-author">'.
		'<label for="author">' . __( 'Name' ) . '</label>'.
		( $req ? '<span class="required">*</span>' : '' ).
		'<input id="author" name="author" type="text" value="'. esc_attr( $commenter['comment_author'] ) . 
		'" size="30" tabindex="1"' . $aria_req . ' /></p>';
	
	$fields[ 'email' ] = '<p class="comment-form-email">'.
		'<label for="email">' . __( 'Email' ) . '</label>'.
		( $req ? '<span class="required">*</span>' : '' ).
		'<input id="email" name="email" type="text" value="'. esc_attr( $commenter['comment_author_email'] ) . 
		'" size="30"  tabindex="2"' . $aria_req . ' /></p>';
				
	$fields[ 'url' ] = '<p class="comment-form-url">'.
		'<label for="url">' . __( 'Website' ) . '</label>'.
		'<input id="url" name="url" type="text" value="'. esc_attr( $commenter['comment_author_url'] ) . 
		'" size="30"  tabindex="3" /></p>';

	return $fields;
}

// Add fields after default fields above the comment box, always visible
add_action( 'comment_form_logged_in_after', 'additional_fields' );
add_action( 'comment_form_after_fields', 'additional_fields' );

function additional_fields () {
	$plugin_url_path = WP_PLUGIN_URL;
	echo '<p class="comment-form-rating">'.
	'<label for="rating">'. __('Rating') . '<span class="required">*</span></label>
	<span class="commentratingbox" style="display:block;height:35px">';
	for( $i=1; $i <= 5; $i++ )
	{
		echo '<input type="radio" name="rating" class="rating" id="rating_'. $i .'" value="'. $i .'" style="display:none" />';
		echo '<img src="'. $plugin_url_path .'/Rating-Plugin/images/star.png" class="rating_img" id="rating_img_'. $i .'" style="float:left;height:35px;cursor:pointer" onclick="checked_rating('. $i .')">';
	}
	echo'</span></p>';
}

// Save the comment meta data along with comment
add_action( 'comment_post', 'save_comment_meta_data' );
function save_comment_meta_data( $comment_id ) {
	if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '') )
	$rating = wp_filter_nohtml_kses($_POST['rating']);
	add_comment_meta( $comment_id, 'rating', $rating );
	$comment_data = get_comment( $comment_id );
	$post_id = $comment_data->comment_post_ID;
	$post = get_post($post_id);
	$data = array(
		'post_id' 		=> $post_id,
		'post_title' 	=> $post->post_title,
		'rate' 			=> $_POST['rating'],
		'token'			=> 'DarbaGuru'
	);
	send_comment_rating($data);
}


// Add the filter to check if the comment meta data has been filled or not
add_filter( 'preprocess_comment', 'verify_comment_meta_data' );
function verify_comment_meta_data( $commentdata ) {
	if ( ! isset( $_POST['rating'] ) )
	wp_die( __( 'Error: You did not add your rating. Hit the BACK button of your Web browser and resubmit your comment with rating.' ) );
	return $commentdata;
}

//Add an edit option in comment edit screen  
add_action( 'add_meta_boxes_comment', 'extend_comment_add_meta_box' );
function extend_comment_add_meta_box() {
    add_meta_box( 'title', __( 'Comment Metadata - Extend Comment' ), 'extend_comment_meta_box', 'comment', 'normal', 'high' );
}
 
function extend_comment_meta_box ( $comment ) {
    $rating = get_comment_meta( $comment->comment_ID, 'rating', true );
    wp_nonce_field( 'extend_comment_update', 'extend_comment_update', false );
    ?>
    <p>
        <label for="rating"><?php _e( 'Rating: ' ); ?></label>
			<span class="commentratingbox">
			<?php for( $i=1; $i <= 5; $i++ ) {
				echo '<span class="commentrating"><input type="radio" name="rating" id="rating" value="'. $i .'"';
				if ( $rating == $i ) echo ' checked="checked"';
				echo ' />'. $i .' </span>'; 
				}
			?>
			</span>
    </p>
    <?php
}

// Update comment meta data from comment edit screen 
add_action( 'edit_comment', 'extend_comment_edit_metafields' );
function extend_comment_edit_metafields( $comment_id ) {
    if( ! isset( $_POST['extend_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_comment_update'], 'extend_comment_update' ) ) return;

	if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '') ):
		$rating = wp_filter_nohtml_kses($_POST['rating']);
		update_comment_meta( $comment_id, 'rating', $rating );
		$data = array(
			'post_id' 		=> get_the_ID(),
			'post_title' 	=> 'denemeee',
			'rate' 			=> $_POST['rating'],
			'token'			=> 'DarbaGuru'
		);
		send_comment_rating($data);
	else :
	delete_comment_meta( $comment_id, 'rating');
	endif;
	
}

add_filter( 'comment_text', 'modify_comment');
function modify_comment( $text )
{
	$plugin_url_path = WP_PLUGIN_URL;
	if( $commentrating = get_comment_meta( get_comment_ID(), 'rating', true ) )
	{
		echo '<div class="comment-rating" style="display:block;height:35px">';
		for($i = 1; $i <= $commentrating; $i++ )
		{
			echo '<img src="'. $plugin_url_path .'/Rating-Plugin/images/star.png" style="float:left;height:35px" />';
		}
		echo '</div>';
		return $text;		
	} else {
		return $text;		
	}	 
}

add_action('wp_enqueue_scripts', 'callback_for_setting_up_scripts');
function callback_for_setting_up_scripts() {
    //wp_register_style( 'rating-css', WP_PLUGIN_URL . '/css/Rating-Plugin/style.css' );
    wp_enqueue_script( 'rating-script', WP_PLUGIN_URL . '/Rating-Plugin/js/script.js', array( 'jquery' ) );
	wp_localize_script('rating-script', 'myScript', array(
		'pluginsUrl' => plugins_url(),
	));
}

/**
 * Send Comment Rating
 */
function send_comment_rating($data)
{
	$url = 'https://okantastan.com/rating-task/public/api/add_rating';
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$headers = array(
		"Content-Type: application/x-www-form-urlencoded",
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	$data = http_build_query($data);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	return curl_exec($curl);
	curl_close($curl);
}