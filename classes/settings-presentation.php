<?php

class WPSC_Settings_Tab_Presentation extends _WPSC_Settings_Tab_Form
{
	public function __construct() {
		parent::__construct();
		$this->hide_submit_button();
	}
	public function display() {
	?>
		<h3><?php _e( 'Wondering where all the old presentation settings have gone?', 'wpsc' ); ?></h3>
		<p><?php _e("Do not worry. We're taking this opportunity to rewrite them properly using the new WordPress settings API throughout this beta phase.", 'wpsc' ); ?></p>
		<p><?php _e( "We'll either add them right back or release mini Plugins. To help us decide what goes back into core and what will become a Plugin, please <a href='https://github.com/wp-e-commerce/WP-e-Commerce/issues/516'>let us know on Github</a> what your most important setting is.", 'wpsc' ); ?></p>
	<?php
	}
}