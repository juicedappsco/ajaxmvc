<?php
/**
 * Ajax MVC Core Init Class.
 *
 * Responsible for activation/deactivation of Ajax MVC Plugin environment
 * and various helper functions that aid this process.
 *
 * @since 1.0.0
 */
class ajaxmvc_core_state extends ajaxmvc_core_object_factory {
    /**
     * Private use of global WordPress db var.
     *
     * @since 1.0.0
     * @access private
     * @var object
     */
    public $wpdb;
    
    public function __construct(){
        $this->wpdb = &$GLOBALS['wpdb'];
    }
    
    public function plugin_is_ajaxmvc_activated( $entity ) {
        try {
            $model = new ajaxmvc_core_model();
            return $model->is_core_entity_activated( $entity );
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function activate_ajaxmvc_plugin( $plugin ) {
        $model = new ajaxmvc_core_model();
        $id = $model->get_collection_entity_id_by_attribute( $this->wpdb->prefix.'ajaxmvc_core', 'ajaxmvc_entity', $plugin );
        if ( ! $id ) {
            $id = $model->get_next_collection_entity_id( $this->wpdb->prefix.'ajaxmvc_core' );
            $model->set_collection_entity_attribute( $this->wpdb->prefix.'ajaxmvc_core', $id, 'ajaxmvc_entity', $plugin );
            $model->set_collection_entity_attribute( $this->wpdb->prefix.'ajaxmvc_core', $id, 'ajaxmvc_version', '1.0.0' );
        }
        $model->set_collection_entity_attribute( $this->wpdb->prefix.'ajaxmvc_core', $id, 'ajaxmvc_activated', 1 );
    }
    
    public function deactivate_ajaxmvc_plugin( $plugin ) {
        $model = new ajaxmvc_core_model();
        $id = $model->get_collection_entity_id_by_attribute( $this->wpdb->prefix.'ajaxmvc_core', 'ajaxmvc_entity', $plugin );
        if ( ! $id ) return false;
        $model->set_collection_entity_attribute( $this->wpdb->prefix.'ajaxmvc_core', $id, 'ajaxmvc_activated', 0 );
    }
    
    /**
     * Call deactivation/activation method for all namespaces, if in existance.
     *
     * Activation method must be named <namespace>_deactivation/activation,
     * reside in <namespace>_deactivation/activation class, and must
     * exist in /includes/src/class-<namespace>-deactivation/activation.php file to be executed.
     *
     * @since 1.0.0
     */
    public function manage_plugin_state(){
        $plugin = false;
        if ( $_REQUEST['action'] == 'deactivate' ) {
            $local_module_path = WP_CONTENT_DIR.'/plugins';
            $plugin = preg_replace( '/\/(.*)$/', '', $_REQUEST['plugin'] );
            $state = 'deactivat';
            $this->deactivate_ajaxmvc_plugin( $plugin );
        } elseif ( $_REQUEST['action'] == 'activate' ) {
            $local_module_path = WP_CONTENT_DIR.'/plugins';
            $plugin = preg_replace( '/\/(.*)$/', '', $_REQUEST['plugin'] );
            $state = 'activat';
            $this->activate_ajaxmvc_plugin( $plugin );
        }
        if ( ! $plugin ) return;
        if ( is_dir( "$local_module_path/$plugin/local/" ) ) {
            $AM_LOCAL_MODULE_PATH = "$local_module_path/$plugin/local/";
            // Iterate through all namespaces
            if ( $handle = opendir( $AM_LOCAL_MODULE_PATH ) ) {
                while ( false !== ( $namespace = readdir( $handle ) ) ) {
                    if ( preg_match( '/^(..|.)$/', $namespace) == 1 ) continue;
                    if ( ! is_dir( $AM_LOCAL_MODULE_PATH.$namespace.'/' ) ) continue;
                    foreach( array( 'mysql', 'src' ) as $state_file_type ){
                        // If there are namespace level includes, include them
                        if ( ! is_dir( $AM_LOCAL_MODULE_PATH.$namespace.'/includes/'.$state_file_type ) ) continue;
                        if( 'mysql' == $state_file_type ) {
                            if ( file_exists( $AM_LOCAL_MODULE_PATH.$namespace.'/includes/mysql/'.$namespace.'-'.$state.'ion.sql' ) ) {
                                $file = $AM_LOCAL_MODULE_PATH.$namespace.'/includes/mysql/'.$namespace.'-'.$state.'ion.sql';
                                $this->execute_sql_state( $file );
                            }
                        } elseif( 'src' == $state_file_type ) {
                            if ( ! file_exists( $AM_LOCAL_MODULE_PATH.$namespace.'/includes/src/class-'.$namespace.'-'.$state.'ion.php' ) ) continue;
                            require_once( $AM_LOCAL_MODULE_PATH.$namespace.'/includes/src/class-'.$namespace.'-'.$state.'ion.php' );
                            $namespace_clean = $this->sanitize_to_underscore( $namespace );
                            if ( ! class_exists( $namespace_clean.'_'.$state.'ion' ) ) continue;
                            // If a namespace state class exists in this file
                            $object = $namespace_clean.'_'.$state.'ion';
                            $ajaxmvc_plugin_state = new $object();
                            // If there is an state method, then execute
                            if ( method_exists( $ajaxmvc_plugin_state, $namespace_clean.'_'.$state.'e' ) ) {
                                $method = $namespace_clean.'_'.$state.'e';
                                $ajaxmvc_plugin_state->$method();
                            }
                        }
                    }
                }
            }
        }
    }
    
    public function execute_sql_state( $file ){
        $this->sanitize_namespace_sql_state_prefix( $file );
        $this->execute_sql_file( $file );
    }
    
    public function sanitize_namespace_sql_state_prefix( $file ){
        $command = "grep -rl 'AM_DB_PREFIX_' {$file} | xargs sed -i 's/AM_DB_PREFIX_/{$this->wpdb->prefix}/g'";
        $output = shell_exec( $command );
    }
    
    public function execute_sql_file( $file ){
        if ( ! file_exists( $file ) ) return false;
        $user = DB_USER;
        $passwd = DB_PASSWORD;
        $host = DB_HOST;
        $db = DB_NAME;
        $command = "mysql -u {$user} -p'{$passwd}' -h {$host} -D {$db} < {$file}";
        $output = shell_exec( $command );
    }
}