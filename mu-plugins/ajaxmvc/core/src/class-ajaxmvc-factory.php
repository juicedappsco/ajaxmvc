<?php
/**
 * Ajax MVC Core Factory Class.
 *
 * Responsible for verification and instantiation of objects.
 *
 * @since 1.0.0
 */
class ajaxmvc_core_object_factory {
    
    /**
     * Public data array for property overloading.
     *
     * @since 1.0.0
     * @access public
     * @var array
     */
    public $data = array();
    
    /**
     * Utilized for reading data from inaccessible properties.
     *
     * @since 1.0.0
     *
     * @param string $name Name of inaccessible property.
     * @return mixed Value of inaccessible property.
     */
    public function __get( $name ) {
        if ( array_key_exists( $name, $this->data ) ) 
            return $this->data[$name];
    }
    
    /**
     * Utilized for  writing data to inaccessible properties.
     *
     * @since 1.0.0
     *
     * @param string $key Name of inaccessible property.
     * @param mixed  $value Value of inaccessible property.
     */
    public function __set( $key, $value ) {
        $this->data[$key] = $value;
    }

    /**
     * Construct the include path of an MVC object.
     *
     * Verifies that a given include directory exists and returns it as a string.
     *
     * @since 1.0.0
     *
     * @param string $namespace Namespace of a given MVC object.
     * @param string $module Module of a given MVC object.
     * @return string Directory path based on a given Namepace and Module.
     */
    public function get_include_path( $plugin, $namespace, $module ) {
        $local_module_path = WP_CONTENT_DIR.'/plugins';        
        $dir = "{$local_module_path}/{$plugin}/local/{$namespace}/{$module}";
        if ( is_dir( $dir ) ) {
            return $dir;
        } else {
            ajaxmvc_core_exception::throw_error( $this, "directory: {$dir} does not exist" );
        }
    }

    /**
     * Verify a given include actually exists.
     *
     * @since 1.0.0
     *
     * @param string $file Complete file path of a given include.
     * @return bool True or false based on if directory exists
     */
    public function verify_include( $file ) {
        return ( file_exists( $file ) ) ? true : false;
    }

    /**
     * Wrapper for include_once().
     *
     * @since 1.0.0
     *
     * @param string $file Complete file path of a given include.
     */
    public function get_include( $file ) {
        include_once( $file );
    }

    /**
     * Get an output buffer of given content.
     *
     * @since 1.0.0
     *
     * @param string $content Complete file path of a given include or string of content.
     * @return string The buffer.
     */
    public function get_output_buffer( $content ) {
        ob_start();
            if ( $this->verify_include( $content ) ){
                $this->get_include( $content );
            } else {
                echo ( string )$content;
            }
        $buffer = ob_get_clean();
        return $buffer;
    }

    /**
     * Remove any unwanted characters from file or directory name.
     * 
     * This will replace any file or directory names that use a "-" as delimiter 
     * with an underscore as delimiter, this is done to infer class names for
     * both controllers and models from given Namespace, Module, and Instance names
     * which may or may not contain "-" as a delimiter. The current WordPress convention 
     * is to use "-" as a delimiter for both filenames and directories.
     *
     * @since 1.0.0
     *
     * @param string $subject A given directory or file name.
     * @return string The sanitized directory or file name.
     */
    public function sanitize_to_underscore( $subject ){
        return preg_replace( '/[_\-\s]/', '_', $subject );
    }
    
    public function sanitize_to_dashes( &$request ) {
        foreach( array( 'namespace', 'module', 'instance' ) as $key ) {
            $request[$key] = preg_replace( '/_/', '-', $request[$key] );
        }
    }
    
    public function is_integer( $value ) {
        return ( preg_match('/^([0-9]+)$/', $value ) ) ? true : false;
    }
    
    public function array_fracture( $attributes, $keys_or_values ) {
        if ( 1 != preg_match( '/^(keys|values)$/', $keys_or_values ) ) {
            ajaxmvc_core_exception::throw_error( $this, "parameter of {$keys_or_values} is incorrect, it must be either: 'keys' or 'values'.");
        }
        $new_array = array();
        array_walk( $attributes, function( $val, $key ) use( &$new_array, $keys_or_values ) {
            if ( 'keys' == $keys_or_values ) {
                $new_array[] = $key;
            } elseif ( 'values' == $keys_or_values ) {
                $new_array[] = $val;
            }
        } );
        return $new_array;
    }
    
    /**
     * Walk a directory recursively.
     *
     * If callback parameter is supplied then it will be executed on the file
     * of a given iterations directory, if the item is a directory it will recurse.
     * If there is no callback supplied and regex is supplied it will be matched against
     * the file of a given iterations directory, and added to an array of all matched files
     * if the match returns true.
     *
     * @since 1.0.0
     *
     * @param string $dir The directory to walk.
     * @param string $regex Optional. A regular expression to match against files found.
     * @param callable $function Optional. A callback function to be executed on files found.
     * @param array $files Optional. This is used internally only, if regex is supplied, any matches will
     * be pushed onto this array reference.
     * @return array Returns array of matched files if regex given and match successful.
     */
    public function recursive_directory_walk( $dir, $regex = null, callable $function = null, array &$files = null ) {
        //remove trailing slash
        $dir = preg_replace('/\/$/','',$dir);
        // Open the given dir and iterate
        if ( $handle = opendir( $dir ) ) {
            while ( false !== ( $entry = readdir( $handle ) ) ) {
                if ( $entry != "." && $entry != ".." ) {
                    // If the given entry is a dir then make a recursive function call
                    if ( is_dir( "{$dir}/{$entry}" ) ) {
                        $this->recursive_directory_walk( "{$dir}/{$entry}", $regex, $function, $files );
                    } else {
                        // The current entry is a file, if there is a callback then execute it
                        if ( $function !== null ) {
                            $function( "{$dir}/{$entry}" );
                        } elseif ( $regex !== null ) {
                            /*
                             * There is no callback, check for a regex match,
                             * if successful then add it to array which will contain
                             * all of the matches for the given regular expression
                             */
                            if ( preg_match( $regex, "{$dir}/{$entry}" ) ) {
                                $files[] = "{$dir}/{$entry}";
                            }
                        }
                    }
                }
            }
        }
        return $files;
    }
    
    /**
     * Construct WordPress environment.
     *
     * Verifies that wp-blog-header.php exists and includes it.
     *
     * If no file or mutiple files found warning message will be supplied.
     *
     * @since 1.0.0
     */
    public function get_wp_env() {
        // Get an array of all occurences of blog header file
        $require = $this->recursive_directory_walk(
                $_SERVER['DOCUMENT_ROOT'],
                '/wp-blog-header.php$/',
                null );
        /*
         * If there is only one instance then there is one install,
         * then include the file
        */
        if ( count( $require ) == 1 ) {
            require_once( $require[0] );
        } elseif ( count( $require ) > 1 ) {
            echo 'there seems to be more than one installation of wordpress'
                    .'...please contact your site adminstrator regarding this issue';
            return;
        } elseif ( count( $require ) < 1 ) {
            echo 'we cannot find an installation of wordpress'
                    .'...please contact your site adminstrator regarding this issue';
            return;
        }
    }
    
    /**
     * Get the directory path of wp-config.php file.
     *
     * If no file or mutiple files found will return false.
     *
     * @since 1.0.0
     *
     * @return string Directory path of wp-config.php excluding wp-config.php term.
     */
    public function get_wp_wpconfig_path() {
        // Get an array of all occurences of config file
        $wp_config = $this->recursive_directory_walk(
                $_SERVER['DOCUMENT_ROOT'],
                '/wp-config.php$/',
                null );
        // There is either no file found or multiple installs which is not currently supported
        if ( count( $wp_config ) != 1 ) return false;
        // Return the directory path of the given config file
        return preg_replace( '/wp-config.php$/', '', $wp_config[0] );
    }
    
    /**
     * Get the directory path of WordPress root .htaccess file.
     *
     * Checks for existance of WordPress root .htaccess file and returns directory path if exists.
     *
     * @since 1.0.0
     *
     * @return string Directory path of .htaccess file of WordPress root excluding .htaccess term.
     */
    public function get_wp_htaccess_path(){
        // Get WordPress root dir
        $root_path = $this->get_wp_wpconfig_path();
        if ( $root_path == false ) return false;
        // Search for .htaccess file
        $htaccess = $this->recursive_directory_walk(
                $_SERVER['DOCUMENT_ROOT'],
                '/\.htaccess$/',
                null );
        if ( count( $htaccess ) < 1 ) return false;
        foreach ( $htaccess as $key => $value ) {
            $htaccess_path = preg_replace( '/\.htaccess$/', '', $value );
            /*
             * There can be .htaccess files in other directories,
             * we only want the file path if it exists in the same dir
             * as the WordPress root
            */
            if ( $htaccess_path == $root_path ) {
                return $htaccess_path;
            }
        }
        return false;
    }
    
    /**
     * Get the .htaccess file of WordPress root as string.
     *
     * @since 1.0.0
     *
     * @return string The string representation of WordPress root .htaccess file, if no file then returns a blank ''.
     */
    public function get_htaccess_as_string(){
        // Get the .htaccess file path
        $htaccess_path = $this->get_wp_htaccess_path();
        $file_contents = '';
        if ( $htaccess_path != false ) {
            $htaccess_file = file( "{$htaccess_path}.htaccess" );
            // Iterate through the file line by line, build string and return
            foreach ( $htaccess_file as $key => $value ) {
                if ( trim( $value ) != '' ) {
                    $file_contents .= trim( $value ).PHP_EOL;
                }
            }
        }
        return $file_contents;
    }
    
    /**
     * Responsible for instantiating MVC objects.
     *
     * This method will construct include path, verify includes, 
     * verify class existance, verify method existance if given,
     * and return a new object based on those parameters.
     *
     * @since 1.0.0
     *
     * @param string $object A given class name.
     * @param array $request Optional. Array of MVC data.
     * @return object The instantiated object of the given class name.
     */
    public function create_class_object( $object, array $request ) {
        if ( ! $request['namespace'] || ! $request['module'] || ! $request['instance'] ) 
            ajaxmvc_core_exception::throw_error( $this, 'namespace, module, and instance must be specified for each object' );
        // Construct include path
        $namespace_path = $this->get_include_path( $request['plugin'], $request['namespace'], $request['module'] );
        $object_type = substr( $object, strrpos( $object , '_' ) + 1 );
        $file = "{$namespace_path}/{$object_type}/{$request['instance']}.php";
        // If file exists include it
        if ( $this->verify_include( $file ) ) {
            $this->get_include( $file );
        } else {
            ajaxmvc_core_exception::throw_error( $this, "{$object_type} file: {$file} does not exist" );
        }
        // If given object exists create it
        if ( class_exists( $object ) ){
            $new_object = new $object();
        } else {
            ajaxmvc_core_exception::throw_error( $this, "{$object_type} class: {$object} does not exist" );
        }
        /*
         * If the $method parameter is there, then the given method must exist
         * in the given class.
         */
        if ( isset( $request['method'] ) ) {
            if ( method_exists( $object, $request['method'] ) ){
                return $new_object;
            } else {
                ajaxmvc_core_exception::throw_error( $this, "{$object_type} method: {$request['method']} does not exist" );
            }
        } else {
            /*
             * It has already been verified that the given include
             * and class exist, so return the new object 
             */
            return $new_object;
        }
    }
}