<?php

class AttachmentTaxSupp_Walker_Category_Checklist extends Walker {
	var $tree_type = 'category';
	var $attachment_id = 0;
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this
	
	function start_lvl(&$output, $depth, $args) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}
	
	function end_lvl(&$output, $depth, $args) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}
	
	function start_el(&$output, $category, $depth, $args) {
		extract($args);
		if ( empty($taxonomy) )
			$taxonomy = 'category';
		
		if ( $taxonomy == 'category' )
			$name = 'post_category';
		else
			$name = 'tax_input['.$taxonomy.']';
		
		$class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
		$output .= "\n<li id='{$this->attachment_id}-{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="'.$name.'['.$this->attachment_id.'][]" id="in-'.$this->attachment_id.'-'.$taxonomy.'-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters('the_category', $category->name )) . '</label>';
	}
	
	function end_el(&$output, $category, $depth, $args) {
		$output .= "</li>\n";
	}
}
  
class AttachmentTaxSupp_Admin {
	
	/**
	 * Configure Admin
	 */
	function AttachmentTaxSupp_Admin() {
		global $AttachmentTaxSupp;
		add_filter( 'attachment_fields_to_edit', array( $this, 'attachment_fields_to_edit' ), null, 2 );
		add_filter( 'attachment_fields_to_save', array( $this, 'attachment_fields_to_save' ), null, 2 );
		wp_enqueue_script( 'media_taxonomies', plugins_url( dirname( $AttachmentTaxSupp->plugin_basename ) . '/admin/js/admin.js' ), array( 'jquery', 'suggest', 'post' ) );
	}
	
	/**
	 * Edit Image Form
	 */
	function attachment_fields_to_edit( $form_fields, $post ) {
		foreach ( $form_fields as $key => $val ) {
			if ( isset( $val['hierarchical'] ) && taxonomy_exists( $val['name'] ) ) {
				$tax_name = esc_attr( $val['name'] );
				$taxonomy = get_taxonomy( $val['name'] );
				if ( $val['hierarchical'] == true) {
					//$popular_ids = wp_popular_terms_checklist( $tax_name );
					ob_start();
					//wp_terms_checklist( $post->ID, array( 'taxonomy' => $tax_name, 'popular_cats' => $popular_ids, 'walker' => new AttachmentTaxSupp_Walker_Category_Checklist ) );
					
					$wp_terms_checklist_walker = new AttachmentTaxSupp_Walker_Category_Checklist;
					$wp_terms_checklist_walker->attachment_id = $post->ID;
					wp_terms_checklist( $post->ID, array( 'taxonomy' => $tax_name, 'walker' => $wp_terms_checklist_walker ) );
					$checklist = ob_get_contents();
					ob_end_clean();
			
					$html = '<div class="inside">
						<div id="taxonomy-' . $tax_name . '" class="categorydiv">
							<ul id="' . $tax_name . '-tabs" class="category-tabs">
								<li class="tabs" style="display: inline;"><a href="#' . $tax_name . '-all" tabindex="3" style="text-decoration:none;">' . $taxonomy->labels->all_items . '</a></li>
								<!--<li class="hide-if-no-js" style="display: inline;"><a href="#' . $tax_name . '-pop" tabindex="3" style="text-decoration:none;">' . __( 'Most Used' ) . '</a></li>-->
							</ul>
							<div id="' . $tax_name . '-pop" class="tabs-panel" style="display: none;">
								<ul id="' . $tax_name . 'checklist-pop" class="categorychecklist form-no-clear" >
								</ul>
							</div>
							<div id="' . $tax_name . '-all" class="tabs-panel">
								<input type="hidden" name="' . $tax_name . '[]" value="0" />
								<ul id="' . $tax_name . 'checklist" class="list:' . $tax_name . ' categorychecklist form-no-clear">
									' . $checklist . '
								</ul>
							</div>';
					/*
					if ( current_user_can( $taxonomy->cap->edit_terms ) ) :
					$html .= '
							<div id="category-adder" class="wp-hidden-children">
								<h4>
									<a id="category-add-toggle" href="#category-add" class="hide-if-no-js" tabindex="3">+ Add New Category</a>
								</h4>
								<p id="category-add" class="category-add wp-hidden-child">
									<label class="screen-reader-text" for="newcategory">Add New Category</label>
									<input type="text" name="newcategory" id="newcategory" class="form-required form-input-tip" value="New Category Name" tabindex="3" aria-required="true">
									<label class="screen-reader-text" for="newcategory_parent">
										Parent Category:</label>
									<select name="newcategory_parent" id="newcategory_parent" class="postform" tabindex="3">
										<option value="-1">— Parent Category —</option>
										<option class="level-0" value="33">Temp</option>
										<option class="level-0" value="34">Temp 2</option>
										<option class="level-1" value="36">&nbsp;&nbsp;&nbsp;Temp 5</option>
										<option class="level-0" value="35">Temp 3</option>
										<option class="level-0" value="1">Uncategorized</option>
									</select>
									<input type="button" id="category-add-submit" class="add:categorychecklist:category-add button category-add-sumbit" value="Add New Category" tabindex="3">
									<input type="hidden" id="_ajax_nonce-add-category" name="_ajax_nonce-add-category" value="092592c9e4">
									<span id="category-ajax-response"></span>
								</p>
							</div>';
					endif;
					*/
					$html .= '
						</div>
						<p><a href="' . admin_url( '/edit-tags.php?taxonomy=' . $tax_name ) . '">Manage ' . $taxonomy->labels->name . '</a></p>
					</div>';
					$form_fields[$key]['input'] = 'html';
					$form_fields[$key]['html'] = $html;
				} else {
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
					$html .= '<p><a href="' . admin_url( '/edit-tags.php?taxonomy=' . $tax_name ) . '">Manage ' . $taxonomy->labels->name . '</a></p>';
					$form_fields[$key]['input'] = 'html';
					$form_fields[$key]['html'] = $html;
				}
			}
		}
		return $form_fields;
	}
	
	/**
	 * Save Image Form
	 * @todo If some checkboxes are checked and you uncheck all on a media page, it doesn't save
	 */
	function attachment_fields_to_save( $post, $attachment ) {
		if ( isset( $_POST['tax_input'] ) && is_array( $_POST['tax_input'] ) ) {
			foreach ( $_POST['tax_input'] as $tax => $arr ) {
				if ( taxonomy_exists( $tax ) ) {
					$val = null;
					if ( isset( $arr[$post['ID']] ) )
						$val = array_map( 'absint', $arr[$post['ID']] );
					wp_set_object_terms( $post['ID'], $val, $tax );
				}
			}
		}
		return $post;
	}
	
}

?>