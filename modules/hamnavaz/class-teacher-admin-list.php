<?php
/**
 * Hamnavaz teacher admin list enhancements.
 *
 * @package DelnavazanExpansion
 */

namespace Delnavazan\Expansion\Hamnavaz;

defined( 'ABSPATH' ) || exit;

/**
 * Adds bounded columns and filters to the teacher list table.
 */
final class Teacher_Admin_List {

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'manage_' . Teacher_Post_Type::POST_TYPE . '_posts_columns', array( $this, 'filter_columns' ) );
		add_action( 'manage_' . Teacher_Post_Type::POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_action( 'restrict_manage_posts', array( $this, 'render_filters' ) );
		add_action( 'pre_get_posts', array( $this, 'apply_filters' ) );
	}

	/**
	 * Configure useful teacher list columns.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function filter_columns( $columns ) {
		$filtered = array();

		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$filtered[ $key ] = esc_html__( 'Persian Name', 'delnavazan-expansion' );
				$filtered['hamnavaz_instruments']  = esc_html__( 'Instruments', 'delnavazan-expansion' );
				$filtered['hamnavaz_location']     = esc_html__( 'Location', 'delnavazan-expansion' );
				$filtered['hamnavaz_listing']      = esc_html__( 'Listing Status', 'delnavazan-expansion' );
				$filtered['hamnavaz_verification'] = esc_html__( 'Verification', 'delnavazan-expansion' );
				continue;
			}

			if ( 'date' === $key ) {
				$filtered['hamnavaz_modified'] = esc_html__( 'Last Modified', 'delnavazan-expansion' );
				continue;
			}

			$filtered[ $key ] = $label;
		}

		return $filtered;
	}

	/**
	 * Render one custom list value.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Teacher post ID.
	 * @return void
	 */
	public function render_column( $column, $post_id ) {
		switch ( $column ) {
			case 'hamnavaz_instruments':
				$this->render_terms( $post_id, Teacher_Taxonomies::INSTRUMENT );
				break;
			case 'hamnavaz_location':
				$this->render_location( $post_id );
				break;
			case 'hamnavaz_listing':
				$this->render_option_label(
					get_post_meta( $post_id, Teacher_Meta::LISTING_STATUS_KEY, true ),
					Teacher_Meta::listing_status_options(),
					'draft'
				);
				break;
			case 'hamnavaz_verification':
				$this->render_verification( $post_id );
				break;
			case 'hamnavaz_modified':
				$modified = get_post_modified_time( get_option( 'date_format' ), false, $post_id, true );
				echo $modified ? esc_html( $modified ) : '&mdash;';
				break;
		}
	}

	/**
	 * Render bounded admin filters for teachers.
	 *
	 * @param string $post_type Current list post type.
	 * @return void
	 */
	public function render_filters( $post_type ) {
		if ( Teacher_Post_Type::POST_TYPE !== $post_type ) {
			return;
		}

		$this->render_taxonomy_filter(
			Teacher_Taxonomies::INSTRUMENT,
			'hamnavaz_instrument_filter',
			esc_html__( 'All instruments', 'delnavazan-expansion' )
		);

		$this->render_taxonomy_filter(
			Teacher_Taxonomies::COUNTRY,
			'hamnavaz_country_filter',
			esc_html__( 'All countries', 'delnavazan-expansion' )
		);

		$current_status = $this->request_value( 'hamnavaz_listing_status', 'key' );
		?>
		<label class="screen-reader-text" for="hamnavaz_listing_status_filter"><?php echo esc_html__( 'Filter by listing status', 'delnavazan-expansion' ); ?></label>
		<select id="hamnavaz_listing_status_filter" name="hamnavaz_listing_status">
			<option value=""><?php echo esc_html__( 'All listing statuses', 'delnavazan-expansion' ); ?></option>
			<?php foreach ( Teacher_Meta::listing_status_options() as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current_status, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Apply validated teacher filters to the main admin query.
	 *
	 * @param \WP_Query $query Current query.
	 * @return void
	 */
	public function apply_filters( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || Teacher_Post_Type::POST_TYPE !== $query->get( 'post_type' ) ) {
			return;
		}

		$existing_tax_query = $query->get( 'tax_query' );
		$tax_query          = is_array( $existing_tax_query ) ? $existing_tax_query : array();
		$filters            = array(
			'hamnavaz_instrument_filter' => Teacher_Taxonomies::INSTRUMENT,
			'hamnavaz_country_filter'    => Teacher_Taxonomies::COUNTRY,
		);

		foreach ( $filters as $request_key => $taxonomy ) {
			$value = $this->request_value( $request_key, 'title' );

			if ( '' === $value || ! term_exists( $value, $taxonomy ) ) {
				continue;
			}

			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $value,
			);
		}

		if ( ! empty( $tax_query ) ) {
			$query->set( 'tax_query', $tax_query );
		}

		$status   = $this->request_value( 'hamnavaz_listing_status', 'key' );
		$statuses = Teacher_Meta::listing_status_options();

		if ( ! array_key_exists( $status, $statuses ) ) {
			return;
		}

		$existing_meta_query = $query->get( 'meta_query' );
		$meta_query          = is_array( $existing_meta_query ) ? $existing_meta_query : array();

		if ( 'draft' === $status ) {
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => Teacher_Meta::LISTING_STATUS_KEY,
					'value'   => 'draft',
					'compare' => '=',
				),
				array(
					'key'     => Teacher_Meta::LISTING_STATUS_KEY,
					'compare' => 'NOT EXISTS',
				),
			);
		} else {
			$meta_query[] = array(
				'key'     => Teacher_Meta::LISTING_STATUS_KEY,
				'value'   => $status,
				'compare' => '=',
			);
		}

		$query->set( 'meta_query', $meta_query );
	}

	/**
	 * Render a taxonomy filter select.
	 *
	 * @param string $taxonomy   Taxonomy key.
	 * @param string $request_key Query parameter name.
	 * @param string $all_label  Empty-option label.
	 * @return void
	 */
	private function render_taxonomy_filter( $taxonomy, $request_key, $all_label ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		if ( is_wp_error( $terms ) ) {
			$terms = array();
		}

		$current = $this->request_value( $request_key, 'title' );
		?>
		<label class="screen-reader-text" for="<?php echo esc_attr( $request_key ); ?>"><?php echo esc_html( $all_label ); ?></label>
		<select id="<?php echo esc_attr( $request_key ); ?>" name="<?php echo esc_attr( $request_key ); ?>">
			<option value=""><?php echo esc_html( $all_label ); ?></option>
			<?php foreach ( $terms as $term ) : ?>
				<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $current, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render comma-separated taxonomy terms.
	 *
	 * @param int    $post_id  Teacher post ID.
	 * @param string $taxonomy Taxonomy key.
	 * @return void
	 */
	private function render_terms( $post_id, $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			echo '&mdash;';
			return;
		}

		echo esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) );
	}

	/**
	 * Render City, Country location text.
	 *
	 * @param int $post_id Teacher post ID.
	 * @return void
	 */
	private function render_location( $post_id ) {
		$parts = array();
		$city  = get_post_meta( $post_id, Teacher_Meta::CITY_KEY, true );

		if ( $city ) {
			$parts[] = $city;
		}

		$countries = get_the_terms( $post_id, Teacher_Taxonomies::COUNTRY );

		if ( ! empty( $countries ) && ! is_wp_error( $countries ) ) {
			$parts = array_merge( $parts, wp_list_pluck( $countries, 'name' ) );
		}

		echo empty( $parts ) ? '&mdash;' : esc_html( implode( ', ', $parts ) );
	}

	/**
	 * Render verification state and last-confirmed date.
	 *
	 * @param int $post_id Teacher post ID.
	 * @return void
	 */
	private function render_verification( $post_id ) {
		$status  = get_post_meta( $post_id, Teacher_Meta::VERIFICATION_STATUS_KEY, true );
		$options = Teacher_Meta::verification_status_options();

		$this->render_option_label( $status, $options, 'not_requested' );

		$timestamp = (int) get_post_meta( $post_id, Teacher_Meta::LAST_VERIFIED_KEY, true );

		if ( $timestamp > 0 ) {
			echo '<br />';
			echo esc_html( wp_date( get_option( 'date_format' ), $timestamp, wp_timezone() ) );
		}
	}

	/**
	 * Render a controlled option label with a safe default.
	 *
	 * @param string   $value         Stored value.
	 * @param string[] $options       Controlled options.
	 * @param string   $default_value Default option key.
	 * @return void
	 */
	private function render_option_label( $value, $options, $default_value ) {
		$value = isset( $options[ $value ] ) ? $value : $default_value;

		echo esc_html( $options[ $value ] );
	}

	/**
	 * Read a scalar admin filter value safely.
	 *
	 * @param string $request_key Query parameter name.
	 * @param string $sanitize    Sanitizer identifier: key or title.
	 * @return string
	 */
	private function request_value( $request_key, $sanitize ) {
		if ( ! isset( $_GET[ $request_key ] ) || ! is_scalar( $_GET[ $request_key ] ) ) {
			return '';
		}

		$value = wp_unslash( $_GET[ $request_key ] );

		return 'title' === $sanitize ? sanitize_title( $value ) : sanitize_key( $value );
	}
}
