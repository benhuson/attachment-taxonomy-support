<?php

/*
Plugin Name: Attachment Taxonomy Support
Plugin URI: https://github.com/benhuson/attachment-taxonomy-support
Description: Improved taxonomy support for media and attachments.
Version: 2.5.3
Author: Ben Huson
Author URI: http://www.benhuson.co.uk/
License: GPLv2 or later
*/

add_action( 'init', 'attachmenttaxsupp_init', 0 );
function attachmenttaxsupp_init() {

	$labels = array(
		'name'              => _x( 'Attachment Tags', 'taxonomy general name' ),
		'singular_name'     => _x( 'Attachment Tag', 'taxonomy singular name' ),
		'search_items'      =>  __( 'Search Attachment Tags' ),
		'all_items'         => __( 'All Attachment Tags' ),
		'parent_item'       => __( 'Parent Attachment Tag' ),
		'parent_item_colon' => __( 'Parent Attachment Tag:' ),
		'edit_item'         => __( 'Edit Attachment Tag' ), 
		'update_item'       => __( 'Update Attachment Tag' ),
		'add_new_item'      => __( 'Add New Attachment Tag' ),
		'new_item_name'     => __( 'New Attachment Tag Name' ),
		'menu_name'         => __( 'Attachment Tags' ),
	);
	register_taxonomy( 'attachment_tag', 'attachment', array(
		'hierarchical' => false,
		'labels'       => $labels,
		'show_ui'      => true,
		'query_var'    => true,
		'rewrite'      => true,
	) );
	
	if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		add_filter( 'attachment_fields_to_edit', 'attachmenttaxsupp_attachment_fields_to_edit', null, 2 );
		//add_filter( 'attachment_fields_to_save', 'attachmenttaxsupp_attachment_fields_to_save', null, 2 );
		wp_enqueue_script( 'media_taxonomies', plugins_url( '/attachment-taxonomy-support/admin/js/admin.js' ), array( 'jquery', 'suggest', 'post' ) );
	}
	
}



/**
 * Edit Image Form
 */
function attachmenttaxsupp_attachment_fields_to_edit( $form_fields, $post ) {
	foreach ( $form_fields as $key => $val ) {
		if ( isset( $val['hierarchical'] ) && taxonomy_exists( $val['name'] ) ) {
			$tax_name = esc_attr( $val['name'] );
			$taxonomy = get_taxonomy( $val['name'] );
			$disabled = !current_user_can( $taxonomy->cap->assign_terms ) ? ' disabled="disabled"' : '';
			$html = '<div class="inside">
				<div class="tagsdiv" id="' . $tax_name . '">
					<div class="jaxtag">
						<div class="nojs-tags hide-if-js">
							<p>' . $taxonomy->labels->add_or_remove_items . '</p>
							<textarea name="attachments[' . $post->ID . '][' . $tax_name . ']" rows="3" cols="20" class="the-tags" id="attachments[' . $post->ID . '][' . $tax_name . ']"' . $disabled . ' style="margin:0px;">' . get_terms_to_edit( $post->ID, $tax_name ) . '</textarea>
						</div>';
			if ( current_user_can( $taxonomy->cap->assign_terms ) ) :
				$html .= '<div class="ajaxtag hide-if-no-js">
							<label class="screen-reader-text" for="new-tag-' . $tax_name . '">' . $val['labels']->name . '</label>
							<div class="taghint screen-reader-text" style="">' . $taxonomy->labels->add_new_item . '</div>
							<p><input type="text" id="new-tag-' . $tax_name . '" name="newtag[' . $tax_name . ']" class="newtag form-input-tip" size="16" autocomplete="off" value="">
								<input type="button" class="button tagadd" value="' . esc_attr( 'Add' ) . '" tabindex="3"></p>
						</div>
						<p class="howto">' . esc_attr( $taxonomy->labels->separate_items_with_commas ) . '</p>
					</div>';
			endif;
			$html .= '<div class="tagchecklist"></div>';
			if ( current_user_can( $taxonomy->cap->assign_terms ) ) :
				$html .= '<p class="hide-if-no-js"><a href="#titlediv" class="tagcloud-link" id="link-' . $tax_name . '">' . $taxonomy->labels->choose_from_most_used . '</a></p>';
			endif;
			$html .= '</div>';
			$form_fields[$key]['input'] = 'html';
			$form_fields[$key]['html'] = $html;
		}
	}
	return $form_fields;
}

/**
 * Save Image Form
 */
function attachmenttaxsupp_attachment_fields_to_save( $post, $attachment ) {
	/*
	if ( isset( $attachment['cithumb'] ) ) {
		$cithumb = absint( trim( $attachment['cithumb'] ) );
		if ( $cithumb == 0 )
			$cithumb = '';
		update_post_meta( $post['ID'], '_cithumb', $cithumb );
	}
	*/
	return $post;
}

		
?>