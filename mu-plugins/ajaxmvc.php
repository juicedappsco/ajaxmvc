<?php
/**
 * Plugin Name: Ajax MVC
 * Plugin URI:
 * Version:     1.0.0
 * Author:      J Phillip Kohberger
 * Author URI:  http://juicedapps.co
 * Description: Ajax MVC is a Model View Controller framework built on top of Wordpress' AJAX engine and is used for rapid development of rich interactive AJAX Wordpress extensions. 
 */

/**
 * Used to turn on descriptive errors.
 * 
 * @since 1.0.0
 */
if ( WP_DEBUG ) {
    define( 'AM_ERRORS_VERBOSE', true );
} else {
    define( 'AM_ERRORS_VERBOSE', true );
}

/**
 * Defines constant for core path.
 * 
 * @since 1.0.0
 */
define( 'AM_CORE_MODULE_PATH', dirname( __FILE__ ).'/ajaxmvc/core/' );

/**
 * Core factory class.
 */
require_once( AM_CORE_MODULE_PATH.'src/class-ajaxmvc-factory.php' );

/**
 * Core exception class.
 */
require_once( AM_CORE_MODULE_PATH.'src/class-ajaxmvc-exception.php' );

/**
 * Core model class.
 */
require_once( AM_CORE_MODULE_PATH.'src/class-ajaxmvc-model.php' );

/**
 * Core view class.
 */
require_once( AM_CORE_MODULE_PATH.'src/class-ajaxmvc-view.php' );

/**
 * Core controller class.
 */
require_once( AM_CORE_MODULE_PATH.'src/class-ajaxmvc-controller.php' );

/**
 * Core router class.
 */
require_once( AM_CORE_MODULE_PATH.'src/class-ajaxmvc-router.php' );

/**
 * Core environment class.
 */
require_once( AM_CORE_MODULE_PATH.'src/class-ajaxmvc-environment.php' );

/**
 * Core activation class.
 */
require_once( AM_CORE_MODULE_PATH.'src/class-ajaxmvc-activation.php' );

/**
 * Core plugin class.
 */
require_once( AM_CORE_MODULE_PATH.'src/class-ajaxmvc-plugin.php' );

/**
 * Instantiate core router object and add it to
 * both frontend and admin ajax hooks.
 */
$ajaxmvc_core_router = new ajaxmvc_core_router();
add_action( 'wp_ajax_nopriv_route_request',
    array ( $ajaxmvc_core_router, 'route_request' ) );
add_action( 'wp_ajax_route_request',
    array ( $ajaxmvc_core_router, 'route_request' ) );

/**
 * Instantiate core env object,
 * add get_core_namespace_and_module_scripts (which will actually enqueue scripts) 
 * method to both frontend and admin
 * enqueue hooks.
 */
$ajaxmvc_core_environment = new ajaxmvc_core_environment();
add_action( 'wp_enqueue_scripts', 
    array ( $ajaxmvc_core_environment, 'get_core_namespace_and_module_scripts' ) );
add_action( 'admin_enqueue_scripts', 
    array( $ajaxmvc_core_environment, 'get_core_namespace_and_module_scripts' ) );

/**
 * get all models for activation
 */
$ajaxmvc_core_environment->get_module_models();

/**
 * activate the core
 */
$ajaxmvc_core_activation = new ajaxmvc_core_activation();
$ajaxmvc_core_activation->manage_ajaxmvc_activation();

/**
 * set plugin state
 */
$ajaxmvc_core_plugin = new ajaxmvc_core_plugin();
$ajaxmvc_core_plugin->manage_plugin_state();

/**
 * load plugins namespaces etc.
 */
$ajaxmvc_core_environment->get_namespace_includes_bootstraps();
