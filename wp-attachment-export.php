<?php
/*
Plugin Name: WP Attachment Export
Plugin URI: https://wordpress.org/plugins/wp-attachment-export
Description: Exports only posts of type 'attachment', i.e. your media library
Version: 0.2.4
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
	 * Displays the admin view/screen
	 */
	function admin_screen() {
		?>
		<div class="wrap">
		<h2><?php esc_attr_e( 'Attachment Export', 'wp-attachment-export' ); ?></h2>
		<p><?php esc_attr_e( 'When you click the button below WordPress will create an XML file for you to save to your computer.', 'wp-attachment-export' ); ?></p>
		<p><?php esc_attr_e( 'This format, which we call WordPress eXtended RSS or WXR, will contain your attachments.', 'wp-attachment-export' ); ?></p>
		<p><?php esc_attr_e( 'Once you&#8217;ve saved the download file, you can use the Import function in another WordPress installation to import the attachments from this site.', 'wp-attachment-export' ); ?></p>
		<h3><?php esc_attr_e( 'Choose what to export', 'wp-attachment-export' ); ?></h3>
		<form action="" method="get" id="export-filters">
			<p><label><input type="radio" name="content" value="attachment" checked="checked" /> <?php esc_attr_e( 'Attachments', 'wp-attachment-export' ); ?></label></p>
			<p class="description"><?php esc_attr_e( 'This will contain all of your attachments.', 'wp-attachment-export' ); ?></p>
			<input type="submit" value="<?php esc_attr_e( 'Download Export File', 'wp-attachment-export' ); ?>" class="button button-secondary">
			<input type="hidden" name="wp-attachment-export-download" value="true" />
			<?php wp_nonce_field( 'wp_attachment_export_download', 'wp_attachment_export_nonce' ); ?>
		</form>
		</div>
		<?php
	}
	
	/**
	 * Adds a menu entry at Tools > WP Attachment Export
	 */
	function add_admin_menu() {
		add_management_page( esc_attr__('WP Attachment Export', 'wp-attachment-export'), esc_attr__('WP Attachment Export', 'wp-attachment-export'), 'manage_options', 'wp-attachment-export', array(&$this, 'admin_screen') );
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