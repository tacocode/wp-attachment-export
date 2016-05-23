<?php
/*
Plugin Name: WP Attachment Export
Plugin URI: https://wordpress.org/plugins/wp-attachment-export
Description: Exports only posts of type 'attachment', i.e. your media library
Version: 0.3.3
Author: Peter Harlacher
Author URI: http://helvetian.io
Text Domain: wp-attachment-export
License: GPL2
*/

/**
 * WP Attachment Export class
 */
class hlvtn_WP_Attachment_Export {
	
	/**
	 * Class constructor
	 */
	function __construct() {
		add_action( 'admin_menu', array(&$this, 'add_admin_menu') );
		add_action( 'wp_loaded', array(&$this, 'run_export') );
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'add_action_links') );
		add_action( 'plugins_loaded', array(&$this, 'load_textdomain') );
	}
	
	/**
	 * Loads our plugin text domain
	 * @return bool true if the language file was loaded successfully, false otherwise
	 */
	function load_textdomain() {
		load_plugin_textdomain( 'wp-attachment-export', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
	}
	
	/**
	 * Adds a link beside the activate/deactivate links on the Plugins page
	 * @param array $links Array with links
	 * @return array Merged links
	 */
	function add_action_links ( $links ) {
		$export_link = array(
			'<a href="'.admin_url( 'tools.php?page=wp-attachment-export' ).'">'.esc_attr__('Export', 'wp-attachment-export').'</a>',
		);
		return array_merge( $links, $export_link );
	}

	/**
	 * Create the date options fields for exporting a given post type.
	 *
	 * @global wpdb      $wpdb      WordPress database abstraction object.
	 * @global WP_Locale $wp_locale Date and Time Locale object.
	 *
	 * @since 3.1.0
	 *
	 * @param string $post_type The post type. Default 'post'.
	 */
	function create_export_date_options( $post_type = 'post' ) {
		global $wpdb, $wp_locale;

		$months = $wpdb->get_results( $wpdb->prepare( "
			SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
			FROM $wpdb->posts
			WHERE post_type = %s AND post_status != 'auto-draft'
			ORDER BY post_date DESC
		", $post_type ) );

		$month_count = count( $months );
		if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
			return;

		foreach ( $months as $date ) {
			if ( 0 == $date->year )
				continue;

			$month = zeroise( $date->month, 2 );
			echo '<option value="' . $date->year . '-' . $month . '">' . $wp_locale->get_month( $month ) . ' ' . $date->year . '</option>';
		}
	}
	
	/**
	 * Displays the admin view/screen
	 */
	function admin_screen() {
		?>
		<div class="wrap">
			<h2><?php esc_attr_e( 'WP Attachment Export', 'wp-attachment-export' ); ?></h2>
			<p><?php esc_attr_e( 'When you click the button below WordPress will create an XML file for you to save to your computer.', 'wp-attachment-export' ); ?></p>
			<p><?php esc_attr_e( 'This format, which we call WordPress eXtended RSS or WXR, will contain your attachments.', 'wp-attachment-export' ); ?></p>
			<p><?php esc_attr_e( 'Once you&#8217;ve saved the download file, you can use the Import function in another WordPress installation to import the attachments from this site.', 'wp-attachment-export' ); ?></p>
			<h3><?php esc_attr_e( 'Choose what to export', 'wp-attachment-export' ); ?></h3>
			<form action="" method="get" id="export-filters">
				<p><label><input type="radio" name="content" value="attachment" checked="checked" /> <?php esc_attr_e( 'Attachments', 'wp-attachment-export' ); ?></label></p>
				<p><span class="label-responsive"><?php esc_attr_e( 'Authors', 'wp-attachment-export' ); ?></span>
					<?php
						global $wpdb;
						$authors = $wpdb->get_col( "SELECT DISTINCT post_author FROM {$wpdb->posts} WHERE post_type = 'post'" );
						wp_dropdown_users( array( 'include' => $authors, 'name' => 'post_author', 'multi' => true, 'show_option_all' => __('All') ) );
					?>
				</p>
				<p>
					<label for="attachment-start-date" class="label-responsive"><?php _e( 'Start date:' ); ?></label>
					<select name="attachment_start_date" id="attachment-start-date">
						<option value="0"><?php _e( '&mdash; Select &mdash;' ); ?></option>
						<?php self::create_export_date_options( 'attachment' ); ?>
					</select>
				</p>
				<p>
					<label for="attachment-end-date" class="label-responsive"><?php _e( 'End date:' ); ?></label>
					<select name="attachment_end_date" id="attachment-end-date">
						<option value="0"><?php _e( '&mdash; Select &mdash;' ); ?></option>
						<?php self::create_export_date_options( 'attachment' ); ?>
					</select>
				</p>
				<p class="description"><?php esc_attr_e( 'This will contain all of your attachments.', 'wp-attachment-export' ); ?></p>
				<input type="submit" value="<?php esc_attr_e( 'Download Export File', 'wp-attachment-export' ); ?>" class="button button-secondary">
				<input type="hidden" name="wp-attachment-export-download" value="true" />
				<?php wp_nonce_field( 'wp_attachment_export_download', 'wp_attachment_export_nonce' ); ?>
			</form>
			<p>&nbsp;</p>
			<hr />
			<p><small><a href="https://wordpress.org/support/view/plugin-reviews/wp-attachment-export" target="_blank"><?php esc_attr_e( 'Rate this plugin', 'wp-attachment-export' ); ?></a> &bull; <a href="https://github.com/thehelvetian/wp-attachment-export" target="_blank"><?php esc_attr_e( 'Contribute on GitHub', 'wp-attachment-export' ); ?></a></small></p>
		</div>
		<?php
	}
	
	/**
	 * Adds a menu entry at Tools > WP Attachment Export
	 */
	function add_admin_menu() {
		add_management_page( esc_attr__('WP Attachment Export', 'wp-attachment-export'), esc_attr__('Attachment Export', 'wp-attachment-export'), 'manage_options', 'wp-attachment-export', array(&$this, 'admin_screen') );
	}
	
	/**
	 * The actual export is done here
	 */
	function run_export() {
		if ( is_admin() && isset( $_GET['wp-attachment-export-download'] ) ) {
			if ( current_user_can( 'administrator' ) && isset($_REQUEST['wp_attachment_export_nonce']) && wp_verify_nonce( $_REQUEST['wp_attachment_export_nonce'], 'wp_attachment_export_download' ) ) {
				require_once(ABSPATH.'/wp-admin/includes/export.php');
				$args = array();
				$args['content'] = $_GET['content'];
				if ( $_GET['attachment_start_date'] || $_GET['attachment_end_date'] ) {
					$args['start_date'] = $_GET['attachment_start_date'];
					$args['end_date'] = $_GET['attachment_end_date'];
				}
				export_wp( $args );
				die();
			} else {
				wp_nonce_ays( 'wp_attachment_export_download' );
			}
		}
	}

}
/**
 * Instantiate our plugin class
 */
$hlvtn_WP_Attachment_Export = new hlvtn_WP_Attachment_Export();
