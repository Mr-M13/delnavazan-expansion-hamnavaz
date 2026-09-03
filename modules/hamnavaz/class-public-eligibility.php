<?php
/**
 * Hamnavaz public teacher eligibility.
 *
 * @package DelnavazanExpansion
 */

namespace Delnavazan\Expansion\Hamnavaz;

defined( 'ABSPATH' ) || exit;

/**
 * Defines the single public-visibility rule shared by profiles and future search.
 */
final class Public_Eligibility {

	/**
	 * Return listing states that remain publicly visible.
	 *
	 * Verification-due records remain listed until the canonical automation
	 * lifecycle moves them on hold.
	 *
	 * @return string[]
	 */
	public static function public_listing_statuses() {
		return array( 'active', 'verification_due' );
	}

	/**
	 * Determine whether a teacher may be presented to an anonymous visitor.
	 *
	 * @param int|\WP_Post $teacher Teacher post ID or object.
	 * @return bool
	 */
	public static function is_public( $teacher ) {
		$post = get_post( $teacher );

		if ( ! $post instanceof \WP_Post ) {
			return false;
		}

		if ( Teacher_Post_Type::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
			return false;
		}

		$listing_status = get_post_meta( $post->ID, Teacher_Meta::LISTING_STATUS_KEY, true );

		return in_array( $listing_status, self::public_listing_statuses(), true );
	}

	/**
	 * Return the metadata query fragment future public teacher queries must use.
	 *
	 * @return array
	 */
	public static function listing_meta_query() {
		return array(
			'key'     => Teacher_Meta::LISTING_STATUS_KEY,
			'value'   => self::public_listing_statuses(),
			'compare' => 'IN',
		);
	}
}
