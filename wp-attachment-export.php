<?php
/*
Plugin Name: WP Attachment Export
Plugin URI: https://wordpress.org/plugins/wp-attachment-export
Description: Exports only posts of type 'attachment', i.e. your media library
Version: 0.2.1
Author: Peter Harlacher
Author URI: http://helvetian.io
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
	}
	
	/**
	 * Adds a link beside the activate/deactivate links on the Plugins page
	 * @param array $links Array with links
	 * @return array Merged links
	 */
	function add_action_links ( $links ) {
		$export_link = array(
			'<a href="' . admin_url( 'tools.php?page=wp-attachment-export' ) . '">Export</a>',
		);
		return array_merge( $links, $export_link );
	}
	
	/**
	 * Displays the admin view/screen
	 */
	function admin_screen() {
		?>
		<div class="wrap">
		<h2>Attachment Export</h2>

		<p><?php _e('When you click the button below WordPress will create an XML file for you to save to your computer.'); ?></p>
		<p><?php _e('This format, which we call WordPress eXtended RSS or WXR, will contain your attachments.'); ?></p>
		<p><?php _e('Once you&#8217;ve saved the download file, you can use the Import function in another WordPress installation to import the attachments from this site.'); ?></p>

		<h3><?php _e( 'Choose what to export' ); ?></h3>
		<form action="" method="get" id="export-filters">
			<p><label><input type="radio" name="content" value="attachment" checked="checked" /> <?php _e( 'Attachments' ); ?></label></p>
			<p class="description"><?php _e( 'This will contain all of your attachments.' ); ?></p>
			<input type="submit" value="Download Export File" class="button button-secondary">
			<input type="hidden" name="wp-attachment-export-download" value="true" />
		</form>
		</div>
		<?php
	}
	
	/**
	 * Adds a menu entry at Tools > WP Attachment Export
	 */
	function add_admin_menu() {
		add_management_page( 'WP Attachment Export', 'WP Attachment Export', 'manage_options', 'wp-attachment-export', array(&$this, 'admin_screen') );
	}
	
	/**
	 * The actual export is done here
	 */
	function run_export() {
		if ( is_admin() && isset( $_GET['wp-attachment-export-download'] ) ) {
			require_once(ABSPATH.'/wp-admin/includes/export.php');
			$args = array();
			$args['content'] = $_GET['content'];
			export_wp( $args );
			die();
		}
	}

}
/**
 * Instantiate our plugin class
 */
$hlvtn_WP_Attachment_Export = new hlvtn_WP_Attachment_Export();