<?php
/**
 * Controlled Hamnavaz teacher profile template.
 *
 * @package DelnavazanExpansion
 */

defined( 'ABSPATH' ) || exit;

$hamnavaz_teacher = \Delnavazan\Expansion\Hamnavaz\Public_Routing::current_teacher();

if ( ! is_array( $hamnavaz_teacher ) ) {
	return;
}

$has_location_details = '' !== $hamnavaz_teacher['location']['state']
	|| '' !== $hamnavaz_teacher['location']['suburb']
	|| '' !== $hamnavaz_teacher['location']['description'];
$has_availability = ! empty( $hamnavaz_teacher['availability']['in_person'] )
	|| ! empty( $hamnavaz_teacher['availability']['online'] );
$has_teaching_details = ! empty( $hamnavaz_teacher['teaching_levels'] ) || $has_availability;
$has_lesson_details = '' !== $hamnavaz_teacher['lesson']['price']
	|| ! empty( $hamnavaz_teacher['lesson']['trial_lesson'] )
	|| '' !== $hamnavaz_teacher['lesson']['notes'];

get_header();
?>
<div id="primary" class="hamnavaz-profile" dir="auto">
	<div class="hamnavaz-profile__container">
		<nav class="hamnavaz-profile__breadcrumb" aria-label="<?php echo esc_attr__( 'Breadcrumb', 'delnavazan-expansion' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html__( 'Home', 'delnavazan-expansion' ); ?></a>
			<span aria-hidden="true">/</span>
			<span><?php echo esc_html__( 'Teacher profile', 'delnavazan-expansion' ); ?></span>
		</nav>

		<header class="hamnavaz-profile__hero">
			<div class="hamnavaz-profile__photo-wrap">
				<?php if ( ! empty( $hamnavaz_teacher['image']['id'] ) ) : ?>
					<?php
					echo wp_get_attachment_image(
						$hamnavaz_teacher['image']['id'],
						'large',
						false,
						array(
							'alt'           => $hamnavaz_teacher['image']['alt'],
							'class'         => 'hamnavaz-profile__photo',
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'sizes'         => '(max-width: 720px) 100vw, 420px',
						)
					); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress generates escaped responsive image markup.
					?>
				<?php else : ?>
					<div class="hamnavaz-profile__photo-placeholder" aria-hidden="true"></div>
				<?php endif; ?>
			</div>

			<div class="hamnavaz-profile__identity">
				<p class="hamnavaz-profile__eyebrow"><?php echo esc_html__( 'Hamnavaz teacher', 'delnavazan-expansion' ); ?></p>
				<h1><bdi><?php echo esc_html( $hamnavaz_teacher['name'] ); ?></bdi></h1>

				<?php if ( '' !== $hamnavaz_teacher['english_name'] ) : ?>
					<p class="hamnavaz-profile__english-name" lang="en" dir="ltr"><bdi><?php echo esc_html( $hamnavaz_teacher['english_name'] ); ?></bdi></p>
				<?php endif; ?>

				<?php if ( ! empty( $hamnavaz_teacher['instruments'] ) ) : ?>
					<p class="hamnavaz-profile__instruments"><?php echo esc_html( implode( ' · ', $hamnavaz_teacher['instruments'] ) ); ?></p>
				<?php endif; ?>

				<?php if ( '' !== $hamnavaz_teacher['location_summary'] ) : ?>
					<p class="hamnavaz-profile__location"><bdi><?php echo esc_html( $hamnavaz_teacher['location_summary'] ); ?></bdi></p>
				<?php endif; ?>

				<?php if ( '' !== $hamnavaz_teacher['short_biography'] ) : ?>
					<p class="hamnavaz-profile__intro"><?php echo esc_html( $hamnavaz_teacher['short_biography'] ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $hamnavaz_teacher['contacts'] ) ) : ?>
					<div class="hamnavaz-profile__primary-actions">
						<?php foreach ( $hamnavaz_teacher['contacts'] as $contact ) : ?>
							<?php
							$contact_label = ! empty( $contact['external'] )
								? sprintf(
									/* translators: %s: contact-action label. */
									__( '%s (opens in a new tab)', 'delnavazan-expansion' ),
									$contact['label']
								)
								: $contact['label'];
							?>
							<a class="hamnavaz-button" href="<?php echo esc_url( $contact['url'], array( 'http', 'https', 'mailto', 'tel' ) ); ?>" aria-label="<?php echo esc_attr( $contact_label ); ?>"<?php echo ! empty( $contact['external'] ) ? ' target="_blank" rel="external noopener noreferrer"' : ''; ?>><?php echo esc_html( $contact['label'] ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</header>

		<div class="hamnavaz-profile__content">
			<?php if ( '' !== $hamnavaz_teacher['full_biography'] ) : ?>
				<section class="hamnavaz-profile__section" aria-labelledby="hamnavaz-about-heading">
					<h2 id="hamnavaz-about-heading"><?php echo esc_html__( 'About', 'delnavazan-expansion' ); ?></h2>
					<div class="hamnavaz-profile__prose"><?php echo wp_kses_post( $hamnavaz_teacher['full_biography'] ); ?></div>
				</section>
			<?php endif; ?>

			<?php if ( '' !== $hamnavaz_teacher['specialisations'] || $has_teaching_details ) : ?>
				<section class="hamnavaz-profile__section" aria-labelledby="hamnavaz-teaching-heading">
					<h2 id="hamnavaz-teaching-heading"><?php echo esc_html__( 'Teaching', 'delnavazan-expansion' ); ?></h2>

					<?php if ( '' !== $hamnavaz_teacher['specialisations'] ) : ?>
						<p><?php echo nl2br( esc_html( $hamnavaz_teacher['specialisations'] ) ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $hamnavaz_teacher['teaching_levels'] ) ) : ?>
						<h3><?php echo esc_html__( 'Teaching levels', 'delnavazan-expansion' ); ?></h3>
						<p><?php echo esc_html( implode( ' · ', $hamnavaz_teacher['teaching_levels'] ) ); ?></p>
					<?php endif; ?>

					<?php if ( $has_availability ) : ?>
						<h3><?php echo esc_html__( 'Lesson availability', 'delnavazan-expansion' ); ?></h3>
						<ul class="hamnavaz-profile__list">
							<?php if ( ! empty( $hamnavaz_teacher['availability']['in_person'] ) ) : ?><li><?php echo esc_html__( 'In-person tuition available', 'delnavazan-expansion' ); ?></li><?php endif; ?>
							<?php if ( ! empty( $hamnavaz_teacher['availability']['online'] ) ) : ?><li><?php echo esc_html__( 'Online tuition available', 'delnavazan-expansion' ); ?></li><?php endif; ?>
						</ul>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $hamnavaz_teacher['languages'] ) ) : ?>
				<section class="hamnavaz-profile__section" aria-labelledby="hamnavaz-languages-heading">
					<h2 id="hamnavaz-languages-heading"><?php echo esc_html__( 'Languages', 'delnavazan-expansion' ); ?></h2>
					<p><?php echo esc_html( implode( ' · ', $hamnavaz_teacher['languages'] ) ); ?></p>
				</section>
			<?php endif; ?>

			<?php if ( $has_location_details ) : ?>
				<section class="hamnavaz-profile__section" aria-labelledby="hamnavaz-location-heading">
					<h2 id="hamnavaz-location-heading"><?php echo esc_html__( 'Location', 'delnavazan-expansion' ); ?></h2>
					<?php if ( '' !== $hamnavaz_teacher['location']['suburb'] ) : ?><p><strong><?php echo esc_html__( 'Area:', 'delnavazan-expansion' ); ?></strong> <bdi><?php echo esc_html( $hamnavaz_teacher['location']['suburb'] ); ?></bdi></p><?php endif; ?>
					<?php if ( '' !== $hamnavaz_teacher['location']['state'] ) : ?><p><strong><?php echo esc_html__( 'State / province:', 'delnavazan-expansion' ); ?></strong> <bdi><?php echo esc_html( $hamnavaz_teacher['location']['state'] ); ?></bdi></p><?php endif; ?>
					<?php if ( '' !== $hamnavaz_teacher['location']['description'] ) : ?><p><?php echo nl2br( esc_html( $hamnavaz_teacher['location']['description'] ) ); ?></p><?php endif; ?>
				</section>
			<?php endif; ?>

			<?php if ( $has_lesson_details ) : ?>
				<section class="hamnavaz-profile__section" aria-labelledby="hamnavaz-lessons-heading">
					<h2 id="hamnavaz-lessons-heading"><?php echo esc_html__( 'Lesson information', 'delnavazan-expansion' ); ?></h2>
					<?php if ( '' !== $hamnavaz_teacher['lesson']['price'] ) : ?><p><strong><?php echo esc_html__( 'Indicative price:', 'delnavazan-expansion' ); ?></strong> <bdi><?php echo esc_html( $hamnavaz_teacher['lesson']['price'] ); ?></bdi></p><?php endif; ?>
					<?php if ( ! empty( $hamnavaz_teacher['lesson']['trial_lesson'] ) ) : ?><p><?php echo esc_html__( 'Trial lesson available', 'delnavazan-expansion' ); ?></p><?php endif; ?>
					<?php if ( '' !== $hamnavaz_teacher['lesson']['notes'] ) : ?><p><?php echo nl2br( esc_html( $hamnavaz_teacher['lesson']['notes'] ) ); ?></p><?php endif; ?>
				</section>
			<?php endif; ?>

			<?php if ( '' !== $hamnavaz_teacher['last_confirmed'] ) : ?>
				<p class="hamnavaz-profile__freshness"><strong><?php echo esc_html__( 'Information last confirmed:', 'delnavazan-expansion' ); ?></strong> <bdi><?php echo esc_html( $hamnavaz_teacher['last_confirmed'] ); ?></bdi></p>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
get_footer();
