<?php
/**
 * Hamnavaz controlled public teacher routing.
 *
 * @package DelnavazanExpansion
 */

namespace Delnavazan\Expansion\Hamnavaz;

defined( 'ABSPATH' ) || exit;

/**
 * Owns the public profile route without making the teacher post type public.
 */
final class Public_Routing {

	/**
	 * Internal public-route query variable.
	 *
	 * @var string
	 */
	const QUERY_VAR = 'hamnavaz_teacher_profile';

	/**
	 * Resolved public teacher view model.
	 *
	 * @var array|false|null
	 */
	private $teacher = null;

	/**
	 * Current profile model made available to the owned template.
	 *
	 * @var array|false|null
	 */
	private static $current_teacher = null;

	/**
	 * Whether the current request targets the controlled route.
	 *
	 * @var bool
	 */
	private $is_profile_request = false;

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( __CLASS__, 'register_rewrite_rule' ), 9 );
		add_filter( 'query_vars', array( $this, 'register_query_var' ) );
		add_action( 'template_redirect', array( $this, 'resolve_request' ), 1 );
		add_filter( 'template_include', array( $this, 'template_include' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'pre_get_document_title', array( $this, 'document_title' ) );
		add_filter( 'rank_math/frontend/title', array( $this, 'document_title' ) );
		add_filter( 'rank_math/frontend/description', array( $this, 'rank_math_description' ) );
		add_filter( 'rank_math/frontend/canonical', array( $this, 'suppress_rank_math_canonical' ) );
		add_action( 'wp_head', array( $this, 'fallback_seo_meta' ), 2 );
		add_filter( 'body_class', array( $this, 'body_class' ) );
	}

	/**
	 * Register the plugin-owned teacher profile rewrite.
	 *
	 * @return void
	 */
	public static function register_rewrite_rule() {
		add_rewrite_rule(
			'^hamnavaz/teacher/([^/]+)/?$',
			'index.php?' . self::QUERY_VAR . '=$matches[1]',
			'top'
		);
	}

	/**
	 * Build one teacher's canonical profile URL.
	 *
	 * @param int|\WP_Post $teacher Teacher post ID or object.
	 * @return string
	 */
	public static function profile_url( $teacher ) {
		$post = get_post( $teacher );

		if ( ! $post instanceof \WP_Post || Teacher_Post_Type::POST_TYPE !== $post->post_type || '' === $post->post_name ) {
			return '';
		}

		$path = 'hamnavaz/teacher/' . $post->post_name;

		return home_url( user_trailingslashit( $path, 'single' ) );
	}

	/**
	 * Return the already-authorized model for the current profile template.
	 *
	 * @return array|false|null
	 */
	public static function current_teacher() {
		return self::$current_teacher;
	}

	/**
	 * Add the controlled route query variable.
	 *
	 * @param string[] $query_vars Public query variables.
	 * @return string[]
	 */
	public function register_query_var( $query_vars ) {
		$query_vars[] = self::QUERY_VAR;

		return $query_vars;
	}

	/**
	 * Resolve, authorize, and canonicalize a profile request.
	 *
	 * @return void
	 */
	public function resolve_request() {
		$raw_slug = get_query_var( self::QUERY_VAR, '' );

		if ( ! is_scalar( $raw_slug ) || '' === (string) $raw_slug ) {
			return;
		}

		$this->is_profile_request = true;
		$slug                     = sanitize_title( rawurldecode( wp_unslash( (string) $raw_slug ) ) );

		if ( '' === $slug ) {
			$this->set_404();
			return;
		}

		$post = get_page_by_path( $slug, OBJECT, Teacher_Post_Type::POST_TYPE );

		if ( ! $post instanceof \WP_Post || ! Public_Eligibility::is_public( $post ) ) {
			$this->set_404();
			return;
		}

		$presenter    = new Teacher_Presenter();
		$this->teacher = $presenter->present( $post );

		if ( false === $this->teacher ) {
			$this->set_404();
			return;
		}

		self::$current_teacher = $this->teacher;

		global $wp_query;

		if ( $wp_query instanceof \WP_Query ) {
			$wp_query->is_404 = false;
		}

		$canonical = $this->teacher['profile_url'];

		if ( $this->needs_canonical_redirect( $canonical ) ) {
			wp_safe_redirect( $canonical, 301, 'Delnavazan Expansion' );
			exit;
		}

		status_header( 200 );
	}

	/**
	 * Select the plugin profile template or the site's ordinary 404 template.
	 *
	 * @param string $template Template selected by WordPress.
	 * @return string
	 */
	public function template_include( $template ) {
		if ( ! $this->is_profile_request ) {
			return $template;
		}

		if ( false === $this->teacher || null === $this->teacher ) {
			$not_found = get_404_template();

			return '' !== $not_found ? $not_found : $template;
		}

		$profile_template = DELNAVAZAN_EXPANSION_PATH . 'templates/hamnavaz/teacher-profile.php';

		return file_exists( $profile_template ) ? $profile_template : $template;
	}

	/**
	 * Enqueue the lightweight public stylesheet only for an eligible profile.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! is_array( $this->teacher ) ) {
			return;
		}

		wp_enqueue_style(
			'delnavazan-expansion-hamnavaz-public',
			DELNAVAZAN_EXPANSION_URL . 'assets/css/hamnavaz-public.css',
			array(),
			DELNAVAZAN_EXPANSION_VERSION
		);
	}

	/**
	 * Provide a meaningful title to WordPress and Rank Math.
	 *
	 * @param string $title Existing title.
	 * @return string
	 */
	public function document_title( $title ) {
		if ( ! is_array( $this->teacher ) ) {
			return $title;
		}

		return sprintf(
			/* translators: %s: teacher name. */
			__( '%s | Hamnavaz', 'delnavazan-expansion' ),
			$this->teacher['name']
		);
	}

	/**
	 * Supply the profile description through Rank Math's supported filter.
	 *
	 * @param string $description Existing description.
	 * @return string
	 */
	public function rank_math_description( $description ) {
		return is_array( $this->teacher ) ? $this->teacher['meta_description'] : $description;
	}

	/**
	 * Prevent Rank Math from emitting a competing canonical on the custom route.
	 *
	 * The route prints its canonical directly because Rank Math does not emit one
	 * when no native public queried object exists.
	 *
	 * @param string $canonical Existing canonical URL.
	 * @return string|false
	 */
	public function suppress_rank_math_canonical( $canonical ) {
		return is_array( $this->teacher ) ? false : $canonical;
	}

	/**
	 * Output the route-owned canonical and a description fallback.
	 *
	 * @return void
	 */
	public function fallback_seo_meta() {
		if ( ! is_array( $this->teacher ) ) {
			return;
		}

		if ( ! defined( 'RANK_MATH_VERSION' ) ) {
			printf(
				"\n<meta name=\"description\" content=\"%s\" />\n",
				esc_attr( $this->teacher['meta_description'] )
			);
		}

		printf( "\n<link rel=\"canonical\" href=\"%s\" />\n", esc_url( $this->teacher['profile_url'] ) );
	}

	/**
	 * Add a narrowly scoped body class for theme-compatible styling.
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public function body_class( $classes ) {
		if ( is_array( $this->teacher ) ) {
			$classes[] = 'hamnavaz-teacher-profile';
		}

		return $classes;
	}

	/**
	 * Mark the route unavailable without revealing why.
	 *
	 * @return void
	 */
	private function set_404() {
		global $wp_query;

		$this->teacher = false;
		self::$current_teacher = false;

		if ( $wp_query instanceof \WP_Query ) {
			$wp_query->set_404();
		}

		status_header( 404 );
		nocache_headers();
	}

	/**
	 * Determine whether this request should redirect to its canonical path.
	 *
	 * @param string $canonical Canonical profile URL.
	 * @return bool
	 */
	private function needs_canonical_redirect( $canonical ) {
		if ( '' === $canonical || ! isset( $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'] ) ) {
			return false;
		}

		$method = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) );

		if ( ! in_array( $method, array( 'GET', 'HEAD' ), true ) ) {
			return false;
		}

		$request_path   = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
		$canonical_path = wp_parse_url( $canonical, PHP_URL_PATH );

		if ( ! is_string( $request_path ) || ! is_string( $canonical_path ) ) {
			return false;
		}

		$normalize = function ( $path ) {
			return untrailingslashit( rawurldecode( $path ) );
		};

		return $normalize( $request_path ) !== $normalize( $canonical_path );
	}
}
