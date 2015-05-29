<?php
/*
Plugin Name: WP Attachment Export
Plugin URI: http://petermichael.me
Description: Exports only posts of type 'attachment', i.e. your media library
Version: 0.1.0
Author: Peter Michael
Author URI: http://petermichael.me
License: GPL2
*/

function aexp_admin_screen() {
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
		<input type="submit" value="Download Export File" class="secondary">
		<input type="hidden" name="download" value="true" />
	</form>
	</div>
	<?php
}

function aexp_add_admin_menu() {
	add_management_page( 'Attachment Export', 'Attachment Export', 'manage_options', 'attachment-export', 'aexp_admin_screen' );
}
add_action('admin_menu', 'aexp_add_admin_menu');

function aexp_export() {
	if ( isset( $_GET['download'] ) ) {
		require_once(ABSPATH.'/wp-admin/includes/export.php');
		$args = array();
		$args['content'] = $_GET['content'];
		export_wp( $args );
		die();
	}
}
add_action('wp_loaded', 'aexp_export');
