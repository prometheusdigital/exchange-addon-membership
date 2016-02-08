<?php
/**
 * Exchange will build your add-on's settings page for you and link to it from our add-on
 * screen. You are free to link from it elsewhere as well if you'd like... or to not use our API
 * at all. This file has all the functions related to registering the page, printing the form, and saving
 * the options. This includes the wizard settings. Additionally, we use the Exchange storage API to
 * save / retreive options. Add-ons are not required to do this.
 */

/**
 * This is the function registered in the options array when it_exchange_register_addon
 * was called for recurring payments
 *
 * It tells Exchange where to find the settings page
 *
 * @return void
 */
function it_exchange_membership_addon_settings_callback() {
	$IT_Exchange_Membership_Add_On = new IT_Exchange_Membership_Add_On();
	$IT_Exchange_Membership_Add_On->print_settings_page();
}

/**
 * Default settings for recurring payments
 *
 * @since 1.0.0
 *
 * @param array $values
 *
 * @return array
 */
function it_exchange_membership_addon_default_settings( $values ) {
	$defaults = array(
		'membership-restricted-show-excerpt' => false,
		'restricted-content-message'         => __( 'This content is for members only. Become a member now to get access to this and other awesome members-only content.', 'LION' ),
		'dripped-content-message'            => __( 'This content will be available in %d days.', 'LION' ),
		'restricted-product-message'         => __( 'This product is for members only. Become a member now to get access to this and other awesome members-only product.', 'LION' ),
		'dripped-product-message'            => __( 'This product will be available in %d days.', 'LION' ),
		'membership-prerequisites-label'     => __( 'Prerequisites', 'LION' ),
		'membership-intended-audience-label' => __( 'Intended Audience', 'LION' ),
		'membership-objectives-label'        => __( 'Objectives', 'LION' ),
		'memberships-group-toggle'           => true,
		'memberships-dashboard-view'         => 'grid',

	);
	$values   = ITUtility::merge_defaults( $values, $defaults );

	return $values;
}

add_filter( 'it_storage_get_defaults_exchange_addon_membership', 'it_exchange_membership_addon_default_settings' );

/**
 * Class for Recurring Payments
 * @since 1.0.0
 */
class IT_Exchange_Membership_Add_On {

	/**
	 * @var boolean $_is_admin true or false
	 * @since 1.0.0
	 */
	var $_is_admin;

	/**
	 * @var string $_current_page Current $_GET['page'] value
	 * @since 1.0.0
	 */
	var $_current_page;

	/**
	 * @var string $_current_add_on Current $_GET['add-on-settings'] value
	 * @since 1.0.0
	 */
	var $_current_add_on;

	/**
	 * @var string $status_message will be displayed if not empty
	 * @since 1.0.0
	 */
	var $status_message;

	/**
	 * @var string $error_message will be displayed if not empty
	 * @since 1.0.0
	 */
	var $error_message;

	/**
	 * Class constructor
	 *
	 * Sets up the class.
	 * @since 1.0.0
	 * @return void
	 */
	function __construct() {
		$this->_is_admin       = is_admin();
		$this->_current_page   = empty( $_GET['page'] ) ? false : $_GET['page'];
		$this->_current_add_on = empty( $_GET['add-on-settings'] ) ? false : $_GET['add-on-settings'];

		if ( ! empty( $_POST ) && $this->_is_admin && 'it-exchange-addons' == $this->_current_page && 'membership-product-type' == $this->_current_add_on ) {
			add_action( 'it_exchange_save_add_on_settings_membership-product-type', array( $this, 'save_settings' ) );
			do_action( 'it_exchange_save_add_on_settings_membership-product-type' );
		}
	}

	/**
	 * Class deprecated constructor
	 *
	 * Sets up the class.
	 * @since 1.0.0
	 * @return void
	 */
	function IT_Exchange_Membership_Add_On() {
		self::__construct();
	}

	/**
	 * Prints settings page
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function print_settings_page() {
		$settings     = it_exchange_get_option( 'addon_membership', true );
		$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
		$form_options = array(
			'id'      => apply_filters( 'it_exchange_add_on_membership', 'it-exchange-add-on-membership-settings' ),
			'enctype' => apply_filters( 'it_exchange_add_on_membership_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-exchange-addons&add-on-settings=membership-product-type',
		);
		$form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-membership' ) );

		if ( ! empty ( $this->status_message ) ) {
			ITUtility::show_status_message( $this->status_message );
		}
		if ( ! empty( $this->error_message ) ) {
			ITUtility::show_error_message( $this->error_message );
		}

		//Suhosin Fix
		if ( empty( $settings['membership-restricted-content-message'] ) ) {
			$settings['restricted-content-message'] = $settings['membership-restricted-content-message'];
		}
		if ( empty( $settings['membership-dripped-content-message'] ) ) {
			$settings['dripped-content-message'] = $settings['membership-dripped-content-message'];
		}
		if ( empty( $settings['membership-restricted-product-message'] ) ) {
			$settings['restricted-product-message'] = $settings['membership-restricted-product-message'];
		}
		if ( empty( $settings['membership-dripped-product-message'] ) ) {
			$settings['dripped-product-message'] = $settings['membership-dripped-product-message'];
		}
		//End Suhosin Fix

		?>
		<div class="wrap">
			<?php screen_icon( 'it-exchange' ); ?>
			<h2><?php _e( 'Membership Settings', 'LION' ); ?></h2>

			<?php do_action( 'it_exchange_membership_settings_page_top' ); ?>
			<?php do_action( 'it_exchange_addon_settings_page_top' ); ?>
			<?php $form->start_form( $form_options, 'it-exchange-membership-settings' ); ?>
			<?php do_action( 'it_exchange_membership__settings_form_top' ); ?>
			<?php $this->get_membership_form_table( $form, $form_values ); ?>
			<?php do_action( 'it_exchange_membership_settings_form_bottom' ); ?>
			<p class="submit">
				<?php $form->add_submit( 'submit', array(
					'value' => __( 'Save Changes', 'LION' ),
					'class' => 'button button-primary button-large'
				) ); ?>
			</p>
			<?php $form->end_form(); ?>
			<?php do_action( 'it_exchange_membership_settings_page_bottom' ); ?>
			<?php do_action( 'it_exchange_addon_settings_page_bottom' ); ?>
		</div>
		<?php
	}

	function get_membership_form_table( ITForm $form, $settings = array() ) {

		global $wp_version;

		if ( ! empty( $settings ) ) {
			foreach ( $settings as $key => $var ) {
				$form->set_option( $key, $var );
			}
		}

		if ( ! empty( $_GET['page'] ) && 'it-exchange-setup' == $_GET['page'] ) : ?>
			<h3><?php _e( 'Membership', 'LION' ); ?></h3>
		<?php endif; ?>
		<div class="it-exchange-addon-settings it-exchange-membership-addon-settings">
			<p>
				<label for="membership-restricted-show-excerpt"><?php _e( 'Show content excerpt?', 'LION' ); ?>
					<span class="tip" title="<?php _e( 'Use this to display the post\'s excerpt before the content message.', 'LION' ); ?>">i</span></label>
				<?php $form->add_check_box( 'membership-restricted-show-excerpt' ); ?>
			</p>
			<p>
				<label for="membership-restricted-content-message"><?php _e( 'Restricted Content Message', 'LION' ); ?>
					<span class="tip" title="<?php _e( 'This message will display when a non-member attempts to access content that has been restricted.', 'LION' ); ?>">i</span></label>
				<?php
				if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
					wp_editor( $settings['restricted-content-message'], 'restricted-content-message', array(
						'textarea_name' => 'it-exchange-add-on-membership-restricted-content-message',
						'textarea_rows' => 10,
						'textarea_cols' => 30,
						'editor_class'  => 'large-text'
					) );

					//We do this for some ITForm trickery... just to add recurring-payments-cancel-body to the used inputs field
					$form->get_text_area( 'restricted-content-message', array(
						'rows'  => 10,
						'cols'  => 30,
						'class' => 'large-text'
					) );
				} else {
					$form->add_text_area( 'restricted-content-message', array(
						'rows'  => 10,
						'cols'  => 30,
						'class' => 'large-text'
					) );
				}
				?>
			</p>
			<p class="description">
				<?php _e( 'Use the {products} tag to display a comma-separated list of memberships that can access the restricted content..', 'LION' ); ?>
			</p>
			<p>
				<label for="membership-dripped-content-message"><?php _e( 'Delayed Content Message', 'LION' ); ?>
					<span class="tip" title="<?php _e( 'This message will appear when a member attempts to access content that has been delayed.', 'LION' ); ?>">i</span></label>
				<?php
				if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
					wp_editor( $settings['dripped-content-message'], 'dripped-content-message', array(
						'textarea_name' => 'it-exchange-add-on-membership-dripped-content-message',
						'textarea_rows' => 10,
						'textarea_cols' => 30,
						'editor_class'  => 'large-text'
					) );

					//We do this for some ITForm trickery... just to add recurring-payments-cancel-body to the used inputs field
					$form->get_text_area( 'dripped-content-message', array(
						'rows'  => 10,
						'cols'  => 30,
						'class' => 'large-text'
					) );
				} else {
					$form->add_text_area( 'dripped-content-message', array(
						'rows'  => 10,
						'cols'  => 30,
						'class' => 'large-text'
					) );
				}
				?>
			</p>
			<p class="description">
				<?php
				_e( 'Use %d to represent the number of days until the delayed content will be available.', 'LION' );
				?>
			</p>
			<p>
				<label for="membership-restricted-product-message"><?php _e( 'Restricted Product Message', 'LION' ); ?>
					<span class="tip" title="<?php _e( 'This message will display when a non-member attempts to access a product that has been restricted.', 'LION' ); ?>">i</span></label>
				<?php
				if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
					wp_editor( $settings['restricted-product-message'], 'restricted-product-message', array(
						'textarea_name' => 'it-exchange-add-on-membership-restricted-product-message',
						'textarea_rows' => 10,
						'textarea_cols' => 30,
						'editor_class'  => 'large-text'
					) );

					//We do this for some ITForm trickery... just to add recurring-payments-cancel-body to the used inputs field
					$form->get_text_area( 'restricted-product-message', array(
						'rows'  => 10,
						'cols'  => 30,
						'class' => 'large-text'
					) );
				} else {
					$form->add_text_area( 'restricted-product-message', array(
						'rows'  => 10,
						'cols'  => 30,
						'class' => 'large-text'
					) );
				}
				?>
			</p>
			<p>
				<label for="membership-dripped-product-message"><?php _e( 'Delayed Product Message', 'LION' ); ?>
					<span class="tip" title="<?php _e( 'This message will appear when a member attempts to access a product that has been delayed.', 'LION' ); ?>">i</span></label>
				<?php
				if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
					wp_editor( $settings['dripped-product-message'], 'dripped-product-message', array(
						'textarea_name' => 'it-exchange-add-on-membership-dripped-product-message',
						'textarea_rows' => 10,
						'textarea_cols' => 30,
						'editor_class'  => 'large-text'
					) );

					//We do this for some ITForm trickery... just to add recurring-payments-cancel-body to the used inputs field
					$form->get_text_area( 'dripped-product-message', array(
						'rows'  => 10,
						'cols'  => 30,
						'class' => 'large-text'
					) );
				} else {
					$form->add_text_area( 'dripped-product-message', array(
						'rows'  => 10,
						'cols'  => 30,
						'class' => 'large-text'
					) );
				}
				?>
			</p>
			<p class="description">
				<?php
				_e( 'Use %d to represent the number of days until the delayed content will be available.', 'LION' );
				?>
			</p>
			<p>
				<label for="membership-prerequisite-label"><?php _e( 'Default Prerequisites Label', 'LION' ); ?>
					<span class="tip" title="<?php _e( 'This label will appear when displaying the prerequisite information on a membership.', 'LION' ); ?>">i</span></label>
			</p>
			<p> <?php $form->add_text_box( 'membership-prerequisites-label' ); ?> </p>
			<p>
				<label for="membership-intended-audience-label"><?php _e( 'Default Intended Audience Label', 'LION' ); ?>
					<span class="tip" title="<?php _e( 'This label will appear when displaying the intended audience information on a membership.', 'LION' ); ?>">i</span></label>
			</p>
			<p> <?php $form->add_text_box( 'membership-intended-audience-label' ); ?> </p>
			<p>
				<label for="membership-objectives-label"><?php _e( 'Default Objectives Label', 'LION' ); ?>
					<span class="tip" title="<?php _e( 'This label will appear when displaying the objective information on a membership.', 'LION' ); ?>">i</span></label>
			</p>
			<p> <?php $form->add_text_box( 'membership-objectives-label' ); ?> </p>
			<p>
				<label for="memberships-dashboard-view"><?php _e( 'Membership Dashboard View', 'LION' ); ?>
					<span class="tip" title="<?php _e( 'Sets the default way items are displayed in the customer\'s membership dashboard.', 'LION' ); ?>">i</span></label>
			</p>
			<p>
				<?php $form->add_drop_down( 'memberships-dashboard-view', array(
					'grid' => __( 'Grid', 'LION' ),
					'list' => __( 'List', 'LION' )
				) ); ?>
			</p>
			<p>
				<label for="memberships-group-toggle"><?php _e( 'Membership Group Toggle', 'LION' ); ?>
					<span class="tip" title="<?php _e( 'Sets the default option for toggling grouped membership content.', 'LION' ); ?>">i</span></label>
			</p>
			<p>
				<?php $form->add_drop_down( 'memberships-group-toggle', array(
					'true'  => __( 'Yes', 'LION' ),
					'false' => __( 'No', 'LION' )
				) ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Save settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function save_settings() {
		$defaults   = it_exchange_get_option( 'addon_membership' );
		$new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

		// Check nonce
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-membership-settings' ) ) {
			$this->error_message = __( 'Error. Please try again', 'LION' );

			return;
		}

		$errors = apply_filters( 'it_exchange_add_on_membership_validate_settings', $this->get_form_errors( $new_values ), $new_values );
		if ( ! $errors && it_exchange_save_option( 'addon_membership', $new_values ) ) {
			ITUtility::show_status_message( __( 'Settings saved.', 'LION' ) );
		} else if ( $errors ) {
			$errors              = implode( '<br />', $errors );
			$this->error_message = $errors;
		} else {
			$this->status_message = __( 'Settings not saved.', 'LION' );
		}
	}

	/**
	 * Validates for values
	 *
	 * Returns string of errors if anything is invalid
	 *
	 * @since 1.0.0
	 *
	 * @param array $values
	 *
	 * @return array
	 */
	public function get_form_errors( $values ) {

		$errors = array();
		if ( empty( $values['restricted-content-message'] ) ) {
			$errors[] = __( 'Please include a restricted content message.', 'LION' );
		}
		if ( empty( $values['dripped-content-message'] ) ) {
			$errors[] = __( 'Please include a delayed content message.', 'LION' );
		}

		return $errors;
	}

}
