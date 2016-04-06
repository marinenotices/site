<?php
	$post 		= false;
	$post_id 	= -1;
	$featured_img_html = '';
	if( isset($_GET['fep_id']) && isset($_GET['fep_action']) && $_GET['fep_action'] == 'edit' ){
		$post_id 			= (int)$_GET['fep_id'];
		$p 					= get_post($post_id, 'ARRAY_A');
		if($p['post_author'] != $current_user->ID) return 'You don\'t have permission to edit this notice';
		$category 			= get_the_terms($post_id, 'notice_category');
		$tags 				= wp_get_post_tags( $post_id, array( 'fields' => 'names' ) );
		$featured_img 		= get_post_thumbnail_id( $post_id );
		$featured_img_html 	= (!empty($featured_img))?wp_get_attachment_image( $featured_img, array(200,200) ):'';
        $postMeta = get_post_meta($post_id, "marinenotice-locations");
        if ($postMeta != "") {
            $locations = unserialize($postMeta[0]);
		} else {
            $locations = "null";
        }

		$post 				= array(
								'title' 			=> $p['post_title'],
								'content' 			=> $p['post_content'],
								'about_the_author' 	=> get_post_meta($post_id, 'about_the_author', true)
							);

		if(isset($category[0]) && is_array($category))
			$post['category'] 	= $category[0]->term_id;
		if(isset($tags) && is_array($tags))
			$post['tags'] 		= implode(', ', $tags);
	}
?>
<noscript><div id="no-js" class="warning">This form needs JavaScript to function properly. Please turn on JavaScript and try again!</div></noscript>
<div id="fep-new-post">
	<div id="fep-message" class="warning"></div>
	<form id="fep-submission-form">
		<label for="fep-post-title">Title</label>
        <p>Enter a title for the Notice.</p>
		<input type="text" name="post_title" id="fep-post-title" value="<?php echo ($post) ? $post['title']:''; ?>"><br/>
		<label for="fep-post-content">Content</label>
        <p>Enter a description of the Notice.  Include as much detail as possible, although it is not necessary to include the latitude and longitude as you can enter this via the map below.</p>
		<?php
			$enable_media = (isset($fep_roles['enable_media']) && $fep_roles['enable_media'])?current_user_can($fep_roles['enable_media']):1;
			wp_editor( $post['content'], 'fep-post-content', $settings = array('textarea_name'=>'post_content', 'textarea_rows'=> 7, 'media_buttons'=>false, 'quicktags'=>false, 'tinymce'=>array('toolbar1'=>'bold,italic,underline,|,bullist,numlist,|,link,unlink,|,undo,redo,|,pastetext')) );
			wp_nonce_field('fepnonce_action','fepnonce');
		?>
        <label for="nautical-map-container">Location</label>
        <p>Drag the pin to the location for the Notice, or enter the latitude and longitude manually below.</p>
        <?php echo do_shortcode('[navionics gmap="true" pin="true"]'); ?>
        <label for='marinenotice-location-lat-0'>Latitude</label> <input id='marinenotice-location-lat-0' name='marinenotice-location-lat-0' type='text' />
        <label for='marinenotice-location-long-0'>Longitude</label> <input id='marinenotice-location-long-0' name='marinenotice-location-long-0' type='text' /><br />
        <?php if (!is_null($locations)): ?>
            <?php $index = 1; ?>
            <?php foreach ($locations as $location): ?>
                <label for='marinenotice-location-lat-<?php echo $index; ?>'>Latitude</label> <input id='marinenotice-location-lat-<?php echo $index; ?>' name='marinenotice-location-lat-<?php echo $index; ?>' type='text' value="<?php echo $location['lat']; ?>"/><br />
                <label for='marinenotice-location-long-<?php echo $index; ?>'>Longitude</label> <input id='marinenotice-location-long-<?php echo $index; ?>' name='marinenotice-location-long-<?php echo $index; ?>' type='text' value="<?php echo $location['long']; ?>"/><br />
            <?php $index++; ?>
            <?php endforeach; ?>
        <?php endif; ?>
		<input type="hidden" name="about_the_author" id="fep-about" value="-1">
        <input type="hidden" name="fep-tags" id="fep-tags" value="">
		<label for="fep-category">Category</label>
        <p>Select an appropriate category for the Notice.</p>
		<?php wp_dropdown_categories(array('id'=>'fep-category', 'hide_empty' => 0, 'name' => 'post_category', 'orderby' => 'name', 'selected' => $post['category'], 'hierarchical' => true, 'taxonomy' => 'notice_category', 'show_option_none' => __('None'))); ?><br/>
		<div id="fep-featured-image">
			<div id="fep-featured-image-container"><?php echo $featured_img_html; ?></div>
			<a id="fep-featured-image-link" href="#">Choose Image (Optional)</a>
			<input type="hidden" id="fep-featured-image-id" value="<?php echo (!empty($featured_img))?$featured_img:'-1'; ?>"/>
		</div>
		<input type="hidden" name="post_id" id="fep-post-id" value="<?php echo $post_id ?>">
		<button type="button" id="fep-submit-post" class="active-btn">Submit</button><img class="fep-loading-img" src="<?php echo plugins_url( 'static/img/ajax-loading.gif', dirname(__FILE__) ); ?>"/>
    </form>
</div>