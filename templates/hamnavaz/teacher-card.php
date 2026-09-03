<?php
/**
 * Reusable Hamnavaz teacher card.
 *
 * @var array $hamnavaz_teacher Allowlisted teacher view model.
 *
 * @package DelnavazanExpansion
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $hamnavaz_teacher ) || ! is_array( $hamnavaz_teacher ) ) {
	return;
}

$availability = array();

if ( ! empty( $hamnavaz_teacher['availability']['in_person'] ) ) {
	$availability[] = __( 'In person', 'delnavazan-expansion' );
}

if ( ! empty( $hamnavaz_teacher['availability']['online'] ) ) {
	$availability[] = __( 'Online', 'delnavazan-expansion' );
}
?>
<article class="hamnavaz-card" dir="auto">
	<a class="hamnavaz-card__media" href="<?php echo esc_url( $hamnavaz_teacher['profile_url'] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'View profile for %s', 'delnavazan-expansion' ), $hamnavaz_teacher['name'] ) ); ?>">
		<?php if ( ! empty( $hamnavaz_teacher['image']['id'] ) ) : ?>
			<?php
			echo wp_get_attachment_image(
				$hamnavaz_teacher['image']['id'],
				'medium',
				false,
				array(
					'alt'     => $hamnavaz_teacher['image']['alt'],
					'class'   => 'hamnavaz-card__image',
					'loading' => 'lazy',
					'sizes'   => '(max-width: 430px) 35vw, 180px',
				)
			); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress generates escaped responsive image markup.
			?>
		<?php else : ?>
			<span class="hamnavaz-card__image-placeholder" aria-hidden="true"></span>
		<?php endif; ?>
	</a>

	<div class="hamnavaz-card__body">
		<h2 class="hamnavaz-card__name"><a href="<?php echo esc_url( $hamnavaz_teacher['profile_url'] ); ?>"><bdi><?php echo esc_html( $hamnavaz_teacher['name'] ); ?></bdi></a></h2>

		<?php if ( '' !== $hamnavaz_teacher['english_name'] ) : ?>
			<p class="hamnavaz-card__english-name" lang="en" dir="ltr"><bdi><?php echo esc_html( $hamnavaz_teacher['english_name'] ); ?></bdi></p>
		<?php endif; ?>

		<?php if ( ! empty( $hamnavaz_teacher['instruments'] ) ) : ?>
			<p class="hamnavaz-card__instruments"><?php echo esc_html( implode( ' · ', $hamnavaz_teacher['instruments'] ) ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $hamnavaz_teacher['location_summary'] ) : ?>
			<p class="hamnavaz-card__location"><bdi><?php echo esc_html( $hamnavaz_teacher['location_summary'] ); ?></bdi></p>
		<?php endif; ?>

		<?php if ( ! empty( $availability ) ) : ?>
			<p class="hamnavaz-card__availability"><?php echo esc_html( implode( ' · ', $availability ) ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $hamnavaz_teacher['lesson']['price'] ) : ?>
			<p class="hamnavaz-card__price"><bdi><?php echo esc_html( $hamnavaz_teacher['lesson']['price'] ); ?></bdi></p>
		<?php endif; ?>

		<a class="hamnavaz-button hamnavaz-button--secondary" href="<?php echo esc_url( $hamnavaz_teacher['profile_url'] ); ?>"><?php echo esc_html__( 'View profile', 'delnavazan-expansion' ); ?></a>
	</div>
</article>
