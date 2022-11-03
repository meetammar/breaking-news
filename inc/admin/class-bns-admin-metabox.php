<?php
/**
 * Breaking News Post Metabox
 *
 * @package    Breaking_News
 * @subpackage Breaking_News/inc/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * BNS_Admin_Metabox class to manage Post Metabox-data
 */
class BNS_Admin_Metabox {


	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
	}


	/**
	 * Add the meta box and link it with a screen.
	 *
	 * @return void
	 */
	public function add_metabox() {
		add_meta_box(
			'bns-meta-box', // Metabox ID.
			__( 'Breaking News', 'breakingnews' ), // Metabox Title.
			array( $this, 'render_metabox' ), // Metabox callback.
			'post', // Screen ID where Metabox has to display.
			'side'
		);
	}


	/**
	 * Renders the Metabox.
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return void Renders Metabox fields HTML.
	 */
	public function render_metabox( $post ) {
		// Add nonce for security and authentication.
		wp_nonce_field( 'bns_nonce_action', 'bns_nonce' );

		$bns_breaking_news = (array) get_option( 'bns_breaking_news' );

		$bns_is_active    = 'no';
		$bns_custom_title = '';
		$bns_is_expirable = 'no';
		$bns_expiry       = '';
		if ( isset( $bns_breaking_news['post_id'] ) && $post->ID === $bns_breaking_news['post_id'] ) {
			$bns_is_active    = ( isset( $bns_breaking_news['bns_is_active'] ) ) ? $bns_breaking_news['bns_is_active'] : 'no';
			$bns_custom_title = ( isset( $bns_breaking_news['bns_custom_title'] ) ) ? $bns_breaking_news['bns_custom_title'] : '';
			$bns_is_expirable = ( isset( $bns_breaking_news['bns_is_expirable'] ) ) ? $bns_breaking_news['bns_is_expirable'] : 'no';
			$bns_expiry       = ( isset( $bns_breaking_news['bns_expiry'] ) && '' !== $bns_breaking_news['bns_expiry'] ) ? date( 'Y-m-d\TH:i', strtotime( $bns_breaking_news['bns_expiry'] ) ) : '';
		}

		$bns_is_active_check    = ( 'yes' === $bns_is_active ) ? 'checked="checked"' : '';
		$bns_is_expirable_check = ( 'yes' === $bns_is_expirable ) ? 'checked="checked"' : '';
		$bns_min_date           = wp_date( 'Y-m-d\TH:i' );
		?>

		<table>
			<tr>
				<td>
					<label for="bns_is_active"><?php esc_html_e( 'Make this post breaking news: ', 'breakingnews' ); ?></label>
					<input type="checkbox" name="bns_is_active" id="bns_is_active" value="yes" <?php echo esc_attr( $bns_is_active_check ); ?> />
				</td>
			</tr>
			<tr>
				<td>
					<br />
					<label for="bns_custom_title"><?php esc_html_e( 'Custom Title: ', 'breakingnews' ); ?></label>
					<input type="text" name="bns_custom_title" id="bns_custom_title" value="<?php echo esc_attr( $bns_custom_title ); ?>" />
				</td>
			</tr>
			<tr>
				<td>
					<br />
					<label for="bns_is_expirable"><?php esc_html_e( 'Add Expiry: ', 'breakingnews' ); ?></label>
					<input type="checkbox" name="bns_is_expirable" id="bns_is_expirable" value="yes" <?php echo esc_attr( $bns_is_expirable_check ); ?> />
				</td>
			</tr>
			<tr id="bns-expiry-wrap">
				<td>
					<br />
					<label for="bns_expiry"><?php esc_html_e( 'Expiry Date: ', 'breakingnews' ); ?></label>
					<input type="datetime-local" name="bns_expiry" id="bns_expiry" min="<?php echo esc_attr( $bns_min_date ); ?>" value="<?php echo esc_attr( $bns_expiry ); ?>" />
				</td>
			</tr>
		</table>

		<?php
	}


	/**
	 * Handles saving the Metabox fields.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return null
	 */
	public function save_metabox( $post_id, $post ) {
		// Add nonce for security and authentication.
		$nonce_name   = isset( $_POST['bns_nonce'] ) ? sanitize_key( $_POST['bns_nonce'] ) : '';
		$nonce_action = 'bns_nonce_action';

		// Check if nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
			return;
		}

		// Check if user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$valid_fields    = $this->validate( $_POST );
		$is_save_allowed = $this->is_valid_post_data( $post_id, $valid_fields );

		if ( 'yes' === $is_save_allowed ) {
			$bns_breaking_news = array(
				'post_id'          => $post_id,
				'bns_is_active'    => $valid_fields['bns_is_active'],
				'bns_custom_title' => $valid_fields['bns_custom_title'],
				'bns_is_expirable' => $valid_fields['bns_is_expirable'],
				'bns_expiry'       => $valid_fields['bns_expiry'],
			);
			update_option( 'bns_breaking_news', $bns_breaking_news );
		}

	}


	/**
	 * Sanitize/Validate each Metabox field as needed.
	 *
	 * @param array $fields Contains Metabox fields.
	 *
	 * @return array Valid Fields.
	 */
	public function validate( $fields ) {
		$valid_fields = array(
			'bns_is_active'    => 'no',
			'bns_custom_title' => '',
			'bns_is_expirable' => 'no',
			'bns_expiry'       => '',
		);
		if ( isset( $fields['bns_is_active'] ) ) {
			$valid_fields['bns_is_active'] = sanitize_text_field( $fields['bns_is_active'] );
		}
		if ( isset( $fields['bns_custom_title'] ) ) {
			$valid_fields['bns_custom_title'] = sanitize_text_field( $fields['bns_custom_title'] );
		}
		if ( isset( $fields['bns_is_expirable'] ) ) {
			$valid_fields['bns_is_expirable'] = sanitize_text_field( $fields['bns_is_expirable'] );
		}
		if ( isset( $fields['bns_expiry'] ) ) {
			$bns_expiry = sanitize_text_field( $fields['bns_expiry'] );

			if ( 'yes' === $this->is_valid_datetime( $bns_expiry ) ) {
				$valid_fields['bns_expiry'] = $bns_expiry;
			}
		}

		return apply_filters( 'validate_bns_metabox_fields', $valid_fields, $fields );
	}


	/**
	 * Check post validity, so that previous data should not overwrite.
	 *
	 * @param int   $post_id      Post ID.
	 * @param array $valid_fields Valid Fields.
	 *
	 * @return string yes/no.
	 */
	public function is_valid_post_data( $post_id, $valid_fields ) {
		$prev_bns_breaking_news = get_option( 'bns_breaking_news' );
		$prev_post_id           = isset( $prev_bns_breaking_news['post_id'] ) ? $prev_bns_breaking_news['post_id'] : '';

		if ( '' === $prev_post_id || $post_id === $prev_post_id || ( $post_id !== $prev_post_id && 'yes' === $valid_fields['bns_is_active'] ) ) {
			return 'yes';
		}
		return 'no';
	}


	/**
	 * Verify if date is valid
	 *
	 * @param string $datetime The date string that needs to be checked.
	 * @param string $format date formate.
	 *
	 * @return yes/no.
	 */
	public function is_valid_datetime( $datetime, $format = 'Y-m-d H:i' ) {
		$datetime = str_replace( 'T', ' ', $datetime );
		$d        = DateTime::createFromFormat( $format, $datetime );
		return ( $d && $d->format( $format ) === $datetime ) ? 'yes' : 'no';
	}


}

if ( is_admin() ) {
	new BNS_Admin_Metabox();
}
