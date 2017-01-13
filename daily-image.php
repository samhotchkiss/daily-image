<?php
/*
Plugin Name: Daily Image
Plugin URI: https://swh.me/
Description: A daily dashboard for your life.
Author: samhotchkiss
Author URI: https://sam.blog/
Version: 0.1
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


function callback_di_remove_image( $id ) {
	// TODO: noncing
	$id = $_POST[ 'attachment_id' ];
	if( !current_user_can( 'publish_posts' ) ) {
		wp_die();
	}

	add_post_meta( $id, 'di_ignored', 1 );

	wp_die();
}
add_action( "wp_ajax_di_remove_image", "callback_di_remove_image" );

add_action('admin_init', 'di_maybe_post_pics');
function di_maybe_post_pics()
{
	if( !isset( $_GET['postit'] ) || !isset( $_GET['page'] ) || $_GET['page'] != 'daily_image'|| !isset( $_POST['image'] )  )
	{
		return;
	}

	$starttime = strtotime( $_POST['start_date'] . ' ' . $_POST['daily_time'] );

	if( $starttime < time() ) {
		wp_die( 'Invalid start time: it must be in the future.' );
	}

	$success = 0;

	foreach( $_POST['image'] as $attachment_id => $data ) {

		$image_data = wp_get_attachment_image_src( $attachment_id, 'large' );

		$image = '<a href="' . wp_get_attachment_url( $attachment_id ) . '" rel="attachment wp-att-' . $attachment_id . '">';
		$image .= '<img src="' . $image_data[0] . '" alt="" width="' . $image_data[1] . '" height="' . $image_data[2] . '" class="size-large wp-image-' . $attachment_id . '" />';
		$image .= '</a>';

		if( stripos( $data['body'], '[image]' ) === false ) {
			$body = $image . $data['body'];
		} else {
			$body = str_ireplace( '[image]', $image, $data['body'] );
		}

		$publish_date = date( 'Y-m-d H:i:s', $starttime );
		$starttime = $starttime + DAY_IN_SECONDS;

		// Insert the post into the database
		$pid = wp_insert_post(
			array(
				'post_title'		=> wp_strip_all_tags( $data['title'] ),
				'post_content'		=> $body,
				'post_status'		=> 'future',
				'post_date'		=> $publish_date,
				'edit_date' 		=> 'true'
			)
		);

		if( $pid ) {
			wp_update_post( array(
					'ID' => $attachment_id,
					'post_parent' => $pid
				)
			);
			if( isset( $_POST['set_featured_image'] ) ) {
				set_post_thumbnail( $pid, $attachment_id );
			}
			$success++;
		}
	}

	wp_redirect( admin_url( 'tools.php?page=daily_image&success=' . $success ) );

}

add_action('admin_menu', 'di_setup_menu');
function di_setup_menu() {
	add_submenu_page( 'tools.php', 'Daily Image', 'Daily Image', 'publish_posts', 'daily_image', 'daily_image_admin_ui' );
}

function daily_image_admin_ui() {
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'daily-image', plugins_url( 'admin-ui.css', __FILE__ ) );
	require 'admin-ui.php';
}

function di_get_next_unscheduled_date()
{
	$args = array(
		'posts_per_page'   => 1,
		'post_type'        => 'post',
		'post_status'      => 'future',
	);
	$scheduled = get_posts( $args );

	if( $scheduled ) {
		$base_date = $scheduled[0]->post_date;
	} else {
		$base_date = current_time( 'mysql' );
	}

	$next_date = date( 'm/d/Y H:i', strtotime( $base_date ) + DAY_IN_SECONDS );

	$date_parts = explode( ' ', $next_date );


	return array( 'date' => $date_parts[0], 'time' => $date_parts[1] );
}

function di_get_unattached_and_lonely()
{
	$args = array(
		'posts_per_page'   => 250,
		'post_type'        => 'attachment',
		'post_parent'      => '0'
	);
	$unattached = get_posts( $args );
	$o = array();

	if ( $unattached ) {
		foreach ( $unattached as $u ) {
			$ignored = get_post_meta( $u->ID, 'di_ignored', true );
			if( $ignored ) {
				continue;
			}
			$o[] = array(
				'id'		=> $u->ID,
				'thumb' 	=> wp_get_attachment_thumb_url( $u->ID ),
				'title'	=> $u->post_title,
				'caption'	=> $u->post_excerpt
			);
		}
	}

	return $o;

}



if( isset( $_GET['di'] ) ) {
	$args = array(
		'posts_per_page'   => 200,
		'post_type'        => 'attachment',
		'post_parent'      => '0'
	);
	$posts_array = get_posts( $args );

	echo '<pre>';
	print_r( $posts_array );
	exit;
}
