<?php
/*
 * Plugin Name: Plugin Dependencies
 * Plugin URI:  http://wordpress.org/plugins/wp-plugin-dependencies/
 * Description: Manage WordPress plugin dependencies.
 * Version:     0.1
 * Author:      Josh Betz
 * Author URI:  https://joshbetz.com
 * Text Domain: wp-plugin-dependencies
 */

class WP_Plugin_Dependencies {

	static function require_plugin( $plugin ) {
		$file = self::get_plugin_file( $plugin );

		// File doesn't exist
		if ( false === $file ) {
			// TODO: Add admin notice.
			// TODO: Add install link.
			return false;
		}

		// File is already included; mu-plugin or dropin
		elseif ( true === $file ) {
			return true;
		}

		// Include the plugin
		self::include_plugin_with_scope( WP_PLUGIN_DIR . '/' . $file );

		// Set plugin active
		add_filter( 'option_active_plugins', function( $plugins ) use ( $file ) {
			if ( ! in_array( $file, $plugins ) ) {
				$plugins[] = $file;
			}

			return $plugins;
		});

		// Don't allow plugin to be disabled
		if ( ! did_action( 'plugin_action_links_' . $file ) ) {
			add_filter( 'plugin_action_links_' . $file, function( $actions ) {
				return array(
					'<span>Enabled in code.</span>',
				);
			});
		}
	}

	private static function get_plugin_file( $plugin ) {
		$try_files = array(
			sanitize_file_name( $plugin . '.php' ),
		);

		if ( file_exists( WP_CONTENT_DIR . '/' . $try_files[0] ) || file_exists( WPMU_PLUGIN_DIR . '/' . $try_files[0] ) ) {
			return true;
		}

		foreach ( $try_files as $f ) {
			$file = $plugin . '/' . $f;

			if ( file_exists( WP_PLUGIN_DIR . '/' . $file ) ) {
				return $file;
			}
		}

		return false;
	}

	/**
	 * @note We're going to be include()'ing inside of a function,
	 * so we need to do some hackery to get the variable scope we want.
	 * See http://www.php.net/manual/en/language.variables.scope.php#91982
	 */
	private static function include_plugin_with_scope( $file ) {

		// Start by marking down the currently defined variables (so we can exclude them later)
		$pre_include_variables = get_defined_vars();

		// Now include
		include_once( $file );

		// Blacklist out some variables
		$blacklist = array( 'blacklist' => 0, 'pre_include_variables' => 0, 'new_variables' => 0 );

		// Let's find out what's new by comparing the current variables to the previous ones
		$new_variables = array_diff_key( get_defined_vars(), $GLOBALS, $blacklist, $pre_include_variables );

		// global each new variable
		foreach ( $new_variables as $new_variable => $devnull ) {
			global $$new_variable;
		}

		// Set the values again on those new globals
		extract( $new_variables );

		return true;
	}
}

/**
 * wppd_require_plugin
 *
 * Require a plugin to to be active.
 *
 * @param string $plugin The name of the plugin to require.
 * @param float $version (unused) The plugin version to require.
 */
function wppd_require_plugin( $plugin, $version = '' ) {
	WP_Plugin_Dependencies::require_plugin( $plugin );
}

/**
 * wppd_require_module
 *
 * Require a specific module of a plugin to be active.
 *
 * @param string $plugin The name of the plugin to require.
 * @param string $module The name of the module to require.
 * @param float $version (unused) The plugin version to require.
 */
function wppd_require_module( $plugin, $module, $version = null ) {
	wppd_require_plugin( $plugin, $version );
	do_action( 'wppd_require_module', $plugin, $module );
}

