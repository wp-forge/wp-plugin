<?php

add_filter(
	'acf/settings/show_admin',
	function ( $shouldShow ) {

		// Only allow ACF fields to be edited during development
		if ( wp_get_environment_type() !== 'local' ) {
			$shouldShow = false;
		}

		return $shouldShow;
	}
);