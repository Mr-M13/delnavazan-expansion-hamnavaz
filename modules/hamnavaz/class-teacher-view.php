<?php
/**
 * Hamnavaz public teacher template renderer.
 *
 * @package DelnavazanExpansion
 */

namespace Delnavazan\Expansion\Hamnavaz;

defined( 'ABSPATH' ) || exit;

/**
 * Renders plugin-owned profile and reusable card templates.
 */
final class Teacher_View {

	/**
	 * Render a reusable teacher card from a teacher record.
	 *
	 * @param int|\WP_Post $teacher Teacher post ID or object.
	 * @param bool         $echo    Whether to print the result.
	 * @return string
	 */
	public static function render_card( $teacher, $echo = true ) {
		$presenter = new Teacher_Presenter();
		$view      = $presenter->present( $teacher );

		if ( false === $view ) {
			return '';
		}

		wp_enqueue_style(
			'delnavazan-expansion-hamnavaz-public',
			DELNAVAZAN_EXPANSION_URL . 'assets/css/hamnavaz-public.css',
			array(),
			DELNAVAZAN_EXPANSION_VERSION
		);

		ob_start();
		$hamnavaz_teacher = $view;
		include DELNAVAZAN_EXPANSION_PATH . 'templates/hamnavaz/teacher-card.php';
		$html = (string) ob_get_clean();

		if ( $echo ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by the owned template.
		}

		return $html;
	}
}
