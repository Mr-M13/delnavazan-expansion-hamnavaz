<?php
/**
 * Hamnavaz teacher taxonomies.
 *
 * @package DelnavazanExpansion
 */

namespace Delnavazan\Expansion\Hamnavaz;

defined( 'ABSPATH' ) || exit;

/**
 * Registers normalized teacher classification terms.
 */
final class Teacher_Taxonomies {

	/**
	 * Instrument taxonomy key.
	 *
	 * @var string
	 */
	const INSTRUMENT = 'hamnavaz_instrument';

	/**
	 * Country taxonomy key.
	 *
	 * @var string
	 */
	const COUNTRY = 'hamnavaz_country';

	/**
	 * Language taxonomy key.
	 *
	 * @var string
	 */
	const LANGUAGE = 'hamnavaz_language';

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_taxonomies' ), 6 );
	}

	/**
	 * Register normalized taxonomies used by teacher records.
	 *
	 * @return void
	 */
	public function register_taxonomies() {
		$this->register_taxonomy(
			self::INSTRUMENT,
			esc_html__( 'Instruments', 'delnavazan-expansion' ),
			esc_html__( 'Instrument', 'delnavazan-expansion' )
		);

		$this->register_taxonomy(
			self::COUNTRY,
			esc_html__( 'Countries', 'delnavazan-expansion' ),
			esc_html__( 'Country', 'delnavazan-expansion' )
		);

		$this->register_taxonomy(
			self::LANGUAGE,
			esc_html__( 'Languages', 'delnavazan-expansion' ),
			esc_html__( 'Language', 'delnavazan-expansion' )
		);
	}

	/**
	 * Register one private, admin-managed taxonomy.
	 *
	 * @param string $taxonomy Taxonomy key.
	 * @param string $plural   Plural label.
	 * @param string $singular Singular label.
	 * @return void
	 */
	private function register_taxonomy( $taxonomy, $plural, $singular ) {
		register_taxonomy(
			$taxonomy,
			Teacher_Post_Type::POST_TYPE,
			array(
				'labels'            => array(
					'name'                       => $plural,
					'singular_name'              => $singular,
					'search_items'               => sprintf(
						/* translators: %s: plural taxonomy label. */
						esc_html__( 'Search %s', 'delnavazan-expansion' ),
						$plural
					),
					'all_items'                  => sprintf(
						/* translators: %s: plural taxonomy label. */
						esc_html__( 'All %s', 'delnavazan-expansion' ),
						$plural
					),
					'edit_item'                  => sprintf(
						/* translators: %s: singular taxonomy label. */
						esc_html__( 'Edit %s', 'delnavazan-expansion' ),
						$singular
					),
					'update_item'                => sprintf(
						/* translators: %s: singular taxonomy label. */
						esc_html__( 'Update %s', 'delnavazan-expansion' ),
						$singular
					),
					'add_new_item'               => sprintf(
						/* translators: %s: singular taxonomy label. */
						esc_html__( 'Add New %s', 'delnavazan-expansion' ),
						$singular
					),
					'new_item_name'              => sprintf(
						/* translators: %s: singular taxonomy label. */
						esc_html__( 'New %s Name', 'delnavazan-expansion' ),
						$singular
					),
					'menu_name'                  => $plural,
					'not_found'                  => sprintf(
						/* translators: %s: plural taxonomy label. */
						esc_html__( 'No %s found.', 'delnavazan-expansion' ),
						strtolower( $plural )
					),
					'items_list_navigation'      => sprintf(
						/* translators: %s: plural taxonomy label. */
						esc_html__( '%s list navigation', 'delnavazan-expansion' ),
						$plural
					),
					'items_list'                 => sprintf(
						/* translators: %s: plural taxonomy label. */
						esc_html__( '%s list', 'delnavazan-expansion' ),
						$plural
					),
					'back_to_items'               => sprintf(
						/* translators: %s: plural taxonomy label. */
						esc_html__( '← Back to %s', 'delnavazan-expansion' ),
						$plural
					),
				),
				'public'            => false,
				'publicly_queryable' => false,
				'show_ui'           => true,
				'show_admin_column' => false,
				'show_in_nav_menus' => false,
				'show_tagcloud'     => false,
				'show_in_quick_edit' => true,
				'show_in_rest'      => false,
				'hierarchical'      => false,
				'capabilities'      => array(
					'manage_terms' => 'manage_categories',
					'edit_terms'   => 'manage_categories',
					'delete_terms' => 'manage_categories',
					'assign_terms' => 'edit_pages',
				),
				'rewrite'           => false,
				'query_var'         => false,
			)
		);
	}
}
