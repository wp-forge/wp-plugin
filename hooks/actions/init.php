<?php

add_action(
	'init',
	function () {

		// Load the plugin's text domain
		load_plugin_textdomain(
			'%%pluginSlug%%',
			false,
			constant( '%%pluginConstantPrefix%%DIR' ) . '/languages'
		);

	}
);