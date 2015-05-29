<?php
/*
Plugin Name: WP Attachment Export
Plugin URI: https://wordpress.org/plugins/wp-attachment-export
Description: Exports only posts of type 'attachment', i.e. your media library
Version: 0.2.0
Author: Peter Michael
Author URI: http://helvetian.io
License: GPL2
*/

/**
 * WP Attachment Export class
 */
class hlvtn_WP_Attachment_Export {
	
	function __construct() {
		add_action( 'admin_menu', array(&$this, 'add_admin_menu') );
		add_action( 'wp_loaded', array(&$this, 'run_export') );
	}
	
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

	function add_admin_menu() {
		add_management_page( 'WP Attachment Export', 'WP Attachment Export', 'manage_options', 'wp-attachment-export', array(&$this, 'admin_screen') );
	}
	
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

$hlvtn_WP_Attachment_Export = new hlvtn_WP_Attachment_Export();
