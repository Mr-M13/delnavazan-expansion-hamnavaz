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
	 * Country edit-form field name.
	 *
	 * @var string
	 */
	const COUNTRY_FIELD = '_hamnavaz_primary_country';

	/**
	 * Country nonce action.
	 *
	 * @var string
	 */
	const COUNTRY_NONCE_ACTION = 'hamnavaz_save_primary_country';

	/**
	 * Country nonce field name.
	 *
	 * @var string
	 */
	const COUNTRY_NONCE_NAME = '_hamnavaz_country_nonce';

	/**
	 * User-specific country validation notice prefix.
	 *
	 * @var string
	 */
	const COUNTRY_ERROR_TRANSIENT_PREFIX = 'hamnavaz_country_save_error_';

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
		add_action( 'save_post_' . Teacher_Post_Type::POST_TYPE, array( $this, 'save_primary_country' ), 10, 2 );
		add_action( 'set_object_terms', array( $this, 'enforce_single_country' ), 10, 6 );
		add_action( 'admin_notices', array( $this, 'render_country_notice' ) );
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
			esc_html__( 'Country', 'delnavazan-expansion' ),
			true
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
	 * @param bool   $single   Whether teachers may have only one term.
	 * @return void
	 */
	private function register_taxonomy( $taxonomy, $plural, $singular, $single = false ) {
		$args = array(
			'labels'             => array(
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
				'back_to_items'              => sprintf(
					/* translators: %s: plural taxonomy label. */
					esc_html__( '← Back to %s', 'delnavazan-expansion' ),
					$plural
				),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_admin_column'  => false,
			'show_in_nav_menus'  => false,
			'show_tagcloud'      => false,
			'show_in_quick_edit' => ! $single,
			'show_in_rest'       => false,
			'hierarchical'       => false,
			'capabilities'       => array(
				'manage_terms' => 'manage_categories',
				'edit_terms'   => 'manage_categories',
				'delete_terms' => 'manage_categories',
				'assign_terms' => 'edit_pages',
			),
			'rewrite'            => false,
			'query_var'          => false,
		);

		if ( $single ) {
			$args['meta_box_cb'] = array( $this, 'render_country_meta_box' );
		}

		register_taxonomy(
			$taxonomy,
			Teacher_Post_Type::POST_TYPE,
			$args
		);
	}

	/**
	 * Render a single-select country taxonomy control.
	 *
	 * @param \WP_Post $post Current teacher post.
	 * @return void
	 */
	public function render_country_meta_box( $post ) {
		$terms      = $this->get_assigned_countries( $post->ID );
		$selected   = ! empty( $terms ) ? (int) $terms[0]->term_id : 0;
		$taxonomy   = get_taxonomy( self::COUNTRY );
		$can_assign = $taxonomy && current_user_can( $taxonomy->cap->assign_terms );
		$countries  = get_terms(
			array(
				'taxonomy'   => self::COUNTRY,
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		if ( is_wp_error( $countries ) ) {
			$countries = array();
			$can_assign = false;
		}

		wp_nonce_field( self::COUNTRY_NONCE_ACTION, self::COUNTRY_NONCE_NAME );
		?>
		<label class="screen-reader-text" for="hamnavaz-primary-country"><?php echo esc_html__( 'Primary Country', 'delnavazan-expansion' ); ?></label>
		<select id="hamnavaz-primary-country" name="<?php echo esc_attr( self::COUNTRY_FIELD ); ?>" class="widefat" <?php disabled( ! $can_assign ); ?>>
			<option value="0"><?php echo esc_html__( 'Select a country', 'delnavazan-expansion' ); ?></option>
			<?php foreach ( $countries as $country ) : ?>
				<option value="<?php echo esc_attr( $country->term_id ); ?>" <?php selected( $selected, (int) $country->term_id ); ?>><?php echo esc_html( $country->name ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php echo esc_html__( 'Each teacher has one primary country for the Country → City → Instrument discovery path.', 'delnavazan-expansion' ); ?></p>
		<?php if ( count( $terms ) > 1 ) : ?>
			<p class="description"><strong><?php echo esc_html__( 'Multiple legacy country assignments were found. Choose the primary country; saving will remove the others.', 'delnavazan-expansion' ); ?></strong></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Save the selected primary country and collapse malformed legacy assignments.
	 *
	 * @param int      $post_id Teacher post ID.
	 * @param \WP_Post $post    Teacher post object.
	 * @return void
	 */
	public function save_primary_country( $post_id, $post ) {
		if ( ! $post instanceof \WP_Post || Teacher_Post_Type::POST_TYPE !== $post->post_type ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Preserve the invariant even for valid save paths that do not render this box.
		$this->collapse_country_assignments( $post_id );

		$taxonomy = get_taxonomy( self::COUNTRY );

		if (
			! current_user_can( 'edit_post', $post_id ) ||
			! $taxonomy ||
			! current_user_can( $taxonomy->cap->assign_terms )
		) {
			return;
		}

		if ( ! isset( $_POST[ self::COUNTRY_NONCE_NAME ] ) || ! is_scalar( $_POST[ self::COUNTRY_NONCE_NAME ] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST[ self::COUNTRY_NONCE_NAME ] ) );

		if ( ! wp_verify_nonce( $nonce, self::COUNTRY_NONCE_ACTION ) ) {
			return;
		}

		if ( ! isset( $_POST[ self::COUNTRY_FIELD ] ) || ! is_scalar( $_POST[ self::COUNTRY_FIELD ] ) ) {
			$this->record_country_error();
			return;
		}

		$submitted = trim( (string) wp_unslash( $_POST[ self::COUNTRY_FIELD ] ) );

		if ( '0' === $submitted || '' === $submitted ) {
			wp_set_object_terms( $post_id, array(), self::COUNTRY, false );
			return;
		}

		if ( ! ctype_digit( $submitted ) ) {
			$this->record_country_error();
			return;
		}

		$country = get_term( (int) $submitted, self::COUNTRY );

		if ( ! $country instanceof \WP_Term ) {
			$this->record_country_error();
			return;
		}

		$result = wp_set_object_terms( $post_id, array( (int) $country->term_id ), self::COUNTRY, false );

		if ( is_wp_error( $result ) ) {
			$this->record_country_error();
		}
	}

	/**
	 * Enforce the one-country invariant after any taxonomy assignment path.
	 *
	 * @param int      $object_id  Object ID.
	 * @param array    $terms      Submitted terms.
	 * @param int[]    $tt_ids     Assigned term-taxonomy IDs.
	 * @param string   $taxonomy   Taxonomy key.
	 * @param bool     $append     Whether terms were appended.
	 * @param int[]    $old_tt_ids Previous term-taxonomy IDs.
	 * @return void
	 */
	public function enforce_single_country( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
		unset( $terms, $tt_ids, $append );

		if ( self::COUNTRY !== $taxonomy || Teacher_Post_Type::POST_TYPE !== get_post_type( $object_id ) ) {
			return;
		}

		$old_tt_ids      = (array) $old_tt_ids;
		$preferred_tt_id = 1 === count( $old_tt_ids ) ? (int) $old_tt_ids[0] : 0;

		$this->collapse_country_assignments( $object_id, $preferred_tt_id );
	}

	/**
	 * Show an admin notice when a submitted country value was rejected.
	 *
	 * @return void
	 */
	public function render_country_notice() {
		$screen = get_current_screen();

		if ( ! $screen || Teacher_Post_Type::POST_TYPE !== $screen->post_type ) {
			return;
		}

		$key = self::COUNTRY_ERROR_TRANSIENT_PREFIX . get_current_user_id();

		if ( ! get_transient( $key ) ) {
			return;
		}

		delete_transient( $key );
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo esc_html__( 'The primary country value was invalid and was not applied. A teacher can have only one country.', 'delnavazan-expansion' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Return assigned countries in a deterministic order.
	 *
	 * @param int $post_id Teacher post ID.
	 * @return \WP_Term[]
	 */
	private function get_assigned_countries( $post_id ) {
		$terms = wp_get_object_terms(
			$post_id,
			self::COUNTRY,
			array(
				'orderby' => 'term_id',
				'order'   => 'ASC',
			)
		);

		return is_wp_error( $terms ) ? array() : $terms;
	}

	/**
	 * Retain one deterministic country when malformed multiple assignments exist.
	 *
	 * @param int $post_id        Teacher post ID.
	 * @param int $preferred_tt_id Existing term-taxonomy ID to retain when still assigned.
	 * @return void
	 */
	private function collapse_country_assignments( $post_id, $preferred_tt_id = 0 ) {
		$terms = $this->get_assigned_countries( $post_id );

		if ( count( $terms ) <= 1 ) {
			return;
		}

		$primary_term_id = (int) $terms[0]->term_id;

		if ( $preferred_tt_id > 0 ) {
			foreach ( $terms as $term ) {
				if ( $preferred_tt_id === (int) $term->term_taxonomy_id ) {
					$primary_term_id = (int) $term->term_id;
					break;
				}
			}
		}

		wp_set_object_terms( $post_id, array( $primary_term_id ), self::COUNTRY, false );
	}

	/**
	 * Record a user-specific invalid-country notice.
	 *
	 * @return void
	 */
	private function record_country_error() {
		set_transient(
			self::COUNTRY_ERROR_TRANSIENT_PREFIX . get_current_user_id(),
			1,
			MINUTE_IN_SECONDS
		);
	}
}
