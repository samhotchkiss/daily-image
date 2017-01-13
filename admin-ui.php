<?php

	$unattached = di_get_unattached_and_lonely();

?>
<div class="wrap">


	<?php if( isset( $_GET['success'] ) ) : ?>
		<div id="message" class="updated notice notice-success is-dismissible">
			<p>Successfully scheduled <?php echo $_GET['success'] ?> beautiful pictures. <a href="<?php echo admin_url( '/edit.php?post_status=future&post_type=post' ); ?>">View scheduled posts</a></p>
		</div>
	<?php endif; ?>
	<h2><?php esc_html_e( 'Daily Image' ); ?></h2>



<form class="" action="<?php echo admin_url( 'edit.php?page=daily_image&postit' ); ?>" method="post">

	<ul id="di_list">
		<?php if ( $unattached ): ?>
			<?php foreach ( $unattached as $u ): ?>
				<li id="di_image_<?php echo $u['id'] ?>">
					<a href="<?php echo admin_url( 'post.php?post=' . $u['id'] . '&action=edit' ) ?>" target="_blank" class="thumbholder">
						<img src="<?php echo $u['thumb'] ?>" class="di_thumb" width="150" height="150" /><br />View Full Image
					</a>
					<input type="text" name="image[<?php echo $u['id'] ?>][title]" value="<?php echo $u['title'] ?>" class="title"/>
					<textarea name="image[<?php echo $u['id'] ?>][body]" rows="8" cols="80"  placeholder="Post Body

You can use the [image] tag to place your image in a custom location, otherwise it will be placed at the beginning of your post." ><?php echo $u['caption'] ?></textarea>

					<input type="text" name="image[<?php echo $u['id'] ?>][tags]" value="" class="tags" placeholder="Tags" />

					<a href="#" class="delete" onclick="di_delete_image(<?php echo $u['id'] ?>); return false;">x</a>
				</li>
			<?php endforeach; ?>
		<?php endif; ?>
	</ul>

	<table class="form-table">
	<?php $default_date = di_get_next_unscheduled_date(); ?>
	<tbody>
		<tr>
			<th scope="row">
				Publish first image on...
			</th>
			<td>
				<input type="text" name="start_date" value="<?php echo $default_date['date'] ?>" id="di_start_date" /> <small>Defaults to one day after your last scheduled post. Don't break the chain!</small>
			</td>
		</tr>
		<tr>
			<th scope="row">
				Publish daily at...
			</th>
			<td>
				<input type="text" name="daily_time" value="<?php echo $default_date['time'] ?>" /> <small>Use 24 hour time like <code>14:15</code></small>
			</td>
		</tr>
		<tr>
			<th scope="row">
				Set as featured image...
			</th>
			<td>
				<input type="checkbox" name="set_featured_image" value="setit" checked="checked"><br>
			</td>
		</tr>
	</tbody>
	</table>

	<input name="save" type="submit" class="button button-primary button-large" value="Schedule Posts" />
</form>


</div><!-- .wrap -->

<script type="text/javascript">
	window.onload = function(){
		jQuery( '#di_list' ).sortable();
		jQuery( '#di_start_date' ).datepicker();
		jQuery( '.tags' ).wpTagsSuggest();
	};
	function di_delete_image( id ) {
		jQuery( '#di_image_' + id ).remove();
		var data = {
			'action': 'di_remove_image',
			'attachment_id': id
		};
		jQuery.post(ajaxurl, data, function(response) {});
	}
</script>
