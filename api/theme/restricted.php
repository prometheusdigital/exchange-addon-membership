<?php
/**
 * Restricted class for THEME API in Membership Add-on
 *
 * @since 1.0.0
*/

class IT_Theme_API_Restricted implements IT_Theme_API {
	
	/**
	 * API context
	 * @var string $_context
	 * @since 1.0.0
	*/
	private $_context = 'restricted';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 1.0.0
	*/
	public $_tag_map = array(
		'content' => 'content',
		'excerpt' => 'excerpt',
		'product' => 'product',
	);

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	*/
	function __construct() {
	}

	/**
	 * Deprecated Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Restricted() {
		self::__construct();
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since 1.0.0
	 * 
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * @since 1.0.0
	 * @return string
	*/
	function content( $options=array() ) {
		global $post;
		
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		$defaults = array(
			'before' => '',
			'after'  => '',
			'message' => $membership_settings['membership-restricted-content-message'],
			'class'  => 'it-exchange-membership-restricted-content',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		$content = $options['before'];
				
		if ( $membership_settings['membership-restricted-show-excerpt'] ) {
			if ( !empty( $post->post_excerpt ) ) {
				$excerpt = $post->post_excerpt;
			} else if ( !empty( $post->post_content ) ) {
				$excerpt = $post->post_content;
                $excerpt = str_replace(']]>', ']]&gt;', $excerpt);
                $excerpt_length = apply_filters('excerpt_length', 55);
                $excerpt_more = apply_filters('excerpt_more', ' ' . '[&hellip;]');
                $excerpt = wp_trim_words( $excerpt, $excerpt_length, $excerpt_more );
			}
			if ( !empty( $excerpt ) ) {
				$excerpt = wp_trim_excerpt( $excerpt );
			} else {
				$excerpt = '';
			}
			$content .= '<p class="it-exchange-membership-content-excerpt">';
			$content .= $excerpt;
			$content .= '</p>';
		}
		
		$content .= '<p class="' . $options['class'] . '">' . $options['message'] . '</p>';
		$content .= $options['after'];
		
		return $content;
	}

	/**
	 * @since 1.0.0
	 * @return string
	*/
	function excerpt( $options=array() ) {
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		$defaults = array(
			'before'  => '',
			'after'   => '',
			'message' => $membership_settings['membership-restricted-content-message'],
			'class'   => 'it-exchange-membership-restricted-excerpt',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		$content = $options['before'];
				
		if ( $membership_settings['membership-restricted-show-excerpt'] ) {
			if ( !empty( $post->post_excerpt ) ) {
				$excerpt = $post->post_excerpt;
			} else if ( !empty( $post->post_content ) ) {
				$excerpt = $post->post_content;
                $excerpt = str_replace(']]>', ']]&gt;', $excerpt);
                $excerpt_length = apply_filters('excerpt_length', 55);
                $excerpt_more = apply_filters('excerpt_more', ' ' . '[&hellip;]');
                $excerpt = wp_trim_words( $excerpt, $excerpt_length, $excerpt_more );
			}
			if ( !empty( $excerpt ) ) {
				$excerpt = wp_trim_excerpt( $excerpt );
			} else {
				$excerpt = '';
			}
			$content .= '<p class="it-exchange-membership-content-excerpt">';
			$content .= $excerpt;
			$content .= '</p>';
		}
		
		$content .= '<p class="' . $options['class'] . '">' . $options['message'] . '</p>';
		$content .= $options['after'];
		
		return $content;
	}

	/**
	 * @since 1.0.0
	 * @return string
	*/
	function product( $options=array() ) {
		global $post;
		
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		$defaults = array(
			'before' => '',
			'after'  => '',
			'message' => $membership_settings['membership-restricted-product-message'],
			'class'  => 'it-exchange-membership-restricted-product',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		$content = $options['before'];
		$content .= '<p class="' . $options['class'] . '">' . $options['message'] . '</p>';
		$content .= $options['after'];
		
		return $content;
	}
}
