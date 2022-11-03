<?php
/**
 * Breaking News Options
 *
 * @package    Breaking_News
 * @subpackage Breaking_News/inc/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * BNS_Admin_Options class to manage plugin options on admin side.
 */
class BNS_Admin_Options {

	/**
	 * Holds the values to be used in the fields callbacks
	 *
	 * @var array
	 */
	private $options;


	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_bns_options_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_filter( 'plugin_action_links_' . BNS_BASENAME, array( $this, 'bns_plugin_action_links' ) );
	}


	/**
	 * Add/Create the `Breaking News` options admin-page
	 *
	 * @return void
	 */
	public function add_bns_options_page() {
		// This page will be under "Settings".
		add_options_page(
			__( 'Breaking News Options', 'breakingnews' ),
			__( 'Breaking News', 'breakingnews' ),
			'manage_options',
			'bns-settings',
			array( $this, 'bns_options_page_content' )
		);
	}


	/**
	 * Callback function to add content to `Breaking News` options admin-page.
	 *
	 * @return void
	 */
	public function bns_options_page_content() {
		// Set class property.
		$this->options = get_option( 'bns_options' );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Breaking News Options', 'breakingnews' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields.
				settings_fields( 'bns_fields_group' );
				do_settings_sections( 'bns-settings' );
				submit_button();
				?>
			</form>
		</div>

		<?php
		bns_the_breaking_news(); // Display Active Breaking News.
	}


	/**
	 * Register setting, Add section and the Option Fields on `admin_init` hook
	 *
	 * @return void
	 */
	public function page_init() {
		register_setting(
			'bns_fields_group', // Option group.
			'bns_options', // Option name.
			array( $this, 'validate' ) // Callback to validate.
		);

		add_settings_section(
			'bns_setings_section', // Section ID.
			'', // Section Title.
			'', // Callback.
			'bns-settings' // `Breakin News` Page Slug.
		);

		add_settings_field(
			'title', // Option Field ID.
			__( 'Title', 'breakingnews' ), // Option Field Title.
			array( $this, 'cb_title' ), // Callback.
			'bns-settings', // `Breakin News` Page Slug.
			'bns_setings_section' // `Breakin News` Section ID.
		);
		add_settings_field(
			'background',
			__( 'Background', 'breakingnews' ),
			array( $this, 'cb_background' ),
			'bns-settings',
			'bns_setings_section'
		);
		add_settings_field(
			'color',
			__( 'Color', 'breakingnews' ),
			array( $this, 'cb_color' ),
			'bns-settings',
			'bns_setings_section'
		);
	}


	/**
	 * Sanitize/Validate each setting field as needed.
	 *
	 * @param array $fields Contains all `Breaking News` fields as array.
	 *
	 * @return array Valid fields.
	 */
	public function validate( $fields ) {
		$valid_fields = array();

		if ( isset( $fields['title'] ) ) {
			$valid_fields['title'] = sanitize_text_field( $fields['title'] );
		}

		if ( isset( $fields['background'] ) && '' !== trim( $fields['background'] ) ) {
			$valid_fields['background'] = $this->validate_color( $fields['background'], 'Background' );
		}

		if ( isset( $fields['color'] ) && '' !== trim( $fields['color'] ) ) {
			$valid_fields['color'] = $this->validate_color( $fields['color'], 'Color' );
		}

		return apply_filters( 'validate_options', $valid_fields, $fields );

	}


	/**
	 * Function to sanitize and validate colors fields.
	 *
	 * @param string $field Color Value (Hex Code Expected).
	 * @param string $key   Color Field Key/String for error message.
	 *
	 * @return string
	 */
	public function validate_color( $field, $key ) {
		// Validate Background Color.
		$field = sanitize_text_field( $field );

		// Check if is a valid hex color.
		if ( false === $this->check_color( $field ) ) {
			// Set the error message.
			add_settings_error( 'bns_options', $key . 'error', 'Insert a valid color for ' . $key, 'error' );

			// Get the previous valid value.
			return $this->options[ $key ];
		} else {
			return $field;
		}
	}


	/**
	 * Function that will check if value is a valid HEX color.
	 *
	 * @param mixed $value Hex-color string to validate.
	 *
	 * @return bool
	 */
	public function check_color( $value ) {
		if ( preg_match( '/^#[a-f0-9]{6}$/i', $value ) ) { // if user insert a HEX color with a hash (#).
			return true;
		}
		return false;
	}


	/**
	 * Get the settings options array and print one of its values
	 *
	 * @return void
	 */
	public function cb_title() {
		printf(
			'<input type="text" id="title" name="bns_options[title]" value="%s" />',
			isset( $this->options['title'] ) ? esc_attr( $this->options['title'] ) : ''
		);
	}


	/**
	 * Get the settings options array and print one of its values
	 *
	 * @return void
	 */
	public function cb_background() {
		printf(
			'<input type="text" id="background" name="bns_options[background]" value="%s" class="color-picker" />',
			isset( $this->options['background'] ) ? esc_attr( $this->options['background'] ) : ''
		);
	}


	/**
	 * Get the settings options array and print one of its values
	 *
	 * @return void
	 */
	public function cb_color() {
		printf(
			'<input type="text" id="color" name="bns_options[color]" value="%s" class="color-picker" />',
			isset( $this->options['color'] ) ? esc_attr( $this->options['color'] ) : ''
		);
	}


	/**
	 * Merging the Settings link in the plugin links, on installed plugins page.
	 *
	 * @param array $links Array of links.
	 *
	 * @return array Array of links.
	 */
	public function bns_plugin_action_links( $links ) {
		$action_links[] = '<a href="' . admin_url( 'options-general.php?page=bns-settings' ) . '">' . esc_html__( 'Settings', 'breakingnews' ) . '</a>';
		return array_merge( $action_links, $links );
	}


}

if ( is_admin() ) {
	new BNS_Admin_Options();
}
