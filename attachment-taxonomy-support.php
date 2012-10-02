<?php

/*
Plugin Name: Attachment Taxonomy Support
Plugin URI: https://github.com/benhuson/attachment-taxonomy-support
Description: Improved support for media and attachments in WordPress versions before WordPress 3.5+
Version: 1.2
Author: Ben Huson
Author URI: http://www.benhuson.co.uk/
License: GPLv2 or later
*/

class AttachmentTaxSupp {
	
	var $plugin_dir = '';
	var $plugin_basename = '';
	var $admin = null;
	
	/**
	 * Configure Plugin
	 */
	function AttachmentTaxSupp() {
		$this->plugin_dir = dirname( __FILE__ );
		$this->plugin_basename = plugin_basename( __FILE__ );
		add_action( 'init', array( $this, 'init_plugin' ), 5 );
	}
	
	/**
	 * Configure Plugin
	 */
	function init_plugin() {
		global $wp_version;
		load_plugin_textdomain( 'attachmenttaxsupp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		$this->setup_taxonomies();
		
		// Pre WordPress 3.5 admin compatibility
		// Currently there is no way to edit an image's taxonomies in the media popup n WordPress 3.5+
		if ( ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) && version_compare( $wp_version, '3.5.dev', '<' ) ) {
			require_once( $this->plugin_dir . '/admin/admin.php' );
			$this->admin = new AttachmentTaxSupp_Admin();
		}
	}
	
	/**
	 * Setup Taxonomies
	 * Creates 'attachment_tag' and 'attachment_category' taxonomies.
	 */
	function setup_taxonomies() {
		$attachment_taxonomies = array();
		$labels = array(
			'name'              => _x( 'Attachment Tags', 'taxonomy general name', 'attachmenttaxsupp' ),
			'singular_name'     => _x( 'Attachment Tag', 'taxonomy singular name', 'attachmenttaxsupp' ),
			'search_items'      =>  __( 'Search Attachment Tags', 'attachmenttaxsupp' ),
			'all_items'         => __( 'All Attachment Tags', 'attachmenttaxsupp' ),
			'parent_item'       => __( 'Parent Attachment Tag', 'attachmenttaxsupp' ),
			'parent_item_colon' => __( 'Parent Attachment Tag:', 'attachmenttaxsupp' ),
			'edit_item'         => __( 'Edit Attachment Tag', 'attachmenttaxsupp' ), 
			'update_item'       => __( 'Update Attachment Tag', 'attachmenttaxsupp' ),
			'add_new_item'      => __( 'Add New Attachment Tag', 'attachmenttaxsupp' ),
			'new_item_name'     => __( 'New Attachment Tag Name', 'attachmenttaxsupp' ),
			'menu_name'         => __( 'Attachment Tags', 'attachmenttaxsupp' ),
		);
		$args = array(
			'hierarchical' => false,
			'labels'       => $labels,
			'show_ui'      => true,
			'query_var'    => true,
			'rewrite'      => true,
		);
		$attachment_taxonomies[] = array(
			'taxonomy'  => 'attachment_tag',
			'post_type' => 'attachment',
			'args'      => $args
		);
		$labels = array(
			'name'              => _x( 'Attachment Categories', 'taxonomy general name', 'attachmenttaxsupp' ),
			'singular_name'     => _x( 'Attachment Category', 'taxonomy singular name', 'attachmenttaxsupp' ),
			'search_items'      => __( 'Search Attachment Categories', 'attachmenttaxsupp' ),
			'all_items'         => __( 'All Attachment Categories', 'attachmenttaxsupp' ),
			'parent_item'       => __( 'Parent Attachment Category', 'attachmenttaxsupp' ),
			'parent_item_colon' => __( 'Parent Attachment Category:', 'attachmenttaxsupp' ),
			'edit_item'         => __( 'Edit Attachment Category', 'attachmenttaxsupp' ), 
			'update_item'       => __( 'Update Attachment Category', 'attachmenttaxsupp' ),
			'add_new_item'      => __( 'Add New Attachment Category', 'attachmenttaxsupp' ),
			'new_item_name'     => __( 'New Attachment Category Name', 'attachmenttaxsupp' ),
			'menu_name'         => __( 'Attachment Category', 'attachmenttaxsupp' ),
		);
		$args = array(
			'hierarchical' => true,
			'labels'       => $labels,
			'show_ui'      => true,
			'query_var'    => true,
			'rewrite'      => true,
		);
		$attachment_taxonomies[] = array(
			'taxonomy'  => 'attachment_category',
			'post_type' => 'attachment',
			'args'      => $args
		);
		$attachment_taxonomies = apply_filters( 'attachmenttaxsupp_taxonomies', $attachment_taxonomies );
		foreach ( $attachment_taxonomies as $attachment_taxonomy ) {
			register_taxonomy( $attachment_taxonomy['taxonomy'], $attachment_taxonomy['post_type'], $attachment_taxonomy['args'] );
		}
	}
	
}

global $AttachmentTaxSupp;
$AttachmentTaxSupp = new AttachmentTaxSupp();
		
?>