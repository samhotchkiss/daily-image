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


<?php if ( !$unattached ) : ?>
<p>You don't have any unscheduled uploaded images!</p>
<p>Maybe you should try out the <a href="https://apps.wordpress.com/lightroom/" target="_blank">WordPress plugin for Lightroom</a>?</p>
<?php else : ?>
<form class="" action="<?php echo admin_url( 'edit.php?page=daily_image&postit' ); ?>" onsubmit="return confirm('Are you sure you\'re ready to schedule these posts?');" method="post">

	<ul id="di_list">
			<?php foreach ( $unattached as $u ): ?>
				<li id="di_image_<?php echo $u['id'] ?>">
					<div class="draghandle">
						&#9776;
					</div>
					<div class="publish_date">
						&nbsp;
					</div>
					<input type="hidden" name="image[<?php echo $u['id'] ?>][date]" value="" class="publish_date_field" />
					<a href="<?php echo admin_url( 'post.php?post=' . $u['id'] . '&action=edit' ) ?>" target="_blank" class="thumbholder">
						<img src="<?php echo $u['thumb'] ?>" class="di_thumb" width="150" height="150" /><br />View Full Image
					</a>
					<input type="text" name="image[<?php echo $u['id'] ?>][title]" value="<?php echo $u['title'] ?>" class="title"/>
					<textarea name="image[<?php echo $u['id'] ?>][body]" rows="8" cols="80"  placeholder="Post Body

You can use the [image] tag to place your image in a custom location, otherwise it will be placed at the beginning of your post." ><?php echo $u['caption'] ?></textarea>

					<input type="text" name="image[<?php echo $u['id'] ?>][tags]" value="" class="tags" placeholder="Tags" />

					<div class="tagcloud">
						<a id="tagcloud<?php echo $u['id'] ?>-post_tag" onclick="tagBox.get( 'tagcloud<?php echo $u['id'] ?>-post_tag' ); jQuery( this ).hide(); return false;">&nbsp;</a>
						<div class="loader">
							<img src="<?php echo plugins_url( 'ajax-loader.gif', __FILE__ ); ?>" />
						</div>
					</div>


					<a href="#" class="delete" onclick="di_delete_image(<?php echo $u['id'] ?>); return false;">x</a>
				</li>
			<?php endforeach; ?>
	</ul>

	<table class="form-table">
	<?php $default_date = di_get_next_unscheduled_date(); ?>
	<tbody>
		<tr>
			<th scope="row">
				Which days should have posts?
			</th>
			<td>
				<table class="di_dow_table">
					<tr>
						<td>Sunday</td>
						<td>Monday</td>
						<td>Tuesday</td>
						<td>Wednesday</td>
						<td>Thursday</td>
						<td>Friday</td>
						<td>Saturday</td>
					</tr>
					<tr>
						<td><input type="checkbox" name="dow_0" id="di_dow_0" value="1" class="dow_checkbox" checked="checked" /></td>
						<td><input type="checkbox" name="dow_1" id="di_dow_1" value="1" class="dow_checkbox" checked="checked" /></td>
						<td><input type="checkbox" name="dow_2" id="di_dow_2" value="1" class="dow_checkbox" checked="checked" /></td>
						<td><input type="checkbox" name="dow_3" id="di_dow_3" value="1" class="dow_checkbox" checked="checked" /></td>
						<td><input type="checkbox" name="dow_4" id="di_dow_4" value="1" class="dow_checkbox" checked="checked" /></td>
						<td><input type="checkbox" name="dow_5" id="di_dow_5" value="1" class="dow_checkbox" checked="checked" /></td>
						<td><input type="checkbox" name="dow_6" id="di_dow_6" value="1" class="dow_checkbox" checked="checked" /></td>
					</tr>
				</table>
			</td>
		</tr>
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
				<input type="checkbox" name="set_featured_image" value="setit" checked="checked" /><br>
			</td>
		</tr>
	</tbody>
	</table>

	<input name="save" type="submit" class="button button-primary button-large" value="Schedule Posts" />
</form>

<script type="text/javascript">
	window.onload = function(){
		di_set_dates();

		jQuery( "ul#di_list li .tagcloud a" ).each( function( index, el ) { jQuery( el ).click(); });

		jQuery('#di_list').sortable({ update: function( event, ui ) { di_set_dates(); } });
		jQuery('#di_start_date').datepicker();
		jQuery('#di_start_date').change(function(){ di_set_dates(); });
		jQuery('.dow_checkbox').change(function(){ di_set_dates(); });

		setTimeout( function(){ di_setup_tagclouds(); }, 3500);


		jQuery( "ul#di_list li .tags" ).each( function( index, el ) {
			jQuery( el ).wpTagsSuggest();
		});


	};

	function di_setup_tagclouds()
	{
		jQuery( ".the-tagcloud a" ).click( function() {
			var taga = jQuery(this);
			var tag_text = taga.text();
			var tag_input = taga.parents("li").find('input.tags');
			var current_tags = tag_input.val();
			tag_input.val( current_tags + tag_text + ", " );
		} );
		jQuery(".tagcloud div").hide();
		jQuery(".tagcloud p").show();
	}

	function di_delete_image( id ) {
		jQuery( '#di_image_' + id ).remove();
		di_set_dates();
		var data = {
			'action': 'di_remove_image',
			'attachment_id': id
		};
		jQuery.post(ajaxurl, data, function(response) {});
	}
	function di_set_dates() {

		if( jQuery('input.dow_checkbox:checked').length == 0 ) {
			alert( 'You must check at least one day a week.' );
			jQuery('div.publish_date').html('Invalid');
			return;
		}

		var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];

		var postdate = jQuery( '#di_start_date' ).val();
		var d = new Date( postdate );

		jQuery( "ul#di_list li" ).each( function( index, el ) {
			var dow = d.getDay();
			while( jQuery( '#di_dow_' + dow ).is(":checked") == false ) {
				d = addDays( d, 1 );
				dow = d.getDay();
			}
			jQuery( el ).find('div.publish_date').html( monthNames[d.getMonth()] + " " + d.getDate() );
			jQuery( el ).find('input.publish_date_field').val( monthNames[d.getMonth()] + " " + d.getDate() + ", " + d.getFullYear() );
			d = addDays( d, 1 );
		});

	}

	function addDays(date, days) {
		var result = new Date(date);
		result.setDate(result.getDate() + days);
		return result;
	}
</script>

<?php endif; ?>

</div><!-- .wrap -->
