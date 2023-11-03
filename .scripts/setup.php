<?php

namespace wpscholar\WordPress\Plugin;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WP_Forge\Helpers\Str;

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

class Setup {

	public $data = [];

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

	public function __construct() {

		echo 'Welcome to the WordPress plugin setup wizard!' . PHP_EOL . PHP_EOL;

		$isPublic = $this->isPublic();

		$path = dirname( __DIR__ );

		$pluginSlug = basename( $path );
		$wpVersion  = $this->getWPVersion();

		// Set known values
		$this->set( 'currentYear', date( 'Y' ) );
		$this->set( 'gitUserName', exec( 'git config --global user.name' ) );
		$this->set( 'nodeVersion', $this->getNodeVersion() );
		$this->set( 'pluginAuthor', $this->get( 'gitUserName' ) );
		$this->set( 'pluginPhpVersion', preg_replace( '/^(\d+\.\d+).*$/', '$1', phpversion() ) );
		$this->set( 'pluginSlug', $pluginSlug );
		$this->set( 'pluginTextDomain', $pluginSlug );
		$this->set( 'pluginWpVersion', preg_replace( '/^(\d+\.\d+).*$/', '$1', $wpVersion ) );
		$this->set( 'port', rand( 1000, 9998 ) );
		$this->set( 'testPort', $this->get( 'port' ) + 1 );

		// Set default values
		$this->prompts['pluginName']['default']        = 'Micah\'s Plugin';
		$this->prompts['pluginDescription']['default'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
		$this->prompts['pluginAuthorUri']['default']   = 'https://wpscholar.com/';
		$this->prompts['pluginUri']['default']         = 'https://wpscholar.com/' . $this->get( 'pluginSlug' );

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
		$this->set( 'pluginConstantPrefix', preg_replace( '/[^a-zA-Z0-9_-]/', '', strtoupper( Str::snake( strtolower( $this->get( 'pluginName' ) ) ) ) . '_' ) );
		$this->set( 'pluginNamespace', preg_replace( '/[^a-zA-Z0-9_-]/', '', Str::studly( $this->get( 'pluginName' ) ) ) );
		$this->set( 'pluginPackage', preg_replace( '/[^a-zA-Z0-9_-]/', '', Str::studly( $this->get( 'pluginName' ) ) ) );
		$this->set( 'vendorSlug', Str::kebab( $this->get( 'vendorSlug' ) ) );

		ksort( $this->data );

		echo PHP_EOL . 'Here is the data that will be used to generate your plugin:' . PHP_EOL;

		echo json_encode( $this->data, JSON_PRETTY_PRINT ) . PHP_EOL . PHP_EOL;

		echo 'Generating plugin...' . PHP_EOL . PHP_EOL;

		$this->copyFile( "{$path}/.wp-env.json", "{$path}/.wp-env.json" );
		$this->copyFile( "{$path}/.templates/README.md", "{$path}/README.md" );
		$this->copyFile( "{$path}/readme.txt", "{$path}/readme.txt" );
		$this->copyFile( "{$path}/wp-plugin.php", "{$path}/{$this->get('pluginSlug')}.php" );

		mkdir( '{$path}/.github' );
		mkdir( '{$path}/.github/workflows' );

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

		exec( 'git init' );

		echo 'Setting up Composer...' . PHP_EOL . PHP_EOL;

		$this->copyFile( "{$path}/.templates/composer.json", "{$path}/composer.json" );
		$this->delete( "{$path}/vendor" );
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

		echo 'You can now run `npm start` to start the development environment.' . PHP_EOL . PHP_EOL;

	}

	public function set( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	public function has( $key ) {
		return isset( $this->data[ $key ] );
	}

	public function get( $key ) {
		return $this->has( $key ) ? $this->data[ $key ] : null;
	}

	public function remove( $key ) {
		if ( $this->has( $key ) ) {
			unset( $this->data[ $key ] );
		}
	}

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

	function getWPVersion() {
		$wpVersion = '';
		$response  = file_get_contents( 'https://api.wordpress.org/core/stable-check/1.0/' );
		$data      = json_decode( $response, true );
		if ( $data && is_array( $data ) ) {
			$wpVersion = array_key_last( $data );
		}

		return $wpVersion;
	}

	function getNodeVersion() {
		$version = exec( 'node -v' );
		if ( $version ) {
			$version = preg_replace( '/^(v\d+).*$/', '$1', $version );
		}

		return $version;
	}

}

new Setup();

