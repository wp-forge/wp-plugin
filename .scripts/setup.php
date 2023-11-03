<?php

namespace WP_Forge\WordPress\Plugin;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WP_Forge\Helpers\Str;

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

/**
 * Class Setup
 *
 * @package WP_Forge\WordPress\Plugin
 */
class Setup {

	/**
	 * Data store.
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Data required to generate the plugin prompts.
	 *
	 * @var array
	 */
	public $prompts = [
		'pluginName'        => [
			'name'     => 'plugin name',
			'required' => true,
			'type'     => 'string',
		],
		'pluginDescription' => [
			'name'     => 'plugin description',
			'required' => true,
		],
		'pluginAuthor'      => [
			'name'     => 'plugin author name',
			'required' => true,
		],
		'pluginAuthorUri'   => [
			'name'     => 'plugin author URL',
			'required' => true,
		],
		'pluginUri'         => [
			'name'     => 'plugin URL',
			'required' => true,
		],
		'pluginUpdateUri'   => [
			'name' => 'plugin update URL',
		],
		'pluginDonateLink'  => [
			'name' => 'plugin donation URL',
		],
		'pluginTags'        => [
			'name' => 'plugin tags (comma separated)',
		],
		'gitUser'           => [
			'name'     => 'GitHub username',
			'required' => true,
		],
		'vendorSlug'        => [
			'name'     => 'vendor name',
			'required' => true,
		],
		'wpOrgUsername'     => [
			'name'     => 'WordPress.org username',
			'required' => true,
		]
	];

	/**
	 * Setup constructor.
	 */
	public function __construct() {

		echo 'Welcome to the WordPress plugin setup wizard!' . PHP_EOL . PHP_EOL;

		$isPublic = $this->isPublic();

		$path = dirname( __DIR__ );

		// Set known values
		$this->set( 'currentYear', date( 'Y' ) );
		$this->set( 'gitUserName', exec( 'git config --global user.name' ) );
		$this->set( 'nodeVersion', $this->getNodeVersion() );
		$this->set( 'pluginAuthor', $this->get( 'gitUserName' ) );
		$this->set( 'pluginPhpVersion', preg_replace( '/^(\d+\.\d+).*$/', '$1', phpversion() ) );
		$this->set( 'pluginSlug', basename( $path ) );
		$this->set( 'pluginTextDomain', basename( $path ) );
		$this->set( 'pluginWpVersion', preg_replace( '/^(\d+\.\d+).*$/', '$1', $this->getWPVersion() ) );
		$this->set( 'port', rand( 1000, 9998 ) );
		$this->set( 'testPort', $this->get( 'port' ) + 1 );

		// Set default values
		$this->prompts['pluginAuthor']['default']  = $this->get( 'gitUserName' );
		$this->prompts['gitUser']['default']       = basename( $_SERVER['HOME'] );
		$this->prompts['wpOrgUsername']['default'] = basename( $_SERVER['HOME'] );
		$this->prompts['vendorSlug']['default']    = basename( $_SERVER['HOME'] );

		// Request unknown values
		foreach ( $this->prompts as $key => $value ) {
			if ( empty( $this->get( $key ) ) ) {
				$this->set( $key, $this->prompt( $key ) );
			}
		}

		// Set derived values
		$this->set( 'pluginConstantPrefix', $this->toAlphaNumeric( strtoupper( Str::snake( Str::lower( $this->get( 'pluginName' ) ) ) ) . '_' ) );
		$this->set( 'pluginNamespace', $this->toAlphaNumeric( Str::studly( $this->get( 'pluginName' ) ) ) );
		$this->set( 'pluginPackage', $this->toAlphaNumeric( Str::studly( $this->get( 'pluginName' ) ) ) );
		$this->set( 'vendorSlug', Str::kebab( $this->get( 'vendorSlug' ) ) );

		// Sort before displaying
		ksort( $this->data );

		echo PHP_EOL . 'Here is the data that will be used to generate your plugin:' . PHP_EOL;

		echo json_encode( $this->data, JSON_PRETTY_PRINT ) . PHP_EOL . PHP_EOL;

		echo 'Generating plugin...' . PHP_EOL . PHP_EOL;

		$this->copyFile( "{$path}/.wp-env.json", "{$path}/.wp-env.json" );
		$this->copyFile( "{$path}/.templates/README.md", "{$path}/README.md" );
		$this->copyFile( "{$path}/readme.txt", "{$path}/readme.txt" );
		$this->copyFile( "{$path}/hooks/actions/init.php", "{$path}/hooks/actions/init.php" );
		$this->copyFile( "{$path}/wp-plugin.php", "{$path}/{$this->get('pluginSlug')}.php" );

		mkdir( "{$path}/.github" );
		mkdir( "{$path}/.github/workflows" );

		$this->copyFile( "{$path}/.templates/upload-artifact-on-push.yml", "{$path}/.github/workflows/upload-artifact-on-push.yml" );
		$this->copyFile( "{$path}/.templates/upload-asset-on-release.yml", "{$path}/.github/workflows/upload-asset-on-release.yml" );

		if ( $isPublic ) {
			$this->copyFile( "{$path}/.templates/svn-deploy-assets-on-push.yml", "{$path}/.github/workflows/svn-deploy-assets-on-push.yml" );
			$this->copyFile( "{$path}/.templates/svn-deploy-plugin-on-release.yml", "{$path}/.github/workflows/svn-deploy-plugin-on-release.yml" );
		} else {
			$this->delete( "{$path}/.wporg" );
			$this->delete( "{$path}/readme.txt" );
		}

		echo 'Initializing Git...' . PHP_EOL . PHP_EOL;

		$this->delete( "{$path}/.git" );
		exec( 'git init' );
		exec( 'git branch -m main' );

		echo 'Setting up Composer...' . PHP_EOL . PHP_EOL;

		$this->copyFile( "{$path}/.templates/composer.json", "{$path}/composer.json" );
		$this->delete( "{$path}/vendor" );
		$this->delete( "{$path}/composer.lock" );
		exec( 'composer install' );
		exec( 'composer run i18n' );

		echo 'Setting up NPM...' . PHP_EOL . PHP_EOL;

		$this->copyFile( "{$path}/.nvmrc", "{$path}/.nvmrc" );
		$this->copyFile( "{$path}/package.json", "{$path}/package.json" );
		$this->delete( "{$path}/node_modules" );
		$this->delete( "{$path}/package-lock.json" );
		exec( 'npm install' );

		echo 'Cleaning up...' . PHP_EOL . PHP_EOL;

		$this->delete( "{$path}/.templates" );
		$this->delete( "{$path}/.scripts" );

		if ( $this->get( 'pluginSlug' ) !== 'wp-plugin.php' ) {
			$this->delete( "{$path}/wp-plugin.php" );
		}

		echo 'Plugin setup is complete!' . PHP_EOL . PHP_EOL;

		if ( $isPublic ) {
			echo 'Make sure to set SVN_USERNAME and SVN_PASSWORD as secrets on GitHub for SVN deployments to work properly.' . PHP_EOL . PHP_EOL;
		}

		echo "You can now run `cd {$this->get('pluginSlug')} && npm start` to start the development environment." . PHP_EOL . PHP_EOL;

	}

	/**
	 * Set a value in the data store.
	 *
	 * @param string $key The key to set.
	 * @param mixed $value The value to set.
	 */
	public function set( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	/**
	 * Determine if a value exists in the data store.
	 *
	 * @param string $key The key to check.
	 *
	 * @return bool
	 */
	public function has( $key ) {
		return isset( $this->data[ $key ] );
	}

	/**
	 * Get a value from the data store.
	 *
	 * @param string $key The key to retrieve.
	 *
	 * @return mixed
	 */
	public function get( $key ) {
		return $this->has( $key ) ? $this->data[ $key ] : null;
	}

	/**
	 * Remove a value from the data store.
	 *
	 * @param string $key The key to remove.
	 */
	public function remove( $key ) {
		if ( $this->has( $key ) ) {
			unset( $this->data[ $key ] );
		}
	}

	/**
	 * Prompt the user for a value.
	 *
	 * @param string $key The key to prompt for.
	 *
	 * @return mixed
	 */
	public function prompt( $key ) {
		$value = null;
		if ( array_key_exists( $key, $this->prompts ) ) {
			$prompt   = $this->prompts[ $key ];
			$name     = isset( $prompt['name'] ) ? $prompt['name'] : $key;
			$default  = isset( $prompt['default'] ) ? $prompt['default'] : null;
			$type     = isset( $prompt['type'] ) ? $prompt['type'] : 'string';
			$required = isset( $prompt['required'] ) && (bool) $prompt['required'];
			$message  = "Enter the {$name}";
			if ( $default ) {
				$message .= " [{$default}]";
			}
			$message .= ': ';
			$value   = readline( $message );
			if ( empty( $value ) ) {
				if ( $default ) {
					$value = $default;
				}
				if ( $required ) {
					while ( empty( $value ) ) {
						$value = readline( $message );
					}
				}
			}
			// Dynamic typecasting
			settype( $value, $type );
		}

		return $value;
	}

	/**
	 * Copy a file from one location to another. Does a search and replace on placeholders.
	 *
	 * @param string $from The source file.
	 * @param string $to The destination file.
	 */
	public function copyFile( $from, $to ) {
		$contents = file_get_contents( $from );

		$placeholders = array_map(
			function ( $key ) {
				return "%%{$key}%%";
			},
			array_keys( $this->data )
		);

		// Handle replacements
		$contents = str_replace( $placeholders, array_values( $this->data ), $contents );

		// Remove any remaining or unknown placeholders
		$contents = preg_replace( '/%%(.*)?%%/', '', $contents );

		file_put_contents( $to, $contents );
	}

	/**
	 * Delete a file or directory.
	 *
	 * @param string $path The path to delete.
	 */
	public function delete( $path ) {
		if ( is_dir( $path ) ) {
			$iterator = new RecursiveDirectoryIterator( $path, RecursiveDirectoryIterator::SKIP_DOTS );
			$files    = new RecursiveIteratorIterator( $iterator, RecursiveIteratorIterator::CHILD_FIRST );
			foreach ( $files as $file ) {
				if ( $file->isDir() ) {
					rmdir( $file->getRealPath() );
				} else {
					unlink( $file->getRealPath() );
				}
			}
			rmdir( $path );
		}
		if ( is_file( $path ) ) {
			unlink( $path );
		}
	}

	/**
	 * Determine if the plugin will be publicly released.
	 *
	 * @return bool
	 */
	public function isPublic() {
		$isPublic = '';
		while ( strtolower( $isPublic ) !== 'y' && strtolower( $isPublic ) !== 'n' ) {
			$isPublic = readline( 'Will this plugin be publicly released? [Y/n]' );
			if ( empty( $isPublic ) ) {
				$isPublic = 'y';
			}
		}

		return strtolower( $isPublic ) === 'y';
	}

	/**
	 * Convert a string to alphanumeric characters.
	 *
	 * @param string $string The string to convert.
	 * @param string $extraChars Additional characters to allow.
	 *
	 * @return string
	 */
	public function toAlphaNumeric( $string, $extraChars = '-_' ) {
		return preg_replace( '/[^a-zA-Z0-9' . $extraChars . ']/', '', $string );

	}

	/**
	 * Get the version of WordPress installed on the system.
	 *
	 * @return string
	 */
	function getWPVersion() {
		$wpVersion = '';
		$response  = file_get_contents( 'https://api.wordpress.org/core/stable-check/1.0/' );
		$data      = json_decode( $response, true );
		if ( $data && is_array( $data ) ) {
			$wpVersion = array_key_last( $data );
		}

		return $wpVersion;
	}

	/**
	 * Get the version of Node installed on the system.
	 *
	 * @return string
	 */
	function getNodeVersion() {
		$version = exec( 'node -v' );
		if ( $version ) {
			$version = preg_replace( '/^(v\d+).*$/', '$1', $version );
		}

		return $version;
	}

}

new Setup();

