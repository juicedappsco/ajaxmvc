<?php
/**
 * Ajax MVC Core Init Class.
 *
 * Responsible for creation of Ajax MVC environment
 * and various helper functions that aid this process.
 *
 * @since 1.0.0
 */
class ajaxmvc_core_environment extends ajaxmvc_core_object_factory {
    
    /**
     * Include the bootstrap files and include files for all namespaces, if in existance.
     *
     * @since 1.0.0
     */
    public function get_namespace_includes_bootstraps() {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $local_module_path = WP_CONTENT_DIR.'/plugins';
        if ( $pluginhandle = opendir( $local_module_path ) ) {
            while ( false !== ( $plugin = readdir( $pluginhandle ) ) ) {
                if ( preg_match( '/^(..|.)$/', $plugin) == 1 ) continue;
                if ( ! is_dir( $local_module_path."/$plugin/" ) ) continue;
                if ( ! is_dir( "$local_module_path/$plugin/local/" ) ) continue;
                $AM_LOCAL_MODULE_PATH = "$local_module_path/$plugin/local/";
                // Iterate through all namespaces
                if ( $handle = opendir( $AM_LOCAL_MODULE_PATH ) ) {
                    while ( false !== ( $namespace = readdir( $handle ) ) ) {
                        if ( preg_match( '/^(..|.)$/', $namespace) == 1 ) continue;
                        if ( is_dir( $AM_LOCAL_MODULE_PATH.$namespace.'/' ) ) {
                            $is_ajaxmvc_activated = ( new ajaxmvc_core_plugin() )->plugin_is_ajaxmvc_activated($plugin);
                            if ( ! $is_ajaxmvc_activated ) continue;
                            // If there are namespace level includes, include them
                            if ( is_dir( $AM_LOCAL_MODULE_PATH.$namespace.'/includes/src' ) ) {
                                $folder = $AM_LOCAL_MODULE_PATH.$namespace.'/includes/src';
                                $dir = glob( "{$folder}/*.php" );
                                if ( is_array( $dir ) ) {
                                    foreach ( $dir as $filename_with_path ) {
                                        include_once( $filename_with_path );
                                    }
                                }
                            }
                            // If there is a namespace level bootstrap include it
                            if ( file_exists( $AM_LOCAL_MODULE_PATH.$namespace.'/'.$namespace.'.php' ) ) {
                                require_once( $AM_LOCAL_MODULE_PATH.$namespace.'/'.$namespace.'.php' );
                            }
                        }
                    }
                }
            }
        }
    }
    
    public function get_module_models() {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $local_module_path = WP_CONTENT_DIR.'/plugins';
        if ( $pluginhandle = opendir( $local_module_path ) ) {
            while ( false !== ( $plugin = readdir( $pluginhandle ) ) ) {
                if ( preg_match( '/^(..|.)$/', $plugin) == 1 ) continue;
                if ( ! is_dir( $local_module_path."/$plugin/" ) ) continue;
                if ( ! is_dir( "$local_module_path/$plugin/local/" ) ) continue;
                $AM_LOCAL_MODULE_PATH = "$local_module_path/$plugin/local/";
                // Iterate through all namespaces
                if ( $handle = opendir( $AM_LOCAL_MODULE_PATH ) ) {
                    while ( false !== ( $namespace = readdir( $handle ) ) ) {
                        if ( preg_match( '/^(..|.)$/', $namespace) == 1 ) continue;
                        if ( ! is_dir( $AM_LOCAL_MODULE_PATH.$namespace.'/' ) ) continue;
                        $local_mod_namespace_path = $AM_LOCAL_MODULE_PATH.$namespace;
                        $admin_or_front = ( ( ! is_admin() ) ? 'frontend' : 'admin' );
                        if ( $sub_handle = opendir( $local_mod_namespace_path ) ) {
                            while ( false !== ( $module = readdir( $sub_handle ) ) ) {
                                if ( preg_match( '/^(..|.)$/', $local_mod_namespace_path ) != 1 && is_dir( $local_mod_namespace_path.'/'.$module ) == true ) {
                                    /*
                                     * If the module type is specified as frontend or admin
                                     * it must match $admin_or_front otherwise move to the next
                                     * iteration
                                     */
                                    if ( is_dir( $local_mod_namespace_path.'/'.$module.'/model') ) {
                                        $folder = $local_mod_namespace_path.'/'.$module.'/model';
                                        $dir = glob( "{$folder}/*.php" );
                                        if ( is_array( $dir ) ) {
                                            foreach ( $dir as $filename_with_path ) {
                                                include_once( $filename_with_path );
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Get the root directory path of a given WordPress install.
     *
     * Root path based on the location of wp-config.php.
     *
     * @since 1.0.0
     *
     * @return string Root directory path excluding the wp-config.php term.
     */
    public function get_root() {
        return site_url(); 
    }
    
    /**
     * Enqueue core, namespace, and module level JavaScript and CSS files.
     *
     * @since 1.0.0
     */
    public function get_core_namespace_and_module_scripts() {
        $this->get_core_javascript();
        $this->localize_javascript_variables();
        $this->get_namepsace_scripts();
    }

    /**
     * Enqueue core ajaxmvc javascript file.
     *
     * @since 1.0.0
     *
     * @param string $coremodulepath Core module path.
     */
    public function get_core_javascript() {
        preg_match( '/(\/wp-content\/mu-plugins\/[a-zA-Z]+\/core\/)$/', AM_CORE_MODULE_PATH, $match );
        $coremodulepath = $match[0];
        wp_enqueue_script( 'ajaxmvc', $coremodulepath.'js/ajaxmvc.js', array( 'jquery' ), null, true );
    }
    
    public function localize_javascript_variables(){
        return wp_localize_script( 'ajaxmvc', 'ajaxmvc', array(
            'root'  => $this->get_root(),
            'url'   => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce( 'ajaxmvc-nonce' ) ) );
    }
    
    public function get_namepsace_scripts(){
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        preg_match( '/(\/wp-content\/plugins\/[a-zA-Z]+\/local\/)$/', AM_LOCAL_MODULE_PATH, $match );
        $localmodulepath = $match[0];
        $admin_or_front = ( ( ! is_admin() ) ? 'frontend' : 'admin' );
        $local_module_path = WP_CONTENT_DIR.'/plugins';
        if ( $pluginhandle = opendir( $local_module_path ) ) {
            while ( false !== ( $plugin = readdir( $pluginhandle ) ) ) {
                if ( preg_match( '/^(..|.)$/', $plugin) == 1 ) continue;
                if ( ! is_dir( $local_module_path."/$plugin/" ) ) continue;
                if ( ! is_plugin_active( "$plugin/$plugin.php" ) ) continue;
                if ( ! is_dir( "$local_module_path/$plugin/local/" ) ) continue;
                $AM_LOCAL_MODULE_PATH = "$local_module_path/$plugin/local/";
                preg_match( '/(\/wp-content\/plugins\/[a-zA-Z-]+\/local\/)$/', $AM_LOCAL_MODULE_PATH, $match );
                $localmodulepath = $match[0];
                // Iterate through all possible namespaces
                if ( $handle = opendir( $AM_LOCAL_MODULE_PATH ) ) {
                    while ( false !== ( $namespace = readdir( $handle ) ) ) {
                        if ( preg_match( '/^(..|.)$/', $namespace ) == 1 ) continue;
                        /*
                         * If the namespace type is specified as frontend or admin
                         * it must match $admin_or_front otherwise move to the next
                         * iteration
                         */
                        if ( ( 'frontend' == $namespace && $namespace != $admin_or_front ) || ( 'admin' == $namespace && $namespace != $admin_or_front ) ) continue;
                        foreach ( array( 'js', 'css' ) as $extension ) {
                            // If there are namespace level js includes enqueue them
                            if ( is_dir( $AM_LOCAL_MODULE_PATH.$namespace.'/includes/'.$extension ) ) {
                                $folder = $AM_LOCAL_MODULE_PATH.$namespace.'/includes/'.$extension;
                                $dir = glob( "{$folder}/*.{$extension}" );
                                if ( is_array( $dir ) ) {
                                    foreach ( $dir as $filename_with_path ) {
                                        $filename_no_path = substr( $filename_with_path, strrpos( $filename_with_path, '/' ) + 1, strlen( $filename_with_path ) - strrpos( $filename_with_path, '/' ) );
                                        $filename_no_ext = preg_replace('/(.js|.css)$/', '', $filename_no_path);
                                        $script_handle = preg_replace('/\//','-',$namespace.'/includes/'.$extension.'/'.$filename_no_ext );
                                        /*
                                         * If a file is prefixed with frontend or admin
                                         * it must match $admin_or_front otherwise move to the next
                                         * iteration
                                        */
                                        if ( ( preg_match( '/^frontend/', $filename_no_path ) == 1 && 'frontend' != $admin_or_front )
                                        || ( preg_match( '/^admin/', $filename_no_path ) == 1 && 'admin' != $admin_or_front ) )
                                            continue;
                                        if ( 'js' == $extension ) {
                                            wp_enqueue_script( $script_handle, $localmodulepath.$namespace.'/includes/'.$extension.'/'.$filename_no_path, array( 'ajaxmvc' ), null, true );
                                            $args['dependency'] = $script_handle;
                                        } elseif ( 'css' == $extension ) {
                                            wp_enqueue_style( $script_handle, $localmodulepath.$namespace.'/includes/'.$extension.'/'.$filename_no_path );
                                        }
                                    }
                                }
                            }
                        }
                        $this->get_module_scripts( $AM_LOCAL_MODULE_PATH,$localmodulepath, $namespace, $admin_or_front );
                    }
                }
            }
        }
    }
    
    public function get_module_scripts($AM_LOCAL_MODULE_PATH, $localmodulepath, $namespace, $admin_or_front){
        // Iterate through all possible modules
        if ( $sub_handle = opendir( $AM_LOCAL_MODULE_PATH.$namespace ) ) {
            while ( false !== ( $module = readdir( $sub_handle ) ) ) {
                if ( preg_match( '/^(..|.)$/', $namespace ) != 1 && is_dir( $AM_LOCAL_MODULE_PATH.$namespace.'/'.$module ) == true ) {
                    /*
                     * If the module type is specified as frontend or admin
                     * it must match $admin_or_front otherwise move to the next
                     * iteration
                     */
                    if ( ( 'frontend' == $module && $module != $admin_or_front )
                    || ( 'admin' == $module && $module != $admin_or_front ) )
                        continue;
                    if ( is_dir( $AM_LOCAL_MODULE_PATH.$namespace.'/'.$module.'/view') ) {
                        foreach ( array( 'js', 'css' ) as $extension ) {
                            // If there are module level js|css includes enqueue them
                            $folder = $AM_LOCAL_MODULE_PATH.$namespace.'/'.$module.'/view/'.$extension;
                            $dir = glob( "{$folder}/*.{$extension}" );
                            if ( is_array( $dir ) ) {
                                foreach ( $dir as $filename_with_path ) {
                                    $filename_no_path = substr( $filename_with_path, strrpos( $filename_with_path, '/' ) + 1, strlen( $filename_with_path ) - strrpos( $filename_with_path, '/' ) );
                                    $filename_no_ext = preg_replace('/(.js|.css)$/', '', $filename_no_path);
                                    $script_handle = preg_replace('/\//','-',$namespace.'/'.$module.'/view/'.$extension.'/'.$filename_no_ext );
                                    /*
                                     * If a file is prefixed with frontend or admin
                                     * it must match $admin_or_front otherwise move to the next
                                     * iteration
                                    */
                                    if ( ( preg_match( '/^frontend/', $filename_no_path ) == 1 && 'frontend' != $admin_or_front )
                                    || ( preg_match( '/^admin/', $filename_no_path ) == 1 && 'admin' != $admin_or_front ) )
                                        continue;
                                    if ( 'js' == $extension ) {
                                        wp_enqueue_script( $script_handle, $localmodulepath.$namespace.'/'.$module.'/view/'.$extension.'/'.$filename_no_path, array( ( ( isset( $dependency ) ) ? $dependency : 'ajaxmvc' ) ), null, true );
                                    } elseif ( 'css' == $extension ) {
                                        wp_enqueue_style( $script_handle, $localmodulepath.$namespace.'/'.$module.'/view/'.$extension.'/'.$filename_no_path );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}