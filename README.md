# WP Plugin Dependencies

Based on [wpcom_vip_load_plugin](https://vip.wordpress.com/functions/wpcom_vip_load_plugin/).

## Background

Plugin dependencies don't exist in WordPress. This is how we solve that problem on [WordPress.com VIP](https://vip.wordpress.com).

## Installation

Put this in `wp-content/mu-plugins` to ensure the functions defined here always exist.

## Usage

`wppd_require_plugin( $plugin, $version )` - Includes required plugin or shows an error in wp-admin if it's not available.
