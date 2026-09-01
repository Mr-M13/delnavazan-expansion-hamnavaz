<?php
/**
 * Hamnavaz teacher post type.
 *
 * @package DelnavazanExpansion
 */

namespace Delnavazan\Expansion\Hamnavaz;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the private, administrator-managed teacher content model.
 */
final class Teacher_Post_Type {

	/**
	 * Teacher post type key.
	 *
	 * @var string
	 */
	const POST_TYPE = 'hamnavaz_teacher';

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_post_type' ), 5 );
		add_filter( 'enter_title_here', array( $this, 'filter_title_placeholder' ), 10, 2 );
	}

	/**
	 * Clarify the core title mapping on teacher edit screens.
	 *
	 * @param string   $title Placeholder text.
	 * @param \WP_Post $post  Current post.
	 * @return string
	 */
	public function filter_title_placeholder( $title, $post ) {
		if ( $post instanceof \WP_Post && self::POST_TYPE === $post->post_type ) {
			return esc_html__( 'Enter Persian name', 'delnavazan-expansion' );
		}

		return $title;
	}

	/**
	 * Register the non-public teacher post type.
	 *
	 * @return void
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => esc_html__( 'Teachers', 'delnavazan-expansion' ),
			'singular_name'         => esc_html__( 'Teacher', 'delnavazan-expansion' ),
			'menu_name'             => esc_html__( 'Teachers', 'delnavazan-expansion' ),
			'name_admin_bar'        => esc_html__( 'Teacher', 'delnavazan-expansion' ),
			'add_new'               => esc_html__( 'Add New', 'delnavazan-expansion' ),
			'add_new_item'          => esc_html__( 'Add New Teacher', 'delnavazan-expansion' ),
			'new_item'              => esc_html__( 'New Teacher', 'delnavazan-expansion' ),
			'edit_item'             => esc_html__( 'Edit Teacher', 'delnavazan-expansion' ),
			'view_item'             => esc_html__( 'View Teacher', 'delnavazan-expansion' ),
			'all_items'             => esc_html__( 'All Teachers', 'delnavazan-expansion' ),
			'search_items'          => esc_html__( 'Search Teachers', 'delnavazan-expansion' ),
			'parent_item_colon'     => esc_html__( 'Parent Teacher:', 'delnavazan-expansion' ),
			'not_found'             => esc_html__( 'No teachers found.', 'delnavazan-expansion' ),
			'not_found_in_trash'    => esc_html__( 'No teachers found in Trash.', 'delnavazan-expansion' ),
			'featured_image'        => esc_html__( 'Profile Photograph', 'delnavazan-expansion' ),
			'set_featured_image'    => esc_html__( 'Set profile photograph', 'delnavazan-expansion' ),
			'remove_featured_image' => esc_html__( 'Remove profile photograph', 'delnavazan-expansion' ),
			'use_featured_image'    => esc_html__( 'Use as profile photograph', 'delnavazan-expansion' ),
			'archives'              => esc_html__( 'Teacher Archives', 'delnavazan-expansion' ),
			'attributes'            => esc_html__( 'Teacher Attributes', 'delnavazan-expansion' ),
			'insert_into_item'      => esc_html__( 'Insert into teacher', 'delnavazan-expansion' ),
			'uploaded_to_this_item' => esc_html__( 'Uploaded to this teacher', 'delnavazan-expansion' ),
			'filter_items_list'     => esc_html__( 'Filter teachers list', 'delnavazan-expansion' ),
			'items_list_navigation' => esc_html__( 'Teachers list navigation', 'delnavazan-expansion' ),
			'items_list'            => esc_html__( 'Teachers list', 'delnavazan-expansion' ),
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => $labels,
				'description'         => esc_html__( 'Structured Hamnavaz teacher records.', 'delnavazan-expansion' ),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => Hamnavaz_Module::ADMIN_SLUG,
				'show_in_admin_bar'   => false,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => false,
				'menu_icon'           => 'dashicons-groups',
				'capability_type'     => 'page',
				'map_meta_cap'        => true,
				'hierarchical'        => false,
				'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'can_export'          => true,
				'delete_with_user'    => false,
			)
		);
	}
}
