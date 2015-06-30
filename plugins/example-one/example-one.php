<?php
/**
 * Plugin Name: Example One Ajax MVC
 * Plugin URI:
 * Version:     1.0.0
 * Author:      J Phillip Kohberger
 * Author URI:  http://juicedapps.co
 * Description: Ajax MVC is a Model View Controller framework built on top of Wordpress' AJAX engine and is used for rapid development of rich interactive AJAX Wordpress extensions. 
 */

/**
 * Defines constant for local path.
 *
 * @since 1.0.0
 */
define( 'AM_EXAMPLE_ONE_MODULE_PATH', dirname( __FILE__ ).'/local/' );
function print_rules( $rules ) {
    return $rules;
}
add_filter( 'mod_rewrite_rules', 'print_rules' );