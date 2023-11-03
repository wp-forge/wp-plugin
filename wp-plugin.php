<?php
/**
 * %%pluginName%%
 *
 * @package           %%pluginPackage%%
 * @author            %%pluginAuthor%%
 * @copyright         Copyright %%currentYear%% by %%pluginAuthor%% - All rights reserved.
 * @license           GPL2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       %%pluginName%%
 * Plugin URI:        %%pluginUri%%
 * Description:       %%pluginDescription%%
 * Version:           1.0
 * Requires PHP:      %%pluginPhpVersion%%
 * Requires at least: %%pluginWpVersion%%
 * Author:            %%pluginAuthor%%
 * Author URI:        %%pluginAuthorUri%%
 * Text Domain:       %%pluginTextDomain%%
 * Domain Path:       /languages
 * Update URI:        %%pluginUpdateUri%%
 * License:           GPL V2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Generated using https://github.com/wp-forge/wp-plugin
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( '%%pluginConstantPrefix%%FILE', __FILE__ );
define( '%%pluginConstantPrefix%%DIR', plugin_dir_path( __FILE__ ) );
define( '%%pluginConstantPrefix%%URL', plugin_dir_url( __FILE__ ) );

if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
// TODO: Uncomment this if you have dependencies, delete otherwise
//} else {
//	if ( 'local' === wp_get_environment_type() ) {
//		wp_die( esc_html( __( 'Please install the %%pluginName%% dependencies.', '%%pluginTextDomain%%' ) ) );
//	}
//
//	return;
}

// Automatically load all PHP files in the 'hooks' directory
$iterator = new RecursiveDirectoryIterator( __DIR__ . '/hooks' );
foreach ( new RecursiveIteratorIterator( $iterator ) as $file ) {
	if ( $file->getExtension() === 'php' ) {
		require $file;
	}
};