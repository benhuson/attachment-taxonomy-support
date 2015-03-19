<?php

class AttachmentTaxSupp_Walker_Category_Checklist extends Walker {
	var $tree_type = 'category';
	var $attachment_id = 0;
	var $db_fields = array(
		'parent' => 'parent',
		'id' => 'term_id'
	); //TODO: decouple this
	
	function start_lvl( &$output, $depth, $args ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "$indent<ul class='children'>\n";
	}
	
	function end_lvl( &$output, $depth, $args ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "$indent</ul>\n";
	}
	
	function start_el( &$output, $category, $depth, $args ) {
		global $attachmenttaxsupp_admin_attachment_id;
		extract( $args );
		if ( empty( $taxonomy ) )
			$taxonomy = 'category';
		
		if ( $taxonomy == 'category' )
			$name = 'post_category';
		else
			$name = 'tax_input['.$taxonomy.']';
		$class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
		$output .= "\n<li id='{$this->attachment_id}-{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->name . '" type="checkbox" name="attachments[' . $attachmenttaxsupp_admin_attachment_id . '][' . $taxonomy . '][]" id="in-'.$this->attachment_id.'-'.$taxonomy.'-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters('the_category', $category->name )) . '</label>';
	}
	
	function end_el( &$output, $category, $depth, $args ) {
		$output .= "</li>\n";
	}
}

class AttachmentTaxSupp_Admin {
	
	/**
	 * Configure Admin
	 */
	function AttachmentTaxSupp_Admin() {
		add_filter( 'attachment_fields_to_edit', array( $this, 'modal_attachment_fields_to_edit' ), null, 2 );
		add_filter( 'attachment_fields_to_save', array( $this, 'attachment_fields_to_save' ), null, 2 );
		add_filter( 'get_edit_term_link', array( $this, 'get_edit_term_link' ), 10, 4 );
		add_filter( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 20 );
	}
	
	/**
	 * Admin Enqueue Scripts
	 */
	function admin_enqueue_scripts() {
		global $AttachmentTaxSupp, $current_screen;
		if ( is_admin() ) {
			wp_enqueue_style( 'attachmenttaxsupp-media-modal', plugins_url( dirname( $AttachmentTaxSupp->plugin_basename ) . '/admin/css/media-modal.css' ) );
			//wp_enqueue_script( 'attachmenttaxsupp-media-modal', plugins_url( dirname( $AttachmentTaxSupp->plugin_basename ) . '/admin/js/media-modal.js' ), array( 'jquery' ) );
			//wp_localize_script( 'attachmenttaxsupp-media-modal', 'AttachmentTaxSupp_Media_Modal', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		}
		if ( ( $current_screen->id == 'media' ) || ( isset( $_GET['taxonomy'] ) && isset( $_GET['post_type'] ) && 'attachment' == $_GET['post_type'] ) ) {
			//wp_enqueue_script( 'media_taxonomies', plugins_url( dirname( $AttachmentTaxSupp->plugin_basename ) . '/admin/js/admin.js' ), array( 'jquery', 'suggest', 'post' ) );
		}
	}

	/**
	 * Get Edit Term Link Filter
	 * Ensure that we add a post_type=attachment arg to the edit URLs
	 * when needed to we can track the state for admin menus etc.
	 *
	 * @param string $location Edit link.
	 * @param string $term_id Term.
	 * @param string $taxonomy Taxonomy.
	 * @param string $object_type Post type.
	 * @return Edit link.
	 */
	function get_edit_term_link( $location, $term_id, $taxonomy, $object_type ) {
		$tax = get_taxonomy( $taxonomy );
		if ( ! in_array( $object_type, $tax->object_type ) ) {
			if ( isset( $_GET['post_type'] ) && 'attachment' == $_GET['post_type'] && in_array( 'attachment', $tax->object_type ) ) {
				$object_type = 'attachment';
			} else {
				$object_type = $tax->object_type[0];
			}
			return add_query_arg( 'post_type', $object_type, $location );
		}
		return $location;
	}
	
	/**
	 * Media Taxonomy Page
	 * This is just a plceholder function
	 * as we actually redirect here instead.
	 */
	function media_taxonomy_edit_page() {
	}

	/**
	 * Modal Edit Image Fields
	 *
	 * @param   array   $form_fields  Form fields array.
	 * @param   object  $post         Post.
	 * @return  array                 Form fields.
	 */
	function modal_attachment_fields_to_edit( $form_fields, $post ) {
		foreach ( $form_fields as $key => $val ) {
			if ( isset( $val['taxonomy'] ) && $val['taxonomy'] && taxonomy_exists( $val['name'] ) ) {
				if ( isset( $val['hierarchical'] ) && $val['hierarchical'] == true ) {
					$form_fields[ $key ] = $this->modal_attachment_fields_to_edit_hierarchical( $val, $post );
				}
			}
		}

		// Add taxonomy checkbox nonce field
		$form_fields['nonce_attachmenttaxsupp'] = array(
			'value' => wp_create_nonce( 'update_attachment' ),
			'input' => 'hidden'
		);

		return $form_fields;
	}

	/**
	 * Modal Edit Image Fields Hierarchical
	 *
	 * @param   array   $form_fields  Form fields array.
	 * @param   object  $post         Post.
	 * @return  array                 Form fields.
	 */
	function modal_attachment_fields_to_edit_hierarchical( $field, $post ) {
		$n = $field['input'];
		$field['input'] = 'taxonomy_checkboxes';
		$field['taxonomy_checkboxes'] = $n . print_r( ( $field['name'] ), true );
		$wp_terms_checklist_walker = new AttachmentTaxSupp_Walker_Category_Checklist;
		$checklist = $this->get_wp_terms_checklist( $post->ID, array(
			'checked_ontop' => false,
			'taxonomy'      => $field['name'],
			'walker'        => $wp_terms_checklist_walker
		) );

		$checklist = '';

		// Get all taxonomy terms
		$all_terms = get_terms( $field['name'], array(
			'hide_empty' => false
		) );

		// Get attachment terms
		$post_terms = wp_get_post_terms( $post->ID, $field['name'], array( 'fields' => 'ids' ) );

		// Create checkbox list
		if ( $all_terms && ! is_wp_error( $all_terms ) ) {
			foreach ( $all_terms as $term ) {
				$selected_term = in_array( $term->term_id, $post_terms ) ? $term->term_id : 0;
				$checklist .= sprintf( '<li id="%s-%s"><label class="selectit"><input value="%s" type="checkbox" name="attachments[%s][tax_%s][%s]" id="in-%s-%s"%s> %s</label></li>', $term->taxonomy, $term->term_id, esc_attr( $term->name ), $post->ID, $term->taxonomy, $term->term_id, $term->taxonomy, $term->term_id, checked( $term->term_id, $selected_term, false ), esc_html( $term->name ) );
			}
		}

		$field['taxonomy_checkboxes'] = '<ul class="taxonomy-checklist">' . $checklist . '</ul>';
		return $field;
	}

	/**
	 * Output an unordered list of checkbox <input> elements labelled
	 * with term names. Taxonomy independent version of wp_category_checklist().
	 * 
	 * @param   int     $post_id
	 * @param   array   $args
	 * @return  string
	 */
	function get_wp_terms_checklist( $post_id = 0, $args = array() ) {
		global $attachmenttaxsupp_admin_attachment_id;
		$attachmenttaxsupp_admin_attachment_id = $post_id;
		$checklist = '';
		ob_start();
		wp_terms_checklist( $post_id, $args );
		$checklist = ob_get_contents();
		ob_end_clean();
		return $checklist;
	}

	/**
	 * Edit Image Form
	 *
	 * @param array $form_fields Form fields array.
	 * @param object $post Post.
	 * @return array Form fields.
	 */
	function attachment_fields_to_edit( $form_fields, $post ) {
		global $AttachmentTaxSupp, $current_screen;
		foreach ( $form_fields as $key => $val ) {
			if ( isset( $val['taxonomy'] ) && $val['taxonomy'] ) {
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

						$html .= '
							</div>
							<p><a href="' . admin_url( '/edit-tags.php?taxonomy=' . $tax_name . '&post_type=attachment' ) . '">' . __( 'Manage', 'attachmenttaxsupp' ) . ' ' . $taxonomy->labels->name . '</a></p>
						</div>';
						$html .= wp_nonce_field( 'update_attachment', '_wpnonce_attachmenttaxsupp', true, false );
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
						$html .= '<p><a href="' . admin_url( '/edit-tags.php?taxonomy=' . $tax_name . '&post_type=attachment' ) . '">' . __( 'Manage', 'attachmenttaxsupp' ) . ' ' . $taxonomy->labels->name . '</a></p>';
						$html .= wp_nonce_field( 'update_attachment', '_wpnonce_attachmenttaxsupp', true, false );
						
						if ( 'async-upload' == $current_screen->id ) {
							$html .= '<script type="text/javascript">tagBox.init();</script>';
						}
						
						$form_fields[$key]['input'] = 'html';
						$form_fields[$key]['html'] = $html;
					}
				}
			}
		}
		return $form_fields;
	}

	/**
	 * Save Image Form
	 *
	 * @param   object  $post        Post.
	 * @param   object  $attachment  Attachment.
	 * @return  object               Post.
	 */
	function attachment_fields_to_save( $post, $attachment ) {

		// If no post variables, bail.
		if ( empty( $_POST ) ) {
			return $post;
		}

		// If attachment data is posted...
		if ( isset( $_POST['post_id'] ) && isset( $_POST['attachments'] ) && isset( $_POST['action'] ) && 'save-attachment-compat' == $_POST['action'] ) {

			// Loop through data for each attachment.
			foreach ( $_POST['attachments'] as $attachment_id => $attachment_data ) {

				// If attachment data passes nonce test...
				if ( wp_verify_nonce( $attachment_data['nonce_attachmenttaxsupp'], 'update_attachment' ) ) {

					// Get all public taxonomies with UI support.
					$taxes = get_taxonomies( array(
						'show_ui' => true,
						'_builtin' => false )
					);

					// Limit to attachment taxonomies.
					$taxes = array_intersect( $taxes, get_object_taxonomies( 'attachment' ) );

					// Save terms for each taxonomy.
					foreach ( $taxes as $tax ) {
						if ( isset( $attachment_data[ 'tax_' . $tax ] ) && is_array( $attachment_data[ 'tax_' . $tax ] ) ) {
							wp_set_object_terms( $post['ID'], array_keys( $attachment_data[ 'tax_' . $tax ] ), $tax );
						} else {
							wp_set_object_terms( $post['ID'], array(), $tax );
						}
					}

				}

			}

		}

		return $post;

	}

}
