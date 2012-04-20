<?php

/*
Plugin Name: Attachment Taxonomy Support
Plugin URI: https://github.com/benhuson/attachment-taxonomy-support
Description: Improved taxonomy support for media and attachments.
Version: 1.1
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
		load_plugin_textdomain( 'attachmenttaxsupp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		$this->setup_taxonomies();
		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
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
			'name'              => _x( 'Attachment Categories', 'taxonomy general name' ),
			'singular_name'     => _x( 'Attachment Category', 'taxonomy singular name' ),
			'search_items'      =>  __( 'Search Attachment Categories' ),
			'all_items'         => __( 'All Attachment Categories' ),
			'parent_item'       => __( 'Parent Attachment Category' ),
			'parent_item_colon' => __( 'Parent Attachment Category:' ),
			'edit_item'         => __( 'Edit Attachment Category' ), 
			'update_item'       => __( 'Update Attachment Category' ),
			'add_new_item'      => __( 'Add New Attachment Category' ),
			'new_item_name'     => __( 'New Attachment Category Name' ),
			'menu_name'         => __( 'Attachment Category' ),
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