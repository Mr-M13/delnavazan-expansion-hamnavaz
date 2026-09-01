<?php
/**
 * Hamnavaz teacher metadata and edit interface.
 *
 * @package DelnavazanExpansion
 */

namespace Delnavazan\Expansion\Hamnavaz;

defined( 'ABSPATH' ) || exit;

/**
 * Registers, renders, validates, and saves structured teacher metadata.
 */
final class Teacher_Meta {

	/**
	 * Listing status meta key.
	 *
	 * @var string
	 */
	const LISTING_STATUS_KEY = '_hamnavaz_listing_status';

	/**
	 * Verification status meta key.
	 *
	 * @var string
	 */
	const VERIFICATION_STATUS_KEY = '_hamnavaz_verification_status';

	/**
	 * Last verified timestamp meta key.
	 *
	 * @var string
	 */
	const LAST_VERIFIED_KEY = '_hamnavaz_last_verified_at';

	/**
	 * Next verification timestamp meta key.
	 *
	 * @var string
	 */
	const NEXT_VERIFICATION_KEY = '_hamnavaz_next_verification_at';

	/**
	 * City meta key.
	 *
	 * @var string
	 */
	const CITY_KEY = '_hamnavaz_city';

	/**
	 * Normalized internal city key.
	 *
	 * @var string
	 */
	const CITY_NORMALIZED_KEY = '_hamnavaz_city_key';

	/**
	 * Nonce action.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'hamnavaz_save_teacher_meta';

	/**
	 * Nonce field name.
	 *
	 * @var string
	 */
	const NONCE_NAME = '_hamnavaz_teacher_nonce';

	/**
	 * User-specific validation notice prefix.
	 *
	 * @var string
	 */
	const ERROR_TRANSIENT_PREFIX = 'hamnavaz_teacher_save_errors_';

	/**
	 * Cached field registry for this request.
	 *
	 * @var array|null
	 */
	private $fields = null;

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_meta' ), 7 );
		add_action( 'add_meta_boxes_' . Teacher_Post_Type::POST_TYPE, array( $this, 'register_meta_boxes' ) );
		add_action( 'edit_form_top', array( $this, 'render_nonce' ) );
		add_action( 'save_post_' . Teacher_Post_Type::POST_TYPE, array( $this, 'save' ), 10, 2 );
		add_action( 'added_post_meta', array( $this, 'synchronize_city_key_on_meta_change' ), 10, 4 );
		add_action( 'updated_post_meta', array( $this, 'synchronize_city_key_on_meta_change' ), 10, 4 );
		add_action( 'deleted_post_meta', array( $this, 'synchronize_city_key_on_meta_change' ), 10, 4 );
		add_action( 'admin_notices', array( $this, 'render_validation_notice' ) );
	}

	/**
	 * Return canonical listing statuses.
	 *
	 * WordPress post status remains an editorial state. This controlled field is the
	 * authoritative Hamnavaz listing lifecycle state.
	 *
	 * @return string[]
	 */
	public static function listing_status_options() {
		return array(
			'draft'            => esc_html__( 'Draft', 'delnavazan-expansion' ),
			'pending_review'   => esc_html__( 'Pending Review', 'delnavazan-expansion' ),
			'active'           => esc_html__( 'Active', 'delnavazan-expansion' ),
			'verification_due' => esc_html__( 'Verification Due', 'delnavazan-expansion' ),
			'on_hold'          => esc_html__( 'On Hold', 'delnavazan-expansion' ),
			'archived'         => esc_html__( 'Archived', 'delnavazan-expansion' ),
		);
	}

	/**
	 * Return verification states.
	 *
	 * @return string[]
	 */
	public static function verification_status_options() {
		return array(
			'not_requested' => esc_html__( 'Not Requested', 'delnavazan-expansion' ),
			'pending'       => esc_html__( 'Pending Confirmation', 'delnavazan-expansion' ),
			'confirmed'     => esc_html__( 'Confirmed', 'delnavazan-expansion' ),
			'overdue'       => esc_html__( 'Overdue', 'delnavazan-expansion' ),
		);
	}

	/**
	 * Register all Phase 2 metadata contracts.
	 *
	 * @return void
	 */
	public function register_meta() {
		foreach ( $this->field_definitions() as $meta_key => $field ) {
			$args = array(
				'object_subtype'    => Teacher_Post_Type::POST_TYPE,
				'type'              => $field['meta_type'],
				'single'            => true,
				'sanitize_callback' => array( $this, 'sanitize_registered_meta' ),
				'auth_callback'     => array( $this, 'authorize_meta' ),
				'show_in_rest'      => false,
			);

			if ( array_key_exists( 'default', $field ) ) {
				$args['default'] = $field['default'];
			}

			register_post_meta( Teacher_Post_Type::POST_TYPE, $meta_key, $args );
		}
	}

	/**
	 * Authorize metadata access through WordPress metadata APIs.
	 *
	 * @param bool   $allowed Whether access is otherwise allowed.
	 * @param string $meta_key Meta key being accessed.
	 * @param int    $post_id Teacher post ID.
	 * @return bool
	 */
	public function authorize_meta( $allowed, $meta_key, $post_id ) {
		unset( $allowed );

		if ( self::CITY_NORMALIZED_KEY === $meta_key ) {
			return false;
		}

		return current_user_can( 'edit_post', (int) $post_id );
	}

	/**
	 * Sanitize writes made through the WordPress metadata API.
	 *
	 * @param mixed  $value    Submitted value.
	 * @param string $meta_key Meta key.
	 * @return mixed
	 */
	public function sanitize_registered_meta( $value, $meta_key ) {
		$result = $this->validate_and_sanitize( $meta_key, $value );

		if ( is_wp_error( $result ) ) {
			return $this->default_value( $meta_key );
		}

		return $result;
	}

	/**
	 * Register logical teacher edit meta boxes.
	 *
	 * @return void
	 */
	public function register_meta_boxes() {
		if ( ! current_theme_supports( 'post-thumbnails', Teacher_Post_Type::POST_TYPE ) ) {
			add_meta_box(
				'postimagediv',
				esc_html__( 'Profile Photograph', 'delnavazan-expansion' ),
				'post_thumbnail_meta_box',
				Teacher_Post_Type::POST_TYPE,
				'side',
				'low'
			);
		}

		$boxes = array(
			'identity'    => array(
				'title'    => esc_html__( 'Identity', 'delnavazan-expansion' ),
				'priority' => 'high',
			),
			'teaching'    => array(
				'title'    => esc_html__( 'Teaching', 'delnavazan-expansion' ),
				'priority' => 'default',
			),
			'location'    => array(
				'title'    => esc_html__( 'Location', 'delnavazan-expansion' ),
				'priority' => 'default',
			),
			'contact'     => array(
				'title'    => esc_html__( 'Contact', 'delnavazan-expansion' ),
				'priority' => 'default',
			),
			'lesson'      => array(
				'title'    => esc_html__( 'Lesson Information', 'delnavazan-expansion' ),
				'priority' => 'default',
			),
			'operational' => array(
				'title'    => esc_html__( 'Operational', 'delnavazan-expansion' ),
				'priority' => 'default',
			),
		);

		foreach ( $boxes as $section => $box ) {
			add_meta_box(
				'hamnavaz_teacher_' . $section,
				$box['title'],
				array( $this, 'render_meta_box' ),
				Teacher_Post_Type::POST_TYPE,
				'normal',
				$box['priority'],
				array( 'section' => $section )
			);
		}
	}

	/**
	 * Render the custom nonce once in the teacher edit form.
	 *
	 * @param \WP_Post $post Current post.
	 * @return void
	 */
	public function render_nonce( $post ) {
		if ( ! $post instanceof \WP_Post || Teacher_Post_Type::POST_TYPE !== $post->post_type ) {
			return;
		}

		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
	}

	/**
	 * Render one logical metadata section.
	 *
	 * @param \WP_Post $post Current teacher post.
	 * @param array    $box  Meta box configuration.
	 * @return void
	 */
	public function render_meta_box( $post, $box ) {
		$section = isset( $box['args']['section'] ) ? sanitize_key( $box['args']['section'] ) : '';

		if ( 'identity' === $section ) {
			?>
			<p class="description">
				<?php echo esc_html__( 'Use the WordPress title for the Persian name, Excerpt for the short biography, main editor for the full biography, and Featured Image for the profile photograph.', 'delnavazan-expansion' ); ?>
			</p>
			<?php
		}

		echo '<div class="hamnavaz-teacher-fields">';

		foreach ( $this->field_definitions() as $meta_key => $field ) {
			if ( $section !== $field['section'] || ! empty( $field['derived'] ) ) {
				continue;
			}

			$this->render_field( $post->ID, $meta_key, $field );
		}

		echo '</div>';
	}

	/**
	 * Save teacher metadata safely.
	 *
	 * @param int      $post_id Teacher post ID.
	 * @param \WP_Post $post    Teacher post object.
	 * @return void
	 */
	public function save( $post_id, $post ) {
		if ( ! $post instanceof \WP_Post || Teacher_Post_Type::POST_TYPE !== $post->post_type ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! is_scalar( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) );

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$errors = array();

		foreach ( $this->field_definitions() as $meta_key => $field ) {
			if ( ! empty( $field['derived'] ) ) {
				continue;
			}

			if ( 'checkbox' === $field['input_type'] ) {
				$raw_value = isset( $_POST[ $meta_key ] ) ? '1' : '0';
			} elseif ( 'checkboxes' === $field['input_type'] ) {
				$raw_value = isset( $_POST[ $meta_key ] ) ? wp_unslash( $_POST[ $meta_key ] ) : array();
			} elseif ( ! isset( $_POST[ $meta_key ] ) ) {
				continue;
			} else {
				$raw_value = wp_unslash( $_POST[ $meta_key ] );
			}

			$value = $this->validate_and_sanitize( $meta_key, $raw_value );

			if ( is_wp_error( $value ) ) {
				$errors[] = $field['label'];
				continue;
			}

			if ( $this->is_empty_value( $value, $field ) ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, $value );
			}
		}

		$this->synchronize_city_key( $post_id );

		if ( ! empty( $errors ) ) {
			set_transient(
				self::ERROR_TRANSIENT_PREFIX . get_current_user_id(),
				array_values( array_unique( $errors ) ),
				MINUTE_IN_SECONDS
			);
		}
	}

	/**
	 * Keep the derived city key synchronized when the readable city meta changes.
	 *
	 * @param mixed  $meta_id    Metadata row ID or deleted row IDs.
	 * @param int    $object_id  Post ID.
	 * @param string $meta_key   Changed meta key.
	 * @param mixed  $meta_value Changed meta value.
	 * @return void
	 */
	public function synchronize_city_key_on_meta_change( $meta_id, $object_id, $meta_key, $meta_value ) {
		unset( $meta_id, $meta_value );

		if ( self::CITY_KEY !== $meta_key || Teacher_Post_Type::POST_TYPE !== get_post_type( $object_id ) ) {
			return;
		}

		$this->synchronize_city_key( $object_id );
	}

	/**
	 * Derive a stable, filterable city key from a human-readable city label.
	 *
	 * WordPress slug normalization collapses case, accents, whitespace, and common
	 * punctuation differences without introducing a global city vocabulary.
	 *
	 * @param mixed $city Human-readable city value.
	 * @return string
	 */
	public static function normalize_city_key( $city ) {
		if ( ! is_scalar( $city ) ) {
			return '';
		}

		$city = sanitize_text_field( (string) $city );

		return '' === $city ? '' : sanitize_title( $city );
	}

	/**
	 * Display a safe validation notice after a rejected save value.
	 *
	 * @return void
	 */
	public function render_validation_notice() {
		$screen = get_current_screen();

		if ( ! $screen || Teacher_Post_Type::POST_TYPE !== $screen->post_type ) {
			return;
		}

		$transient_key = self::ERROR_TRANSIENT_PREFIX . get_current_user_id();
		$errors        = get_transient( $transient_key );

		if ( empty( $errors ) || ! is_array( $errors ) ) {
			return;
		}

		delete_transient( $transient_key );
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo esc_html__( 'Some teacher fields contained invalid values and were not changed:', 'delnavazan-expansion' ); ?></p>
			<ul>
				<?php foreach ( $errors as $label ) : ?>
					<li><?php echo esc_html( $label ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Render one registered metadata field.
	 *
	 * @param int    $post_id  Teacher post ID.
	 * @param string $meta_key Meta key.
	 * @param array  $field    Field definition.
	 * @return void
	 */
	private function render_field( $post_id, $meta_key, $field ) {
		$value    = get_post_meta( $post_id, $meta_key, true );
		$field_id = ltrim( $meta_key, '_' );
		?>
		<div class="hamnavaz-teacher-field hamnavaz-teacher-field--<?php echo esc_attr( $field['input_type'] ); ?>">
			<?php if ( 'checkbox' !== $field['input_type'] && 'checkboxes' !== $field['input_type'] ) : ?>
				<label for="<?php echo esc_attr( $field_id ); ?>"><strong><?php echo esc_html( $field['label'] ); ?></strong></label>
			<?php endif; ?>
			<?php
			switch ( $field['input_type'] ) {
				case 'textarea':
					$this->render_textarea( $field_id, $meta_key, $value, $field );
					break;
				case 'select':
					$this->render_select( $field_id, $meta_key, $value, $field );
					break;
				case 'checkbox':
					$this->render_checkbox( $field_id, $meta_key, $value, $field );
					break;
				case 'checkboxes':
					$this->render_checkboxes( $field_id, $meta_key, $value, $field );
					break;
				case 'datetime-local':
					$this->render_datetime( $field_id, $meta_key, $value );
					break;
				default:
					$this->render_input( $field_id, $meta_key, $value, $field );
					break;
			}
			?>
			<?php if ( ! empty( $field['description'] ) ) : ?>
				<p class="description"><?php echo esc_html( $field['description'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render a text-like input.
	 *
	 * @param string $field_id Field ID.
	 * @param string $meta_key Meta key.
	 * @param mixed  $value    Current value.
	 * @param array  $field    Field definition.
	 * @return void
	 */
	private function render_input( $field_id, $meta_key, $value, $field ) {
		$type = in_array( $field['input_type'], array( 'text', 'email', 'url', 'tel', 'number' ), true ) ? $field['input_type'] : 'text';
		?>
		<input
			type="<?php echo esc_attr( $type ); ?>"
			id="<?php echo esc_attr( $field_id ); ?>"
			name="<?php echo esc_attr( $meta_key ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			class="widefat"
			<?php if ( 'number' === $type ) : ?>min="0" step="0.01"<?php endif; ?>
		/>
		<?php
	}

	/**
	 * Render a textarea.
	 *
	 * @param string $field_id Field ID.
	 * @param string $meta_key Meta key.
	 * @param mixed  $value    Current value.
	 * @param array  $field    Field definition.
	 * @return void
	 */
	private function render_textarea( $field_id, $meta_key, $value, $field ) {
		$rows = isset( $field['rows'] ) ? absint( $field['rows'] ) : 4;
		?>
		<textarea id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $meta_key ); ?>" rows="<?php echo esc_attr( $rows ); ?>" class="widefat"><?php echo esc_textarea( $value ); ?></textarea>
		<?php
	}

	/**
	 * Render a controlled select.
	 *
	 * @param string $field_id Field ID.
	 * @param string $meta_key Meta key.
	 * @param mixed  $value    Current value.
	 * @param array  $field    Field definition.
	 * @return void
	 */
	private function render_select( $field_id, $meta_key, $value, $field ) {
		?>
		<select id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $meta_key ); ?>" class="widefat">
			<?php foreach ( $field['options'] as $option_value => $option_label ) : ?>
				<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( (string) $value, (string) $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render a boolean checkbox.
	 *
	 * @param string $field_id Field ID.
	 * @param string $meta_key Meta key.
	 * @param mixed  $value    Current value.
	 * @param array  $field    Field definition.
	 * @return void
	 */
	private function render_checkbox( $field_id, $meta_key, $value, $field ) {
		?>
		<label for="<?php echo esc_attr( $field_id ); ?>">
			<input type="checkbox" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $meta_key ); ?>" value="1" <?php checked( ! empty( $value ) ); ?> />
			<strong><?php echo esc_html( $field['label'] ); ?></strong>
		</label>
		<?php
	}

	/**
	 * Render a controlled multi-checkbox group.
	 *
	 * @param string $field_id Field ID prefix.
	 * @param string $meta_key Meta key.
	 * @param mixed  $value    Current value.
	 * @param array  $field    Field definition.
	 * @return void
	 */
	private function render_checkboxes( $field_id, $meta_key, $value, $field ) {
		$selected_values = is_array( $value ) ? $value : array();
		?>
		<fieldset>
			<legend><strong><?php echo esc_html( $field['label'] ); ?></strong></legend>
			<div class="hamnavaz-teacher-field__choices">
				<?php foreach ( $field['options'] as $option_value => $option_label ) : ?>
					<label for="<?php echo esc_attr( $field_id . '_' . $option_value ); ?>">
						<input
							type="checkbox"
							id="<?php echo esc_attr( $field_id . '_' . $option_value ); ?>"
							name="<?php echo esc_attr( $meta_key ); ?>[]"
							value="<?php echo esc_attr( $option_value ); ?>"
							<?php checked( in_array( $option_value, $selected_values, true ) ); ?>
						/>
						<?php echo esc_html( $option_label ); ?>
					</label>
				<?php endforeach; ?>
			</div>
		</fieldset>
		<?php
	}

	/**
	 * Render a local-site-time datetime field backed by a Unix timestamp.
	 *
	 * @param string $field_id Field ID.
	 * @param string $meta_key Meta key.
	 * @param mixed  $value    Current timestamp.
	 * @return void
	 */
	private function render_datetime( $field_id, $meta_key, $value ) {
		$formatted = '';

		if ( ! empty( $value ) ) {
			$formatted = wp_date( 'Y-m-d\TH:i', (int) $value, wp_timezone() );
		}
		?>
		<input type="datetime-local" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $meta_key ); ?>" value="<?php echo esc_attr( $formatted ); ?>" class="widefat" />
		<?php
	}

	/**
	 * Validate and sanitize one field value.
	 *
	 * @param string $meta_key Meta key.
	 * @param mixed  $value    Submitted value.
	 * @return mixed|\WP_Error
	 */
	private function validate_and_sanitize( $meta_key, $value ) {
		$definitions = $this->field_definitions();

		if ( ! isset( $definitions[ $meta_key ] ) ) {
			return new \WP_Error( 'unknown_teacher_field' );
		}

		$field = $definitions[ $meta_key ];

		switch ( $field['sanitize'] ) {
			case 'text':
				return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : new \WP_Error( 'invalid_text' );
			case 'city_key':
				return self::normalize_city_key( $value );
			case 'textarea':
				return is_scalar( $value ) ? sanitize_textarea_field( (string) $value ) : new \WP_Error( 'invalid_textarea' );
			case 'email':
				return $this->sanitize_email( $value );
			case 'url':
				return $this->sanitize_url( $value );
			case 'phone':
				return $this->sanitize_phone( $value );
			case 'price':
				return $this->sanitize_price( $value );
			case 'currency':
				return $this->sanitize_currency( $value );
			case 'boolean':
				return $this->sanitize_boolean( $value );
			case 'timestamp':
				return $this->sanitize_timestamp( $value );
			case 'enum':
				$value = is_scalar( $value ) ? sanitize_key( (string) $value ) : '';

				return array_key_exists( $value, $field['options'] ) ? $value : new \WP_Error( 'invalid_option' );
			case 'enum_array':
				return $this->sanitize_enum_array( $value, $field['options'] );
		}

		return new \WP_Error( 'unsupported_teacher_field' );
	}

	/**
	 * Sanitize an email address, rejecting invalid non-empty values.
	 *
	 * @param mixed $value Submitted value.
	 * @return string|\WP_Error
	 */
	private function sanitize_email( $value ) {
		if ( ! is_scalar( $value ) ) {
			return new \WP_Error( 'invalid_email' );
		}

		$value = trim( (string) $value );

		if ( '' === $value ) {
			return '';
		}

		$email = sanitize_email( $value );

		return is_email( $email ) ? $email : new \WP_Error( 'invalid_email' );
	}

	/**
	 * Sanitize an HTTP(S) URL.
	 *
	 * @param mixed $value Submitted value.
	 * @return string|\WP_Error
	 */
	private function sanitize_url( $value ) {
		if ( ! is_scalar( $value ) ) {
			return new \WP_Error( 'invalid_url' );
		}

		$value = trim( (string) $value );

		if ( '' === $value ) {
			return '';
		}

		$url    = esc_url_raw( $value, array( 'http', 'https' ) );
		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
		$host   = wp_parse_url( $url, PHP_URL_HOST );

		if ( empty( $url ) || empty( $host ) || ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
			return new \WP_Error( 'invalid_url' );
		}

		return $url;
	}

	/**
	 * Sanitize an international phone-like value.
	 *
	 * @param mixed $value Submitted value.
	 * @return string|\WP_Error
	 */
	private function sanitize_phone( $value ) {
		if ( ! is_scalar( $value ) ) {
			return new \WP_Error( 'invalid_phone' );
		}

		$value = trim( sanitize_text_field( (string) $value ) );

		if ( '' === $value ) {
			return '';
		}

		if ( strlen( $value ) > 25 || ! preg_match( '/^\+?[0-9][0-9\s().-]{4,24}$/', $value ) ) {
			return new \WP_Error( 'invalid_phone' );
		}

		return $value;
	}

	/**
	 * Validate and normalize a non-negative indicative price.
	 *
	 * @param mixed $value Submitted value.
	 * @return float|string|\WP_Error
	 */
	private function sanitize_price( $value ) {
		if ( ! is_scalar( $value ) ) {
			return new \WP_Error( 'invalid_price' );
		}

		$value = trim( (string) $value );

		if ( '' === $value ) {
			return '';
		}

		if ( ! is_numeric( $value ) ) {
			return new \WP_Error( 'invalid_price' );
		}

		$price = round( (float) $value, 2 );

		if ( $price < 0 || $price > 999999999 ) {
			return new \WP_Error( 'invalid_price' );
		}

		return $price;
	}

	/**
	 * Validate a three-letter ISO-style currency code.
	 *
	 * @param mixed $value Submitted value.
	 * @return string|\WP_Error
	 */
	private function sanitize_currency( $value ) {
		if ( ! is_scalar( $value ) ) {
			return new \WP_Error( 'invalid_currency' );
		}

		$value = strtoupper( trim( (string) $value ) );

		if ( '' === $value ) {
			return '';
		}

		return preg_match( '/^[A-Z]{3}$/', $value ) ? $value : new \WP_Error( 'invalid_currency' );
	}

	/**
	 * Normalize a submitted boolean.
	 *
	 * @param mixed $value Submitted value.
	 * @return bool|\WP_Error
	 */
	private function sanitize_boolean( $value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( ! is_scalar( $value ) ) {
			return new \WP_Error( 'invalid_boolean' );
		}

		return in_array( strtolower( (string) $value ), array( '1', 'true', 'yes', 'on' ), true );
	}

	/**
	 * Convert a site-local datetime input into a Unix timestamp.
	 *
	 * @param mixed $value Submitted value.
	 * @return int|\WP_Error
	 */
	private function sanitize_timestamp( $value ) {
		if ( ! is_scalar( $value ) ) {
			return new \WP_Error( 'invalid_timestamp' );
		}

		$value = trim( (string) $value );

		if ( '' === $value ) {
			return 0;
		}

		if ( ctype_digit( $value ) ) {
			return absint( $value );
		}

		$date   = \DateTimeImmutable::createFromFormat( '!Y-m-d\TH:i', $value, wp_timezone() );
		$errors = \DateTimeImmutable::getLastErrors();

		if ( false === $date || ( is_array( $errors ) && ( $errors['warning_count'] > 0 || $errors['error_count'] > 0 ) ) ) {
			return new \WP_Error( 'invalid_timestamp' );
		}

		return $date->getTimestamp();
	}

	/**
	 * Validate a controlled multi-value field.
	 *
	 * @param mixed    $value   Submitted value.
	 * @param string[] $options Allowed options.
	 * @return string[]|\WP_Error
	 */
	private function sanitize_enum_array( $value, $options ) {
		if ( ! is_array( $value ) ) {
			return new \WP_Error( 'invalid_options' );
		}

		$values = array();

		foreach ( $value as $item ) {
			if ( ! is_scalar( $item ) ) {
				return new \WP_Error( 'invalid_options' );
			}

			$values[] = sanitize_key( (string) $item );
		}

		$values = array_values( array_unique( $values ) );

		foreach ( $values as $item ) {
			if ( ! array_key_exists( $item, $options ) ) {
				return new \WP_Error( 'invalid_options' );
			}
		}

		return $values;
	}

	/**
	 * Determine whether an empty value should remove stored metadata.
	 *
	 * @param mixed $value Sanitized value.
	 * @param array $field Field definition.
	 * @return bool
	 */
	private function is_empty_value( $value, $field ) {
		if ( 'array' === $field['meta_type'] ) {
			return empty( $value );
		}

		if ( 'boolean' === $field['meta_type'] ) {
			return false === $value;
		}

		if ( 'integer' === $field['meta_type'] ) {
			return 0 === $value;
		}

		return '' === $value;
	}

	/**
	 * Return a field's registered default for rejected API writes.
	 *
	 * @param string $meta_key Meta key.
	 * @return mixed
	 */
	private function default_value( $meta_key ) {
		$definitions = $this->field_definitions();

		return isset( $definitions[ $meta_key ]['default'] ) ? $definitions[ $meta_key ]['default'] : '';
	}

	/**
	 * Synchronize the internal city key with the current readable city value.
	 *
	 * @param int $post_id Teacher post ID.
	 * @return void
	 */
	private function synchronize_city_key( $post_id ) {
		$city_key = self::normalize_city_key( get_post_meta( $post_id, self::CITY_KEY, true ) );

		if ( '' === $city_key ) {
			delete_post_meta( $post_id, self::CITY_NORMALIZED_KEY );
			return;
		}

		update_post_meta( $post_id, self::CITY_NORMALIZED_KEY, $city_key );
	}

	/**
	 * Return the complete Phase 2 field registry.
	 *
	 * @return array
	 */
	private function field_definitions() {
		if ( null !== $this->fields ) {
			return $this->fields;
		}

		$this->fields = array(
			'_hamnavaz_english_name'       => array(
				'section'     => 'identity',
				'label'       => esc_html__( 'English Name', 'delnavazan-expansion' ),
				'description' => esc_html__( 'Optional English or Latin-script teacher name.', 'delnavazan-expansion' ),
				'input_type'  => 'text',
				'meta_type'   => 'string',
				'sanitize'    => 'text',
			),
			'_hamnavaz_specialisations'    => array(
				'section'     => 'teaching',
				'label'       => esc_html__( 'Specialisations', 'delnavazan-expansion' ),
				'description' => esc_html__( 'Concise specialisations not represented by instrument terms.', 'delnavazan-expansion' ),
				'input_type'  => 'textarea',
				'meta_type'   => 'string',
				'sanitize'    => 'textarea',
				'rows'        => 3,
			),
			'_hamnavaz_teaching_levels'    => array(
				'section'     => 'teaching',
				'label'       => esc_html__( 'Teaching Levels', 'delnavazan-expansion' ),
				'description' => '',
				'input_type'  => 'checkboxes',
				'meta_type'   => 'array',
				'sanitize'    => 'enum_array',
				'default'     => array(),
				'options'     => array(
					'beginner'     => esc_html__( 'Beginner', 'delnavazan-expansion' ),
					'intermediate' => esc_html__( 'Intermediate', 'delnavazan-expansion' ),
					'advanced'     => esc_html__( 'Advanced', 'delnavazan-expansion' ),
					'professional' => esc_html__( 'Professional', 'delnavazan-expansion' ),
				),
			),
			'_hamnavaz_in_person'          => array(
				'section'     => 'teaching',
				'label'       => esc_html__( 'Offers In-Person Tuition', 'delnavazan-expansion' ),
				'description' => '',
				'input_type'  => 'checkbox',
				'meta_type'   => 'boolean',
				'sanitize'    => 'boolean',
				'default'     => false,
			),
			'_hamnavaz_online'             => array(
				'section'     => 'teaching',
				'label'       => esc_html__( 'Offers Online Tuition', 'delnavazan-expansion' ),
				'description' => '',
				'input_type'  => 'checkbox',
				'meta_type'   => 'boolean',
				'sanitize'    => 'boolean',
				'default'     => false,
			),
			'_hamnavaz_state'              => array(
				'section'     => 'location',
				'label'       => esc_html__( 'State / Province', 'delnavazan-expansion' ),
				'description' => '',
				'input_type'  => 'text',
				'meta_type'   => 'string',
				'sanitize'    => 'text',
			),
			self::CITY_KEY                 => array(
				'section'     => 'location',
				'label'       => esc_html__( 'City', 'delnavazan-expansion' ),
				'description' => esc_html__( 'Readable city label. A normalized internal key is derived automatically for future filtering.', 'delnavazan-expansion' ),
				'input_type'  => 'text',
				'meta_type'   => 'string',
				'sanitize'    => 'text',
			),
			self::CITY_NORMALIZED_KEY      => array(
				'section'     => 'location',
				'label'       => esc_html__( 'Normalized City Key', 'delnavazan-expansion' ),
				'description' => '',
				'input_type'  => 'text',
				'meta_type'   => 'string',
				'sanitize'    => 'city_key',
				'derived'     => true,
			),
			'_hamnavaz_suburb'             => array(
				'section'     => 'location',
				'label'       => esc_html__( 'Suburb / Area', 'delnavazan-expansion' ),
				'description' => esc_html__( 'Optional public area only; do not enter a residential street address.', 'delnavazan-expansion' ),
				'input_type'  => 'text',
				'meta_type'   => 'string',
				'sanitize'    => 'text',
			),
			'_hamnavaz_public_location'    => array(
				'section'     => 'location',
				'label'       => esc_html__( 'Public Location Description', 'delnavazan-expansion' ),
				'description' => esc_html__( 'Public-facing description only; never store a private residential address here.', 'delnavazan-expansion' ),
				'input_type'  => 'textarea',
				'meta_type'   => 'string',
				'sanitize'    => 'textarea',
				'rows'        => 3,
			),
			'_hamnavaz_whatsapp'           => array(
				'section'     => 'contact',
				'label'       => esc_html__( 'WhatsApp', 'delnavazan-expansion' ),
				'description' => esc_html__( 'International format is recommended, for example +61400000000.', 'delnavazan-expansion' ),
				'input_type'  => 'tel',
				'meta_type'   => 'string',
				'sanitize'    => 'phone',
			),
			'_hamnavaz_phone'              => array(
				'section'     => 'contact',
				'label'       => esc_html__( 'Phone', 'delnavazan-expansion' ),
				'description' => '',
				'input_type'  => 'tel',
				'meta_type'   => 'string',
				'sanitize'    => 'phone',
			),
			'_hamnavaz_email'              => array(
				'section'     => 'contact',
				'label'       => esc_html__( 'Email', 'delnavazan-expansion' ),
				'description' => '',
				'input_type'  => 'email',
				'meta_type'   => 'string',
				'sanitize'    => 'email',
			),
			'_hamnavaz_website'            => array(
				'section'     => 'contact',
				'label'       => esc_html__( 'Website', 'delnavazan-expansion' ),
				'description' => '',
				'input_type'  => 'url',
				'meta_type'   => 'string',
				'sanitize'    => 'url',
			),
			'_hamnavaz_instagram'          => array(
				'section'     => 'contact',
				'label'       => esc_html__( 'Instagram URL', 'delnavazan-expansion' ),
				'description' => '',
				'input_type'  => 'url',
				'meta_type'   => 'string',
				'sanitize'    => 'url',
			),
			'_hamnavaz_youtube'            => array(
				'section'     => 'contact',
				'label'       => esc_html__( 'YouTube URL', 'delnavazan-expansion' ),
				'description' => '',
				'input_type'  => 'url',
				'meta_type'   => 'string',
				'sanitize'    => 'url',
			),
			'_hamnavaz_other_contact_url'  => array(
				'section'     => 'contact',
				'label'       => esc_html__( 'Other Approved Contact URL', 'delnavazan-expansion' ),
				'description' => '',
				'input_type'  => 'url',
				'meta_type'   => 'string',
				'sanitize'    => 'url',
			),
			'_hamnavaz_price'              => array(
				'section'     => 'lesson',
				'label'       => esc_html__( 'Indicative Price', 'delnavazan-expansion' ),
				'description' => esc_html__( 'Informational only; Delnavazan does not process independent lesson payments.', 'delnavazan-expansion' ),
				'input_type'  => 'number',
				'meta_type'   => 'number',
				'sanitize'    => 'price',
			),
			'_hamnavaz_currency'           => array(
				'section'     => 'lesson',
				'label'       => esc_html__( 'Currency Code', 'delnavazan-expansion' ),
				'description' => esc_html__( 'Three-letter code such as AUD, CAD, GBP, or USD.', 'delnavazan-expansion' ),
				'input_type'  => 'text',
				'meta_type'   => 'string',
				'sanitize'    => 'currency',
			),
			'_hamnavaz_price_type'         => array(
				'section'     => 'lesson',
				'label'       => esc_html__( 'Price Type', 'delnavazan-expansion' ),
				'description' => '',
				'input_type'  => 'select',
				'meta_type'   => 'string',
				'sanitize'    => 'enum',
				'default'     => 'contact',
				'options'     => array(
					'contact'    => esc_html__( 'Contact Teacher', 'delnavazan-expansion' ),
					'per_hour'   => esc_html__( 'Per Hour', 'delnavazan-expansion' ),
					'per_lesson' => esc_html__( 'Per Lesson', 'delnavazan-expansion' ),
					'from'       => esc_html__( 'From', 'delnavazan-expansion' ),
				),
			),
			'_hamnavaz_trial_lesson'       => array(
				'section'     => 'lesson',
				'label'       => esc_html__( 'Trial Lesson Available', 'delnavazan-expansion' ),
				'description' => '',
				'input_type'  => 'checkbox',
				'meta_type'   => 'boolean',
				'sanitize'    => 'boolean',
				'default'     => false,
			),
			'_hamnavaz_lesson_notes'       => array(
				'section'     => 'lesson',
				'label'       => esc_html__( 'Additional Lesson Information', 'delnavazan-expansion' ),
				'description' => '',
				'input_type'  => 'textarea',
				'meta_type'   => 'string',
				'sanitize'    => 'textarea',
				'rows'        => 4,
			),
			self::LISTING_STATUS_KEY       => array(
				'section'     => 'operational',
				'label'       => esc_html__( 'Listing Status', 'delnavazan-expansion' ),
				'description' => esc_html__( 'Controls the Hamnavaz lifecycle without deleting the teacher record.', 'delnavazan-expansion' ),
				'input_type'  => 'select',
				'meta_type'   => 'string',
				'sanitize'    => 'enum',
				'default'     => 'draft',
				'options'     => self::listing_status_options(),
			),
			self::VERIFICATION_STATUS_KEY  => array(
				'section'     => 'operational',
				'label'       => esc_html__( 'Verification Status', 'delnavazan-expansion' ),
				'description' => esc_html__( 'Manual Phase 2 state only; no verification automation runs yet.', 'delnavazan-expansion' ),
				'input_type'  => 'select',
				'meta_type'   => 'string',
				'sanitize'    => 'enum',
				'default'     => 'not_requested',
				'options'     => self::verification_status_options(),
			),
			self::LAST_VERIFIED_KEY        => array(
				'section'     => 'operational',
				'label'       => esc_html__( 'Last Verified', 'delnavazan-expansion' ),
				'description' => esc_html__( 'Saved as a Unix timestamp using the WordPress site timezone.', 'delnavazan-expansion' ),
				'input_type'  => 'datetime-local',
				'meta_type'   => 'integer',
				'sanitize'    => 'timestamp',
			),
			self::NEXT_VERIFICATION_KEY    => array(
				'section'     => 'operational',
				'label'       => esc_html__( 'Next Verification', 'delnavazan-expansion' ),
				'description' => esc_html__( 'Manual Phase 2 date; scheduling is deferred.', 'delnavazan-expansion' ),
				'input_type'  => 'datetime-local',
				'meta_type'   => 'integer',
				'sanitize'    => 'timestamp',
			),
			'_hamnavaz_internal_notes'     => array(
				'section'     => 'operational',
				'label'       => esc_html__( 'Internal Notes', 'delnavazan-expansion' ),
				'description' => esc_html__( 'Administrator/editorial notes only. Never expose publicly.', 'delnavazan-expansion' ),
				'input_type'  => 'textarea',
				'meta_type'   => 'string',
				'sanitize'    => 'textarea',
				'rows'        => 5,
			),
		);

		return $this->fields;
	}
}
