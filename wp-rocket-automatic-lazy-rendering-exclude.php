<?php
/**
 * Plugin Name: WP Rocket | Dynamic Popup Compatibility
 * Description: Prevents WP Rocket Automatic Lazy Rendering and Remove Unused CSS from breaking Popup Builder and Fancybox video popups.
 * Author:      Nishan
 * Author URI:  https://wp-rocket.me/
 * License:     GNU General Public License v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Version:     1.2.3
 */

namespace WP_Rocket\Helpers\alr_exclude;

defined( 'ABSPATH' ) || exit;

/**
 * Exclude selected elements from WP Rocket Automatic Lazy Rendering.
 *
 * @param array $exclusions Existing exclusions.
 * @return array
 */
function wpr_alr_exclusions( $exclusions ) {

	if ( ! is_array( $exclusions ) ) {
		$exclusions = array();
	}

	/*
	 * Above-the-fold hero video.
	 */
	$exclusions[] = 'class="th-hero-wrapper hero-13 nm-video-hero"';
	$exclusions[] = 'nm-video-hero';
	$exclusions[] = 'nm-video-hero-media';
	$exclusions[] = 'nm-video-hero-player';

	/*
	 * Existing BTI business section exclusion.
	 */
	$exclusions[] = 'class="container nm-business-section"';

	/*
	 * Popup Builder elements.
	 */
	$exclusions[] = 'sg-popup-builder-content';
	$exclusions[] = 'sgpb-popup-builder-content-html';
	$exclusions[] = 'sgpb-main-popup-data-container-';
	$exclusions[] = 'nm-schedule-popup__content';

	return array_values( array_unique( $exclusions ) );
}

add_filter(
	'rocket_lrc_exclusions',
	__NAMESPACE__ . '\\wpr_alr_exclusions',
	10,
	1
);

/**
 * Safelist Fancybox's dynamically generated CSS selectors from
 * WP Rocket Remove Unused CSS.
 *
 * Fancybox creates classes such as "has-youtube" only after a
 * video popup is opened. These classes are not present during
 * WP Rocket's initial Used CSS scan.
 *
 * @param array $safelist Existing Remove Unused CSS safelist.
 * @return array
 */
function bti_rucss_safelist( $safelist ) {

	if ( ! is_array( $safelist ) ) {
		$safelist = array();
	}

	/*
	 * Retain Fancybox's complete stylesheet.
	 *
	 * This matches URLs such as:
	 * /npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css
	 * /wp-content/cache/min/.../fancybox/fancybox.css
	 */
	$safelist[] = '(.*)fancybox/fancybox.css';

	/*
	 * Retain all Fancybox base and dynamically generated selectors.
	 */
	$safelist[] = '(.*)fancybox(.*)';

	/*
	 * Retain video and iframe state selectors added after interaction.
	 */
	$safelist[] = '(.*)has-youtube(.*)';
	$safelist[] = '(.*)has-vimeo(.*)';
	$safelist[] = '(.*)has-html5video(.*)';
	$safelist[] = '(.*)has-iframe(.*)';

	return array_values( array_unique( $safelist ) );
}

add_filter(
	'rocket_rucss_safelist',
	__NAMESPACE__ . '\\bti_rucss_safelist',
	20,
	1
);

/**
 * Add fallback CSS for Popup Builder and Fancybox videos.
 *
 * The Fancybox rules also override an incorrect small inline width,
 * such as width: 354.9px, while retaining the 16:9 video ratio.
 *
 * @return void
 */
function bti_dynamic_popup_fallback_css() {
	?>
	<style id="bti-dynamic-popup-compatibility">

		/*
		 * Hero video Automatic Lazy Rendering fallback.
		 */
		.nm-video-hero[data-wpr-lazyrender],
		.nm-video-hero-media[data-wpr-lazyrender],
		.nm-video-hero-player[data-wpr-lazyrender] {
			content-visibility: visible !important;
			contain-intrinsic-size: auto !important;
		}

		/*
		 * Popup Builder Automatic Lazy Rendering fallback.
		 */
		[class*="sgpb-main-popup-data-container-"][data-wpr-lazyrender],
		.sg-popup-builder-content[data-wpr-lazyrender],
		.sgpb-popup-builder-content-html[data-wpr-lazyrender] {
			content-visibility: visible !important;
		}

		@media (max-width: 767px) {
			.nm-properties-form-popup{
				width: 100% !important;
			}
		}
		
		/*
		 * Fancybox video popup size fallback.
		 */
		.has-youtube .fancybox__content,
		.has-vimeo .fancybox__content,
		.has-html5video .fancybox__content,
		.has-iframe .fancybox__content {
			box-sizing: border-box !important;
			width: min(960px, calc(100vw - 32px)) !important;
			max-width: calc(100vw - 32px) !important;
			height: auto !important;
			aspect-ratio: 16 / 9 !important;
			padding: 0 !important;
			overflow: hidden !important;
			background: #000 !important;
		}

		.has-youtube .fancybox__iframe,
		.has-vimeo .fancybox__iframe,
		.has-html5video .fancybox__iframe,
		.has-iframe .fancybox__iframe {
			display: block !important;
			width: 100% !important;
			height: 100% !important;
			border: 0 !important;
		}

	</style>
	<?php
}

add_action(
	'wp_head',
	__NAMESPACE__ . '\\bti_dynamic_popup_fallback_css',
	100
);

/**
 * Disable Automatic Lazy Rendering only on single BTI property pages.
 *
 * The check runs on the `wp` hook, after WordPress has prepared the main
 * query, so conditional tags such as is_singular() are safe to use.
 *
 * @return void
 */
// function bti_disable_alr_on_single_property() {
// 	if ( ! is_singular( 'bti_properties' ) ) {
// 		return;
// 	}

// 	add_filter( 'rocket_lrc_optimization', '__return_false', PHP_INT_MAX );
// }

// add_action(
// 	'wp',
// 	__NAMESPACE__ . '\\bti_disable_alr_on_single_property',
// 	1
// );

