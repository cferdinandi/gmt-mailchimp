<?php

	/**
	 * Add custom post type
	 */
	function mailchimp_add_custom_post_type() {

		$labels = array(
			'name'               => _x( 'Forms', 'post type general name', 'mailchimp' ),
			'singular_name'      => _x( 'Form', 'post type singular name', 'mailchimp' ),
			'add_new'            => _x( 'Add New', 'keel-pets', 'mailchimp' ),
			'add_new_item'       => __( 'Add New Form', 'mailchimp' ),
			'edit_item'          => __( 'Edit Form', 'mailchimp' ),
			'new_item'           => __( 'New Form', 'mailchimp' ),
			'all_items'          => __( 'All Forms', 'mailchimp' ),
			'view_item'          => __( 'View Form', 'mailchimp' ),
			'search_items'       => __( 'Search Forms', 'mailchimp' ),
			'not_found'          => __( 'No forms found', 'mailchimp' ),
			'not_found_in_trash' => __( 'No forms found in the Trash', 'mailchimp' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'MailChimp', 'mailchimp' ),
		);
		$args = array(
			'labels'        => $labels,
			'description'   => 'Holds our forms and form-specific data',
			'public'        => true,
			// 'menu_position' => 5,
			'menu_icon'     => 'dashicons-email',
			'hierarchical'  => false,
			'supports'      => array(
				'title',
				// 'editor',
				// 'thumbnail',
				// 'excerpt',
				'revisions',
				// 'page-attributes',
			),
			'has_archive'   => false,
			// 'rewrite' => array(
			// 	'slug' => 'courses',
			// ),
			// 'map_meta_cap'  => true,
			// 'capabilities' => array(
			// 	'create_posts' => false,
			// 	'edit_published_posts' => false,
			// 	'delete_posts' => false,
			// 	'delete_published_posts' => false,
			// )
		);
		register_post_type( 'gmt-mailchimp', $args );
	}
	add_action( 'init', 'mailchimp_add_custom_post_type' );