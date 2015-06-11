<?php
/**
 * Ajax MVC Core Init Class.
 *
 * Responsible for activation of Ajax MVC environment
 * and various helper functions that aid this process.
 *
 * @since 1.0.0
 */
class ajaxmvc_core_activation extends ajaxmvc_core_object_factory {
    /**
     * Private use of global WordPress db var.
     *
     * @since 1.0.0
     * @access private
     * @var object
     */
    public $model;
    public $wpdb;
    
    public function __construct(){
        $this->wpdb = &$GLOBALS['wpdb'];
    }
    
    public function ajaxmvc_is_activated(){
        try {
            $model = new ajaxmvc_core_model();
            $is_activated = $model->is_core_entity_activated( 'core' );
            return ( $is_activated ) ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function activation_query( $sql ) {
        if ( $this->wpdb->use_mysqli ) {
            return mysqli_query( $this->wpdb->dbh, $sql ) or
            ajaxmvc_core_exception::throw_error( $this,  mysqli_error() );
        } else {
            return mysql_query( $sql, $this->wpdb->dbh ) or
            ajaxmvc_core_exception::throw_error( $this,  mysql_error() );
        }
    }
        
    public function manage_ajaxmvc_activation() {
        /**
         * If is activated already once we dont want to disturb
         * the state of the application (i.e. database) unless otherwise determined
         */ 
        if ( ! $this->ajaxmvc_is_activated() ) {
            $this->activate_ajaxmvc();
        }
    }
    
    public function activate_ajaxmvc(){
        $this->create_ajaxmvc_collection();
        $this->create_ajaxmvc_collection_entity();
        $this->create_ajaxmvc_collection_attribute();
        $this->create_ajaxmvc_collection_entity_attribute();
        $this->create_ajaxmvc_collection_characteristic();
        $this->create_ajaxmvc_collection_characteristic_value();
        $model = new ajaxmvc_core_model();
        $id = $model->get_next_collection_entity_id( $this->wpdb->prefix.'ajaxmvc_core' );
        $model->set_collection_entity_attribute( $this->wpdb->prefix.'ajaxmvc_core', $id, 'ajaxmvc_entity', 'core' );
        $model->set_collection_entity_attribute( $this->wpdb->prefix.'ajaxmvc_core', $id, 'ajaxmvc_activated', 1 );
        $model->set_collection_entity_attribute( $this->wpdb->prefix.'ajaxmvc_core', $id, 'ajaxmvc_version', '1.0.0' );
    }
    
    public function create_ajaxmvc_collection() {
        $collection_name = $this->wpdb->prefix.'ajaxmvc_collection';
        $charset_collate = $this->wpdb->get_charset_collate();
        /**
         * @todo remove the next 3 lines, its just for clearing for testing of demo
         */
        $this->activation_query( "DROP TABLE IF EXISTS {$this->wpdb->prefix}ajaxmvc_example_three_model;" );
        $this->activation_query( "DROP TABLE IF EXISTS {$this->wpdb->prefix}ajaxmvc_example_two_model;" );
        $this->activation_query( "DROP TABLE IF EXISTS {$this->wpdb->prefix}ajaxmvc_example_one_model;" );
        
        $this->activation_query( "DROP TABLE IF EXISTS {$this->wpdb->prefix}ajaxmvc_collection_characteristic_value;" );
        $this->activation_query( "DROP TABLE IF EXISTS {$this->wpdb->prefix}ajaxmvc_collection_characteristic;" );
        $this->activation_query( "DROP TABLE IF EXISTS {$this->wpdb->prefix}ajaxmvc_collection_entity_attribute;" );
        $this->activation_query( "DROP TABLE IF EXISTS {$this->wpdb->prefix}ajaxmvc_collection_attribute;" );
        $this->activation_query( "DROP TABLE IF EXISTS {$this->wpdb->prefix}ajaxmvc_collection_entity;" );
        $this->activation_query( "DROP TABLE IF EXISTS {$this->wpdb->prefix}ajaxmvc_collection;" );
        $sql = "CREATE TABLE {$collection_name} (
                    collection_id mediumint(12) NOT NULL AUTO_INCREMENT,
                    collection varchar(255) NOT NULL,
                    state enum('logical','physical') DEFAULT 'logical',
                    created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (collection_id),
                    UNIQUE KEY (collection)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 {$charset_collate};";
        $this->activation_query( $sql );
        $sql = "CREATE TRIGGER {$collection_name}_update_trigger
                BEFORE UPDATE
                ON {$collection_name}
                FOR EACH ROW SET NEW.updated = NOW();";
        $this->activation_query( $sql );
    }
    
    public function create_ajaxmvc_collection_entity() {
        $parent_collection_name = $this->wpdb->prefix.'ajaxmvc_collection';
        $collection_name = $this->wpdb->prefix.'ajaxmvc_collection_entity';
        $charset_collate = $this->wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$collection_name} (
                    entity_id mediumint(12) NOT NULL AUTO_INCREMENT,
                    collection_id mediumint(12) NOT NULL,
                    collection_entity_id mediumint(12) NOT NULL,
                    created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (entity_id),
                    UNIQUE KEY (collection_id,collection_entity_id),
                    INDEX (collection_entity_id),
                    CONSTRAINT AM_COLL_ENT_COLL_ID_TO_AM_COLL_COLL_ID FOREIGN KEY (collection_id) REFERENCES {$parent_collection_name} (collection_id) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB AUTO_INCREMENT=1 {$charset_collate};";
        $this->activation_query( $sql );
        $sql = "CREATE TRIGGER {$collection_name}_update_trigger
                BEFORE UPDATE
                ON {$collection_name}
                FOR EACH ROW SET NEW.updated = NOW();";
        $this->activation_query( $sql );
    }
    
    public function create_ajaxmvc_collection_attribute() {
        $parent_collection_name = $this->wpdb->prefix.'ajaxmvc_collection';
        $collection_name = $this->wpdb->prefix.'ajaxmvc_collection_attribute';
        $charset_collate = $this->wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$collection_name} (
                    attribute_id mediumint(12) NOT NULL AUTO_INCREMENT,
                    collection_id mediumint(12) NOT NULL,
                    attribute varchar(255) NOT NULL,
                    script_db_type varchar(255) NOT NULL,
                    logical_db_type varchar(255) NOT NULL,
                    physical_db_type varchar(255) NOT NULL,
                    fillable int(1) NOT NULL DEFAULT 0,
                    ordinal_position mediumint(12) DEFAULT 0,
                    created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (attribute_id),
                    UNIQUE KEY (collection_id,attribute),
                    INDEX (attribute),
                    CONSTRAINT AM_COLL_ATTR_COLL_ID_TO_AM_COLL_COLL_ID FOREIGN KEY (collection_id) REFERENCES {$parent_collection_name} (collection_id) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB AUTO_INCREMENT=1 {$charset_collate};";
        $this->activation_query( $sql );
        $sql = "CREATE TRIGGER {$collection_name}_update_trigger
                BEFORE UPDATE
                ON {$collection_name}
                FOR EACH ROW SET NEW.updated = NOW();";
        $this->activation_query( $sql );
    }
    
    public function create_ajaxmvc_collection_entity_attribute() {
        $parent_collection_name = $this->wpdb->prefix.'ajaxmvc_collection_entity';
        $collection_name = $this->wpdb->prefix.'ajaxmvc_collection_entity_attribute';
        $charset_collate = $this->wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$collection_name} (
                    entity_id mediumint(12) NOT NULL,
                    attribute_id mediumint(12) NOT NULL,
                    value varchar(255) NOT NULL,
                    created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (entity_id,attribute_id),
                    INDEX (attribute_id),
                    CONSTRAINT AM_COLL_ENT_ATTR_ENT_ID_TO_AM_COLL_ENT_ENT_ID FOREIGN KEY (entity_id) REFERENCES {$parent_collection_name} (entity_id) ON DELETE CASCADE ON UPDATE CASCADE,
                    CONSTRAINT AM_COLL_ENT_ATTR_ATTR_ID_TO_AM_COLL_ATTR_ATTR_ID FOREIGN KEY (attribute_id) REFERENCES {$this->wpdb->prefix}ajaxmvc_collection_attribute (attribute_id) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB AUTO_INCREMENT=1 {$charset_collate};";
        $this->activation_query( $sql );
        $sql = "CREATE TRIGGER {$collection_name}_update_trigger
                BEFORE UPDATE
                ON {$collection_name}
                FOR EACH ROW SET NEW.updated = NOW();";
        $this->activation_query( $sql );
    }
    
    public function create_ajaxmvc_collection_characteristic() {
        $parent_collection_name = $this->wpdb->prefix.'ajaxmvc_collection';
        $collection_name = $this->wpdb->prefix.'ajaxmvc_collection_characteristic';
        $charset_collate = $this->wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$collection_name} (
                    characteristic_id mediumint(12) NOT NULL AUTO_INCREMENT,
                    collection_id mediumint(12) NOT NULL,
                    characteristic varchar(255) NOT NULL,
                    characteristic_name varchar(255) NOT NULL,
                    created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (characteristic_id),
                    UNIQUE KEY (collection_id,characteristic,characteristic_name),
                    INDEX (collection_id),
                    CONSTRAINT AM_COLL_CHAR_COLL_ID_TO_AM_COLL_COLL_ID FOREIGN KEY (collection_id) REFERENCES {$parent_collection_name} (collection_id) ON DELETE CASCADE ON UPDATE CASCADE                    
                ) ENGINE=InnoDB AUTO_INCREMENT=1 {$charset_collate};";
        $this->activation_query( $sql );
        $sql = "CREATE TRIGGER {$collection_name}_update_trigger
                BEFORE UPDATE
                ON {$collection_name}
                FOR EACH ROW SET NEW.updated = NOW();";
        $this->activation_query( $sql );
    }
    
    public function create_ajaxmvc_collection_characteristic_value() {
        $parent_collection_name = $this->wpdb->prefix.'ajaxmvc_collection_characteristic';
        $collection_name = $this->wpdb->prefix.'ajaxmvc_collection_characteristic_value';
        $charset_collate = $this->wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$collection_name} (
                    characteristic_value_id mediumint(12) NOT NULL AUTO_INCREMENT,
                    characteristic_id mediumint(12) NOT NULL,
                    value varchar(255) NOT NULL,
                    created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (characteristic_value_id),
                    UNIQUE KEY (characteristic_id,value),
                    CONSTRAINT AM_COLL_CHAR_VAL_CHAR_ID_TO_AM_COLL_CHAR_CHAR_ID FOREIGN KEY (characteristic_id) REFERENCES {$parent_collection_name} (characteristic_id) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB AUTO_INCREMENT=1 {$charset_collate};";
        $this->activation_query( $sql );
        $sql = "CREATE TRIGGER {$collection_name}_update_trigger
                BEFORE UPDATE
                ON {$collection_name}
                FOR EACH ROW SET NEW.updated = NOW();";
        $this->activation_query( $sql );
    }
}