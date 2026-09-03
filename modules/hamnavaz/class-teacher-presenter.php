<?php
/**
 * Hamnavaz public teacher presentation model.
 *
 * @package DelnavazanExpansion
 */

namespace Delnavazan\Expansion\Hamnavaz;

defined( 'ABSPATH' ) || exit;

/**
 * Converts one eligible teacher into an explicit, normalized public allowlist.
 */
final class Teacher_Presenter {

	/**
	 * Metadata keys approved for public presentation in Phase 3.
	 *
	 * Operational metadata is deliberately absent.
	 *
	 * @var string[]
	 */
	const PUBLIC_META_KEYS = array(
		'_hamnavaz_english_name',
		'_hamnavaz_specialisations',
		'_hamnavaz_teaching_levels',
		'_hamnavaz_in_person',
		'_hamnavaz_online',
		'_hamnavaz_state',
		'_hamnavaz_city',
		'_hamnavaz_suburb',
		'_hamnavaz_public_location',
		'_hamnavaz_whatsapp',
		'_hamnavaz_phone',
		'_hamnavaz_email',
		'_hamnavaz_website',
		'_hamnavaz_instagram',
		'_hamnavaz_youtube',
		'_hamnavaz_other_contact_url',
		'_hamnavaz_price',
		'_hamnavaz_currency',
		'_hamnavaz_price_type',
		'_hamnavaz_trial_lesson',
		'_hamnavaz_lesson_notes',
		'_hamnavaz_last_verified_at',
	);

	/**
	 * Build the public view model for one teacher.
	 *
	 * @param int|\WP_Post $teacher Teacher post ID or object.
	 * @return array|false Public view model, or false when ineligible.
	 */
	public function present( $teacher ) {
		$post = get_post( $teacher );

		if ( ! $post instanceof \WP_Post || ! Public_Eligibility::is_public( $post ) ) {
			return false;
		}

		$meta = $this->allowed_meta( $post->ID );
		$name = trim( wp_strip_all_tags( get_the_title( $post ), true ) );

		if ( '' === $name ) {
			return false;
		}

		$short_biography = trim( (string) $post->post_excerpt );

		if ( '' === $short_biography ) {
			$short_biography = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 36, '&hellip;' );
		}

		$profile_url = Public_Routing::profile_url( $post );

		if ( '' === $profile_url ) {
			return false;
		}

		return array(
			'name'               => $name,
			'english_name'       => $this->text( $meta['_hamnavaz_english_name'] ),
			'profile_url'        => $profile_url,
			'image'              => $this->image( $post, $name ),
			'short_biography'    => $this->textarea( $short_biography ),
			'full_biography'     => $this->biography( $post->post_content ),
			'instruments'        => $this->term_names( $post->ID, Teacher_Taxonomies::INSTRUMENT ),
			'languages'          => $this->term_names( $post->ID, Teacher_Taxonomies::LANGUAGE ),
			'country'            => $this->single_term_name( $post->ID, Teacher_Taxonomies::COUNTRY ),
			'specialisations'    => $this->textarea( $meta['_hamnavaz_specialisations'] ),
			'teaching_levels'    => $this->controlled_labels(
				$meta['_hamnavaz_teaching_levels'],
				array(
					'beginner'     => __( 'Beginner', 'delnavazan-expansion' ),
					'intermediate' => __( 'Intermediate', 'delnavazan-expansion' ),
					'advanced'     => __( 'Advanced', 'delnavazan-expansion' ),
					'professional' => __( 'Professional', 'delnavazan-expansion' ),
				)
			),
			'availability'       => array(
				'in_person' => $this->boolean( $meta['_hamnavaz_in_person'] ),
				'online'    => $this->boolean( $meta['_hamnavaz_online'] ),
			),
			'location'           => array(
				'state'       => $this->text( $meta['_hamnavaz_state'] ),
				'city'        => $this->text( $meta['_hamnavaz_city'] ),
				'suburb'      => $this->text( $meta['_hamnavaz_suburb'] ),
				'description' => $this->textarea( $meta['_hamnavaz_public_location'] ),
			),
			'location_summary'   => $this->location_summary( $meta, $post->ID ),
			'contacts'           => $this->contacts( $meta ),
			'lesson'             => array(
				'price'        => $this->price( $meta ),
				'trial_lesson' => $this->boolean( $meta['_hamnavaz_trial_lesson'] ),
				'notes'        => $this->textarea( $meta['_hamnavaz_lesson_notes'] ),
			),
			'last_confirmed'     => $this->last_confirmed( $meta['_hamnavaz_last_verified_at'] ),
			'meta_description'   => $this->meta_description( $name, $short_biography ),
		);
	}

	/**
	 * Fetch only metadata explicitly approved for public use.
	 *
	 * @param int $post_id Teacher post ID.
	 * @return array
	 */
	private function allowed_meta( $post_id ) {
		$meta = array();

		foreach ( self::PUBLIC_META_KEYS as $key ) {
			$meta[ $key ] = get_post_meta( $post_id, $key, true );
		}

		return $meta;
	}

	/**
	 * Normalize a plain text value.
	 *
	 * @param mixed $value Stored value.
	 * @return string
	 */
	private function text( $value ) {
		return is_scalar( $value ) ? trim( sanitize_text_field( (string) $value ) ) : '';
	}

	/**
	 * Normalize a public multiline value without retaining HTML.
	 *
	 * @param mixed $value Stored value.
	 * @return string
	 */
	private function textarea( $value ) {
		return is_scalar( $value ) ? trim( sanitize_textarea_field( (string) $value ) ) : '';
	}

	/**
	 * Render editorial biography content through WordPress, then constrain HTML.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	private function biography( $content ) {
		if ( '' === trim( $content ) ) {
			return '';
		}

		$content = preg_replace( '/<!--.*?(?:-->|$)/s', '', $content );

		if ( ! is_string( $content ) ) {
			return '';
		}

		$content = strip_shortcodes( $content );
		$content = wp_kses_post( $content );

		return wp_kses_post( wpautop( $content ) );
	}

	/**
	 * Prepare responsive profile image data.
	 *
	 * @param \WP_Post $post Teacher post.
	 * @param string   $name Public teacher name.
	 * @return array
	 */
	private function image( $post, $name ) {
		$image_id = get_post_thumbnail_id( $post );

		if ( ! $image_id ) {
			return array();
		}

		$stored_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		$alt        = is_scalar( $stored_alt ) ? trim( (string) $stored_alt ) : '';

		return array(
			'id'  => (int) $image_id,
			'alt' => '' !== $alt ? sanitize_text_field( $alt ) : $name,
		);
	}

	/**
	 * Return public taxonomy labels only.
	 *
	 * @param int    $post_id  Teacher post ID.
	 * @param string $taxonomy Taxonomy key.
	 * @return string[]
	 */
	private function term_names( $post_id, $taxonomy ) {
		$terms = wp_get_post_terms(
			$post_id,
			$taxonomy,
			array(
				'orderby' => 'name',
				'order'   => 'ASC',
			)
		);

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		return array_values(
			array_filter(
				array_map(
					function ( $term ) {
						return $term instanceof \WP_Term ? sanitize_text_field( $term->name ) : '';
					},
					$terms
				)
			)
		);
	}

	/**
	 * Return the sole public country label.
	 *
	 * @param int    $post_id  Teacher post ID.
	 * @param string $taxonomy Taxonomy key.
	 * @return string
	 */
	private function single_term_name( $post_id, $taxonomy ) {
		$names = $this->term_names( $post_id, $taxonomy );

		return isset( $names[0] ) ? $names[0] : '';
	}

	/**
	 * Map controlled internal values to public labels.
	 *
	 * @param mixed $stored Stored array.
	 * @param array $labels Approved label map.
	 * @return string[]
	 */
	private function controlled_labels( $stored, $labels ) {
		if ( ! is_array( $stored ) ) {
			return array();
		}

		$output = array();

		foreach ( $stored as $value ) {
			if ( is_scalar( $value ) && isset( $labels[ (string) $value ] ) ) {
				$output[] = $labels[ (string) $value ];
			}
		}

		return array_values( array_unique( $output ) );
	}

	/**
	 * Normalize a registered boolean value.
	 *
	 * @param mixed $value Stored value.
	 * @return bool
	 */
	private function boolean( $value ) {
		return in_array( $value, array( true, 1, '1' ), true );
	}

	/**
	 * Build the compact city/country display.
	 *
	 * @param array $meta    Allowlisted metadata.
	 * @param int   $post_id Teacher post ID.
	 * @return string
	 */
	private function location_summary( $meta, $post_id ) {
		$parts   = array();
		$city    = $this->text( $meta['_hamnavaz_city'] );
		$country = $this->single_term_name( $post_id, Teacher_Taxonomies::COUNTRY );

		if ( '' !== $city ) {
			$parts[] = $city;
		}

		if ( '' !== $country ) {
			$parts[] = $country;
		}

		return implode( ', ', $parts );
	}

	/**
	 * Build safe public contact actions.
	 *
	 * @param array $meta Allowlisted metadata.
	 * @return array[]
	 */
	private function contacts( $meta ) {
		$contacts = array();
		$whatsapp = $this->whatsapp_url( $meta['_hamnavaz_whatsapp'] );
		$phone    = $this->phone_url( $meta['_hamnavaz_phone'] );
		$email    = is_scalar( $meta['_hamnavaz_email'] ) ? sanitize_email( (string) $meta['_hamnavaz_email'] ) : '';

		if ( '' !== $whatsapp ) {
			$contacts[] = array(
				'label'    => __( 'WhatsApp', 'delnavazan-expansion' ),
				'url'      => $whatsapp,
				'external' => true,
			);
		}

		if ( '' !== $phone ) {
			$contacts[] = array(
				'label'    => __( 'Call', 'delnavazan-expansion' ),
				'url'      => $phone,
				'external' => false,
			);
		}

		if ( '' !== $email ) {
			$contacts[] = array(
				'label'    => __( 'Email', 'delnavazan-expansion' ),
				'url'      => 'mailto:' . $email,
				'external' => false,
			);
		}

		$external = array(
			'_hamnavaz_website'           => __( 'Website', 'delnavazan-expansion' ),
			'_hamnavaz_instagram'         => __( 'Instagram', 'delnavazan-expansion' ),
			'_hamnavaz_youtube'           => __( 'YouTube', 'delnavazan-expansion' ),
			'_hamnavaz_other_contact_url' => __( 'More information', 'delnavazan-expansion' ),
		);

		foreach ( $external as $key => $label ) {
			$url = $this->http_url( $meta[ $key ] );

			if ( '' !== $url ) {
				$contacts[] = array(
					'label'    => $label,
					'url'      => $url,
					'external' => true,
				);
			}
		}

		return $contacts;
	}

	/**
	 * Construct a WhatsApp click-to-chat URL from an international number.
	 *
	 * @param mixed $value Stored phone value.
	 * @return string
	 */
	private function whatsapp_url( $value ) {
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		$raw    = trim( (string) $value );
		$digits = preg_replace( '/\D+/', '', $raw );

		if ( 0 === strpos( $digits, '00' ) ) {
			$digits = substr( $digits, 2 );
		}

		if ( ! preg_match( '/^[1-9][0-9]{6,14}$/', $digits ) ) {
			return '';
		}

		return 'https://wa.me/' . $digits;
	}

	/**
	 * Construct a safe telephone URL.
	 *
	 * @param mixed $value Stored phone value.
	 * @return string
	 */
	private function phone_url( $value ) {
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		$raw      = trim( (string) $value );
		$has_plus = 0 === strpos( $raw, '+' );
		$digits   = preg_replace( '/\D+/', '', $raw );

		if ( ! preg_match( '/^[0-9]{5,20}$/', $digits ) ) {
			return '';
		}

		return 'tel:' . ( $has_plus ? '+' : '' ) . $digits;
	}

	/**
	 * Accept only absolute HTTP(S) contact URLs.
	 *
	 * @param mixed $value Stored URL.
	 * @return string
	 */
	private function http_url( $value ) {
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		$url    = esc_url_raw( trim( (string) $value ), array( 'http', 'https' ) );
		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );

		return in_array( $scheme, array( 'http', 'https' ), true ) ? $url : '';
	}

	/**
	 * Format the informational lesson price.
	 *
	 * @param array $meta Allowlisted metadata.
	 * @return string
	 */
	private function price( $meta ) {
		$type_labels = array(
			'contact'    => __( 'Contact teacher', 'delnavazan-expansion' ),
			'per_hour'   => __( 'per hour', 'delnavazan-expansion' ),
			'per_lesson' => __( 'per lesson', 'delnavazan-expansion' ),
			'from'       => __( 'from', 'delnavazan-expansion' ),
		);
		$type        = is_scalar( $meta['_hamnavaz_price_type'] ) ? (string) $meta['_hamnavaz_price_type'] : '';
		$price       = is_scalar( $meta['_hamnavaz_price'] ) ? trim( (string) $meta['_hamnavaz_price'] ) : '';
		$currency    = is_scalar( $meta['_hamnavaz_currency'] ) ? strtoupper( trim( (string) $meta['_hamnavaz_currency'] ) ) : '';

		if ( ! preg_match( '/^[A-Z]{3}$/', $currency ) ) {
			$currency = '';
		}

		if ( 'contact' === $type || '' === $price || ! is_numeric( $price ) || (float) $price < 0 ) {
			return isset( $type_labels[ $type ] ) && 'contact' === $type ? $type_labels[ $type ] : '';
		}

		$amount = number_format_i18n( (float) $price, floor( (float) $price ) === (float) $price ? 0 : 2 );
		$parts  = array_filter( array( $currency, $amount ) );
		$output = implode( ' ', $parts );

		if ( 'from' === $type ) {
			return $type_labels['from'] . ' ' . $output;
		}

		return isset( $type_labels[ $type ] ) ? $output . ' ' . $type_labels[ $type ] : $output;
	}

	/**
	 * Format the public information-confirmed date without exposing raw state.
	 *
	 * @param mixed $value Stored timestamp.
	 * @return string
	 */
	private function last_confirmed( $value ) {
		$timestamp = is_scalar( $value ) ? absint( $value ) : 0;

		return $timestamp > 0 ? wp_date( get_option( 'date_format' ), $timestamp ) : '';
	}

	/**
	 * Build a compact and safe meta description.
	 *
	 * @param string $name             Teacher name.
	 * @param string $short_biography Short biography.
	 * @return string
	 */
	private function meta_description( $name, $short_biography ) {
		$description = '' !== trim( $short_biography )
			? $short_biography
			: sprintf(
				/* translators: %s: teacher name. */
				__( 'Learn about %s, an independent music teacher listed on Hamnavaz.', 'delnavazan-expansion' ),
				$name
			);

		return wp_html_excerpt( wp_strip_all_tags( $description ), 155, '&hellip;' );
	}
}
