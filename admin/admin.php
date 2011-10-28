<?php

class CSSTheme_Admin {
	
	/**
	 * Configure Admin
	 */
	function CSSTheme_Admin() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
	}
	
	/**
	 * Add Meta Boxes
	 */
	function add_meta_boxes() {
		$themes = $this->theme_array();
		if ( count( $themes ) == 0 ) 
			return;
		$post_types = $this->get_supported_post_types();
		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'css_theme',
				apply_filters( 'css_theme_title', 'CSS Theme' ),
				array( $this, 'css_theme_meta_box' ),
				$post_type,
				'side'
			);
		}
	}
	
	/**
	 * Save Property
	 * Updates the property meta data when the post is saved.
	 */
	function save_post( $post_id ) {
		global $CSSTheme;
		
		// Verify if this is an auto save routine. 
		// If it is our form has not been submitted, so we dont want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return;
		
		// Verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( !isset( $_POST['css_theme_noncename'] ) || !wp_verify_nonce( $_POST['css_theme_noncename'], $CSSTheme->plugin_basename ) )
			return;
		
		// Check edit capability
		$abort = true;
		$post_types = $this->get_supported_post_types();
		$post_types_obj = (array) get_post_types( array(
			'public'   => true,
			'_builtin' => false
		), 'objects' );
		if ( in_array( $_POST['post_type'], $post_types ) ) {
			if ( 'page' == $_POST['post_type'] && current_user_can( 'edit_page', $post_id ) )
				$abort = false;
			elseif ( 'post' == $_POST['post_type'] && current_user_can( 'edit_post', $post_id ) )
				$abort = false;
			elseif ( current_user_can( $post_types_obj[$_POST['post_type']]->cap->edit_post, $post_id ) )
				$abort = false;
		}
		if ( $abort )
			return $post_id;
		
		update_post_meta( $post_id, '_css_theme', $_POST['css_theme'] );
		
		return $_POST['css_theme'];
	}
	
	/**
	 * Product Details Meta Box
	 */
	function css_theme_meta_box( $post ) {
		global $CSSTheme;
		$themes = $this->theme_array();
		$css_theme = get_post_meta( $post->ID, '_css_theme', true );
		
		$selected_css_theme = array();
		foreach ( $themes as $key => $val ) {
			$selected_css_theme[$val] = '';
		}
		$selected_css_theme[$css_theme] = ' selected="selected"';
		
		// Use nonce for verification
		wp_nonce_field( $CSSTheme->plugin_basename, 'css_theme_noncename' );
		
		echo '<select name="css_theme" style="width:100%;">';
		foreach ( $themes as $key => $val ) {
			echo '<option value="' . $val . '"' . $selected_css_theme[$val] . '>' . $key . '</option>';
		}
		echo '</select>';
	}
	
	function theme_array() {
		$themes = array();
		return apply_filters( 'css_theme_array', $themes );
	}
	
	/**
	 * Get Supported Post Types
	 */
	function get_supported_post_types() {
		$args = array(
			'public'   => true,
			'_builtin' => false
		);
		$post_types = (array) get_post_types( $args );
		$post_types[] = 'post';
		$post_types[] = 'page';
		$supported = array();
		foreach ( $post_types as $post_type ) {
			if ( post_type_supports( $post_type, 'css_theme' ) )
				$supported[] = $post_type;
		}
		return $supported;
	}
	
}

?>