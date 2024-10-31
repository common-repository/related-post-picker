<?php
/*
Plugin Name: Related Post Picker
Plugin URI: http://www.thoughtlab.com/blog/index.php/related-posts-picker/
Description: When you create a post, you will now have the ability to choose from a list of auto-populated related posts.
Version: 1.1
Author: ThoughtLab
Author URI: http://www.thoughtlab.com
License: GPLv2
*/

/*
This program is free software; you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by 
the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful, 
but WITHOUT ANY WARRANTY; without even the implied warranty of 
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
GNU General Public License for more details. 

You should have received a copy of the GNU General Public License 
along with this program; if not, write to the Free Software 
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*/

// backwards compatible
add_action('admin_init', 'rpp_add_custom_box', 1);

/* Do something with the data entered */
add_action('save_post', 'rpp_save_postdata');

/* Adds a box to the main column on the Post and Page edit screens */
function rpp_add_custom_box() {
	add_meta_box( 'rel-post-div', 'Related Posts', 'rpp_inner_custom_box', 'post' );
}

/* Prints the box content */
function rpp_inner_custom_box() {
	// Use nonce for verification
	wp_nonce_field( plugin_basename(__FILE__), 'rpp_noncename' );


	global $tags, $tag_query, $post;
	$all_tags =get_the_tags();
	$i=0; 
	if ($all_tags) {
		foreach($all_tags as $key=> $tag) {
			$tags[$i] = $tag->name;
			$i++;                   
		}
	}
	$numposts = get_option('rpp_posts');
	if ($numposts){
		if($numposts > 0){
			$numberposts = $numposts;
		} else{
			$numberposts = '';
		}
	}else{
		$numberposts = '5';
	}
	$numopts = get_option('rpp_options');
	if ($numopts){
		if($numopts > 0){
			$numberopts = $numopts;
		} else{
			$numberopts = '';
		}
	}else{
		$numberopts = '25';
	}
	if($tags) $related_tags = implode(',', $tags);
	$tag_query = 'tag='.$related_tags.'&numberposts='.$numberopts.'&exclude='.$post->ID;
	$related_posts = get_posts($tag_query);
	
	?>
<style>
#rel-post-div li{
	padding-left:20px;
	text-indent:-20px;
}
</style>
	<ul>
	<?php
	$saved_related_posts = explode(':',get_post_meta($post->ID,'related_posts',true));
	foreach ($related_posts as $p)
	{?>
		<li><input type="checkbox" name="related_posts[]" value="<?php echo $p->ID; ?>"<?php 
			if (!($saved_related_posts) || in_array($p->ID,$saved_related_posts)){ 
				echo ' checked="checked"';
			}
		?>/>&nbsp;<?php echo $p->post_title; ?></li>
<?php } ?>
	</ul><?php /*
	<fieldset>
		<legend>Default Settings</legend>
		<p>You must update or publish this post to apply changes.</p>*/ ?>
		<hr/>
		<label for="default_options_num"><strong>Number of Options</strong><br/>
		<sub>The maximum number of related post options to choose from in the area above.</sub></label><br/>
		<select id="default_options_num" name="default_options_num">
			<option<?php if($numberopts == 5){ echo ' selected="selected"';} ?>>5</option>
			<option<?php if($numberopts == 10){ echo ' selected="selected"';} ?>>10</option>
			<option<?php if($numberopts == 25){ echo ' selected="selected"';} ?>>25</option>
			<option<?php if($numberopts == 50){ echo ' selected="selected"';} ?>>50</option>
			<option value="0"<?php if($numberopts == 0){ echo ' selected="selected"';} ?>>Unlimited</option>
		</select><br/><br/>
		<label for="default_posts_num"><strong>Number of Posts</strong><br/>
		<sub>The number of related posts that will show with the post if none are selected.</sub></label><br/>
		<select id="default_posts_num" name="default_posts_num">
			<option<?php if($numberposts == 5){ echo ' selected="selected"';} ?>>5</option>
			<option<?php if($numberposts == 10){ echo ' selected="selected"';} ?>>10</option>
			<option<?php if($numberposts == 25){ echo ' selected="selected"';} ?>>25</option>
			<option<?php if($numberposts == 50){ echo ' selected="selected"';} ?>>50</option>
			<option value="0"<?php if($numberposts == 0){ echo ' selected="selected"';} ?>>Unlimited</option>
		</select>
	<?php /*</fieldset>*/ ?>
<?php }

/* When the post is saved, saves our custom data */
function rpp_save_postdata( $post_id ) {

	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times

	if ( !wp_verify_nonce( $_POST['rpp_noncename'], plugin_basename(__FILE__) )) {
		return $post_id;
	}

	// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
	// to do anything
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
		return $post_id;

	
	// Check permissions
	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
			return $post_id;
	} else {
		if ( !current_user_can( 'edit_post', $post_id ) )
			return $post_id;
	}

	// OK, we're authenticated: we need to find and save the data
	if(is_array($_POST['related_posts'])){
		$related_posts = implode(':',$_POST['related_posts']);
	}else{
		$related_posts = '';
	}
	update_post_meta($post_id,'related_posts', $related_posts);
	update_option('rpp_options',$_POST['default_options_num']);
	update_option('rpp_posts',$_POST['default_posts_num']);

	// Do something with $mydata 
	// probably using add_post_meta(), update_post_meta(), or 
	// a custom table (see Further Reading section below)

	return $related_posts;
}

function rpp_related_posts(){
	global $post;
	$saved_related_posts = get_post_meta($post->ID,'related_posts',true);
if ($saved_related_posts && ($saved_related_posts != '')){
	$ids = str_replace(':', ',', $saved_related_posts);
	$related_posts = get_posts('include='.$ids.'');
} else{
	global $tags, $tag_query;
	$all_tags =get_the_tags();
	$i=0; 
	if ($all_tags) {
		foreach($all_tags as $key=> $tag) {
			$tags[$i] = $tag->name;
			$i++;                   
		}
	}
	if($tags) $related_tags = implode(',', $tags);
	$num = get_option('default_posts_num');
if ($num){
	if($num > 0){
		$numberposts = $num;
	} else{
		$numberposts = '';
	}
}else{
	$numberposts = '5';
}
	$tag_query = 'tag='.$related_tags.'&numberposts='.$numberposts.'&exclude='.$post->ID;
	$related_posts = get_posts($tag_query);
}
		echo '<div id="rpp_related">
	<h3>Related Posts</h3>
	<ul>
	';
		foreach ($related_posts as $p){
			echo '	<li><a href="'.get_permalink($p->ID).'" rel="bookmark">'.$p->post_title.'</a></li>
	';
		}
		echo '</ul>
</div>';
}