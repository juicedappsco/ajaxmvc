<?php
/**
 * PLEASE EXCUSE THE LACK OF COMMENTS AND FORMATTING THIS IS A WORK IN PROGRESS
 *
 * Ajax MVC Core Model Class.
 *
 * Responsible for instantiating a given namespace module's model class, and ORM.
 *
 * @since 1.0.0
 */
class ajaxmvc_core_model extends ajaxmvc_core_object_factory {

    /**
     * Private use of global WordPress db var.
     *
     * @since 1.0.0
     * @access private
     * @var object 
     */
    public $wpdb;
    
    public $state;
    
    public $name;
    
    protected $fillable =  array('ajaxmvc_entity','ajaxmvc_activated','ajaxmvc_version');
    
    public $primary_key = 'id';
    
    public $break = '<br />';
    
    public $query_order = array();
    
    public $last_query_clause;
    
    public $last_query_clause_index;
    
    public $insert_into = array();
    
    public $values;
    
    public $update = false;
    
    public $set;
    
    public $delete = false;
    
    public $select = false;
    
    public $from;
    
    public $join;
    
    public $where;
    
    public $group_by;
    
    public $having;
    
    public $order_by;
    
    public $limit;
    
    public $mysql_error;
    
    public $is_transaction = false;
    
    public $save;
    
    public $destroy;
    
    public $is_modification_query = false;
    
    public $collections = array();
    
    public $join_meta_data = array();
    
    public $update_multiple_tables = false;
    
    public $is_from_join = false;
    
    public $update_set_array = array();
    
    public $delete_array = array();
    
    public $insert_into_array = array();
    
    public $insert_into_values_array = array();

    /**
     * Sets private property $wpdb to global WordPress db object.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->wpdb = &$GLOBALS['wpdb'];
        $this_model = get_class( $this );
        if ( 'ajaxmvc_core_model' != $this_model ) {
            $this->name = "{$this->wpdb->prefix}ajaxmvc_{$this_model}";
        } else {
            $this->name = preg_replace( '/_model$/', '',"{$this->wpdb->prefix}{$this_model}");
        }
        if( $this->is_collection( $this->name ) ) {
            $this->state = $this->get_collection_state( $this->name );
        } else {
            $this->set_collection( $this->name );
            $this->state = $this->get_collection_state( $this->name );
        }
        if ( 'logical' == $this->state ) {
            $this->set_all_fillable_attributes();
            $this->set_all_collection_characteristics();
            $this->set_all_collection_physical_attribute_type_properties();
        }
    }

    /**
     * Instantiates and returns a given namespace module's model class.
     *
     * Receives the request parameter array from the router,
     * sanitizes the instance parameter, creates the model
     * object via parent method, binds a new database object to itself,
     * and then returns it to the router.
     *
     * @since 1.0.0
     *
     * @param  array $request Request parameters passed in from router.
     * @return model Class of given namespace module's model.
     */
    public function create_model_object( array $request ) {
        /*
         * Unset method from the request object
         * as execution of a method is not required for
         * model instantiation
         */
        unset( $request['method'] );
        $model_class = $this->sanitize_to_underscore( $request['instance'] ) . '_model';
        $namespace_path = $this->get_include_path( $request['plugin'], $request['namespace'], $request['module'] );
        $file = "{$namespace_path}/model/{$request['instance']}.php";
        if ( $this->verify_include( $file ) ) {
            $model = $this->create_class_object( $model_class, $request );
            return $model;
        } else {
            return null;
        }
    }
    
    /**
     * Prefixes an unprefixed collection name.
     *
     * @since 1.0.0
     *
     * @param string $collection Unprefixed collection to be prefixed.
     * @return string Prefixed collection name.
     */
    public function get_prefixed_collection( $unprefixed_collection ) {
        return $this->wpdb->prefix.$unprefixed_collection;
    }

    /**
     * Get count based on an arbitrary SQL WHERE clause.
     *
     * Prefixes the collection name, prepares the statement, and executes.
     *
     * @since 1.0.0
     *
     * @param string $collection Unprefixed collection where data is to be counted.
     * @param string $clauses Optional. WHERE conditional statement.
     * @param array  $param_array Optional. Array of the raw data to be prepared.
     * @return int Count of data.
     */
    public function _count( $collection, $clauses = null, array $param_array = null ) {
        $results = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) AS count 
                 FROM {$collection}
                 {$clauses}", 
                 $param_array ) );
        $this->handle_query_error( 'get_var', $results );
        return $results;
    }

    /**
     * Get results based on arbitrary SQL clauses.
     *
     * Prefixes the collection name, prepares the statement, and executes.
     *
     * @since 1.0.0
     *
     * @param string $collection Unprefixed collection where data is to be queried.
     * @param string $attributes Fields to be selected.
     * @param string $clauses Optional. WHERE conditional statement, can contain GROUP BY, hence the plural: clauses.
     * @param array  $param_array Optional. Array of the raw data to be prepared.
     * @return array results of queried data.
     */
    public function _get_results( $attributes, $collection, $clauses = null, array $param_array = null ) {
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT {$attributes}
                 FROM {$collection}
                 {$clauses}",
                 $param_array
            ),
            ARRAY_A );
        $this->handle_query_error( 'get_results', $results );
        return $results;
    }

    /**
     * Get database row based on arbitrary SQL clauses.
     *
     * Prefixes the collection name, prepares the statement, and executes.
     *
     * @since 1.0.0
     *
     * @param string $collection Unprefixed collection where data is to be queried.
     * @param string $attributes Fields to be selected.
     * @param string $clauses Optional. WHERE conditional statement, can contain GROUP BY, hence the plural: clauses.
     * @param array  $param_array Optional. Array of the raw data to be prepared.
     * @return array Row of queried data.
     */
    public function _get_row( $attributes, $collection, $clauses = null, array $param_array = null ) {
        $results = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT {$attributes}
                 FROM {$collection}
                 {$clauses}",
                 $param_array
            ),
            ARRAY_A );
        $this->handle_query_error( 'get_row', $results );
        return $results;
    }
    
    /**
     * Wrapper for WordPress update method.
     *
     * Prefixes the collection name and executes.
     *
     * @since 1.0.0
     *
     * @param string $collection Unprefixed collection to be updated.
     * @param array  $data Columns to be updated, column => value pairs.
     * @param array  $data_conditions Conditions of the update, column => value pairs.
     * @param array  $data_format Columns value format.
     * @param array  $data_conditions_format Conditions value format.
     * @return mixed Returns false if errors, or the number of rows affected if successful.
     * $wpdb::update( $collection, $data, $where, $format = null, $where_format = null )
     */
    public function _update( $collection, array $data,  array $data_conditions, array $data_format, array $data_conditions_format ) {
        $results = $this->wpdb->update(
                $collection,
                $data,
                $data_conditions,
                $data_format,
                $data_conditions_format );
        $this->handle_query_error( 'update', $results );
        return $results;
    }
    
    /**
     * Wrapper for WordPress insert method.
     *
     * Prefixes the collection name and executes.
     *
     * @since 1.0.0
     *
     * @param string $collection Unprefixed collection where data is to be inserted.
     * @param array  $data Data to be inserted, column => value pairs.
     * @param array  $format Format of the insert column values.
     * @return mixed Returns false if errors, or the number of rows affected if successful.
     * $wpdb::insert( $collection, $data, $format );
     */
    public function _insert( $collection, array $data, array $format ){
        $results = $this->wpdb->insert(
                $collection,
                $data,
                $format );
        $this->handle_query_error( 'insert', $results );
        return $results;
    }
    
    public function _multi_insert( $collection, array $data ) {
        foreach( $data as $insert_array ) {
            $results = $this->_insert( $collection, $insert_array[0], $insert_array[1] );
            if ( 1 == $results ) $count++;
        }
        return $count;
    }
    
    /**
     * Wrapper for WordPress delete method.
     *
     * Prefixes the collection name and executes.
     *
     * @since 1.0.0
     *
     * @param string $collection Unprefixed collection where data is to be deleted.
     * @param array  $data Data to be deleted, column => value pairs.
     * @param array  $format Format of the delete column values.
     * @return mixed Returns false if errors, or the number of rows affected if successful.
     */
    public function _delete( $collection, array $data, array $format ){
        $results = $this->wpdb->delete(
                $collection,
                $data,
                $format );
        $this->handle_query_error( 'delete', $results );
        return $results;
    }
    
    /**
     * Get database var based on arbitrary SQL clauses.
     *
     * Prefixes the collection name, prepares the statement, and executes.
     *
     * @since 1.0.0
     *
     * @param string $collection Unprefixed collection where data is to be queried.
     * @param string $attribute Field to be selected.
     * @param string $clauses Optional. WHERE conditional statement, can contain GROUP BY, hence the plural: clauses.
     * @param array  $param_array Optional. Array of the raw data to be prepared.
     * @return mixed Var of queried attribute.
     */
    public function _get_var( $attribute, $collection, $clauses = null, array $param_array = null ) {
        $results = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT {$attribute}
                 FROM {$collection}
                 {$clauses}", 
                 $param_array ) );
        $this->handle_query_error( 'get_var', $results );
        return $results;
    }
    
    /**
     * Utility functions
     */
    
    public function print_results( $results ) {
?>
    <?php if ( is_array( $results ) ): ?>
        <table>
            <thead>
                <tr>
                    <?php if ( is_array( $results[0] ) ): ?>
                        <?php foreach ( $results[0] as $attribute => $value ): ?>
                                <th style="text-align: center;">
                                    <?php echo ucwords( strtolower( preg_replace('/(-|_)/', ' ', $attribute ) ) );?>
                                </th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $results as $index => $record ): ?>
                    <tr>
                        <?php foreach ( $record as $attribute => $value ): ?>
                            <td style="text-align: center;">
                                <?php echo $value.':'.gettype( $value ); ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php
    }
     
    public function query( $sql ) {
        if ( $this->wpdb->use_mysqli ) {
            return mysqli_query( $this->wpdb->dbh, $sql ) or 
            $this->handle_query_error( 'mysql_query', mysqli_error().' '.$sql );
        } else {
            return mysql_query( $sql, $this->wpdb->dbh ) or 
            $this->handle_query_error( 'mysql_query', mysql_error().' '.$sql );
        }
    }
    
    public function create_physical_collection( $sql ) {
        return $this->query( $sql );
    }
    
    public function get_logical_collection_storage_type( $type ) {
        $type = strtolower( $type );
        $php_to_mysql_map = array(
            'boolean'   =>  'SIGNED',
            'integer'   =>  'SIGNED',
            'double'    =>  'DECIMAL(12,6)',
            'string'    =>  'CHAR'
        );
        if ( ! array_key_exists( $type, $php_to_mysql_map ) ) {
            ajaxmvc_core_exception::throw_error( $this,  "{$type} is not a supported database storage type." );
        } else {
            return $php_to_mysql_map[$type];
        }
    }
    
    public function get_statement_prepare_type( $type ) {
        $type = strtolower( $type );
        $php_to_mysql_map = array(
                'boolean'   =>  '%d',
                'integer'   =>  '%d',
                'double'    =>  '%f',
                'string'    =>  '%s'
        );
        if ( ! array_key_exists( $type, $php_to_mysql_map ) ) {
            ajaxmvc_core_exception::throw_error( $this,  "{$type} is not a supported data format type." );
        } else {
            return $php_to_mysql_map[$type];
        }
    }
    
    public function cast_as_logical_collection_storage_type( $value ) {
        if ( 'boolean' == gettype( $value ) ) {
            return ( string ) ( false == $value ) ? 0 : 1;
        } else {
            return ( string ) $value;
        }
    }
    
    public function get_php_type_from_mysql( $collection, $attribute_offset ) {
        $collection_name = $this->get_collection_name( $collection );
        $sql = "SELECT * FROM {$collection_name} LIMIT 0,1";
        if ( $this->wpdb->use_mysqli ) {
            $result = mysqli_query( $this->wpdb->dbh, $sql ) or
            $this->handle_query_error( 'mysql_query', mysqli_error().' '.$sql );
            $attribute = mysqli_fetch_field_direct( $result, $attribute_offset );
            return $this->infer_php_type( $attribute->type, 'mysqli' );
        } else {
            $result = mysql_query( $sql, $this->wpdb->dbh ) or
            $this->handle_query_error( 'mysql_query', mysql_error().' '.$sql );
            $attribute = mysql_fetch_field( $result, $attribute_offset );
            return $this->infer_php_type( $attribute->type, 'mysql' );
        }
    }
    
    /**
     * @todo need to make the sql types more robust
     */
    
    public function infer_php_type( $attribute_type, $mysql_type ) {
        if ( 'mysqli' == $mysql_type ) {
            $attribute_type = ( integer ) $attribute_type;
            switch( $attribute_type ) {
                case 3:
                    $type = 'integer';
                    break;
                case 4:
                    $type = 'double';
                    break;
                default:
                    $type = 'string';
                    break;
            }
        } elseif ( 'mysql' == $mysql_type ) {
            $attribute_type = strtoupper( $attribute_type );
            switch( $attribute_type ) {
                case 'TINY':
                case 'SHORT':
                case 'LONG':
                case 'LONGLONG':
                case 'INT24':
                case 'INT':
                    $type = 'integer';
                    break;
                case 'DECIMAL':
                case 'DEC':
                case 'FLOAT':
                case 'REAL':
                    $type = 'double';
                    break;
                default:
                    $type = 'string';
                    break;
            }
        }
        return $type;
    }
    
    public function cast_as_physical_collection_storage_type( $value ) {
        if ( 'boolean' == gettype( $value ) ) {
            return ( integer ) ( false == $value ) ? 0 : 1;
        } else {
            return $value;
        }
    }
    
    public function sanitize_single_sql_input( $string ) {
        return preg_replace(
                '/((^(\'|\"))|((\'|\")$))/',
                '',
                $this->wpdb->prepare( '%s', array( ( string ) $string ) ) );
    }
    
    /**
     * @todo do we want exceptions for non transactional?
     */
     
    public function handle_query_error( $method, $results ) {
        if ( $this->is_transaction ) {
            if ( 'get_results' == $method && empty( $results ) && $this->wpdb->last_error ) {
                $this->mysql_error = $this->wpdb->last_error;
                ajaxmvc_core_exception::throw_error( $this,  $this->mysql_error );
            } elseif ( ( 'get_row' == $method || 'get_var' == $method ) 
              && null == $results && $this->wpdb->last_error ) {
                $this->mysql_error = $this->wpdb->last_error;
                ajaxmvc_core_exception::throw_error( $this,  $this->mysql_error );
            } elseif ( ( 'insert' == $method )
                    && ( $results < 1 || false === $results ) ) {
                $query = $this->wpdb->last_query;
                $this->mysql_error = "$query: the {$method} operation was not successful.";
                ajaxmvc_core_exception::throw_error( $this,  $this->mysql_error );
            } elseif ( ( 'update' == $method || 'delete' == $method )
                    && ( false === $results ) ) {
                $query = $this->wpdb->last_query;
                $this->mysql_error = "the {$method} operation was not successful.";
                ajaxmvc_core_exception::throw_error( $this,  $this->mysql_error );
            } elseif ( 'mysql_query' == $method && $results ) {
                $this->mysql_error = $results;
                ajaxmvc_core_exception::throw_error( $this,  $this->mysql_error );
            }
        } else {
            if ( 'get_results' == $method && empty( $results ) && $this->wpdb->last_error ) {
                $this->mysql_error = $this->wpdb->last_error;
                ajaxmvc_core_exception::throw_error( $this,  $this->mysql_error );
            } elseif ( ( 'get_row' == $method || 'get_var' == $method )
                    && null == $results && $this->wpdb->last_error ) {
                $this->mysql_error = $this->wpdb->last_error;
                ajaxmvc_core_exception::throw_error( $this,  $this->mysql_error );
            } elseif ( ( 'insert' == $method )
                    && ( $results < 1 || false === $results ) ) {
                $query = $this->wpdb->last_query;
                $error = $this->wpdb->last_error;
                $this->mysql_error = "$error $query: --$results-- the {$method} operation was not successful.";
                ajaxmvc_core_exception::throw_error( $this,  $this->mysql_error );
            } elseif ( ( 'update' == $method || 'delete' == $method )
                    && ( false === $results ) ) {
                $query = $this->wpdb->last_query;
                $this->mysql_error = "the {$method} operation was not successful.";
                ajaxmvc_core_exception::throw_error( $this,  $this->mysql_error );
            } elseif ( 'mysql_query' == $method && $results ) {
                $this->mysql_error = $results;
                ajaxmvc_core_exception::throw_error( $this,  $this->mysql_error );
            }
        }
    }
    
    public function start_transaction() {
        $results = $this->query( "START TRANSACTION" );
        $this->is_transaction = true;
        return $results;
    }
    
    public function commit_transaction() {
        $results = $this->query( "COMMIT" );
        $this->is_transaction = false;
        return $results;
    }
    
    public function rollback_transaction() {
        $results = $this->query( "ROLLBACK" );
        $this->is_transaction = false;
        return $results;
    }
    
    public function transaction( callable $method, callable $rollback = null ) {
        try {
            $this->start_transaction();
            $method();
            $this->commit_transaction();
        } catch ( Exception $e ) {
            $this->rollback_transaction();
            if ( $rollback ) $rollback();
            $message =  "<strong>MySQL Transaction</strong>: {$e->getMessage()} ";
            ajaxmvc_core_exception::throw_error( $this,  $message );
            die();
        }
    }
    
    /**
     * Collection Characteristics
     */
    
    public function set_all_collection_characteristics() {
        if ( 'string' != gettype( $this->primary_key ) ) {
            ajaxmvc_core_exception::throw_error( $this, '$this->primary_key can only be of type string.' );
        }
        $this->primary_key = array(
            'primary'  => array(
                $this->primary_key,
            ),
        );
        $characteristics = array( 'primary_key', 'index', 'unique_key', 'foreign_key' );
        foreach( $characteristics as $characteristic ) {
            if ( isset( $this->$characteristic ) && is_array( $this->$characteristic ) ) {
                foreach( $this->$characteristic as $characteristic_name => $characteristics_values ) {
                    if ( is_array( $characteristics_values ) ) {
                        $this->set_collection_characteristic_values( $this->name, $characteristic, $characteristic_name, $characteristics_values );
                    } else {
                        $this->set_collection_characteristic_values( $this->name, $characteristic, $characteristic_name, array( $characteristics_values ) );
                    }
                }
            }
        }
        $this->primary_key = $this->primary_key['primary'][0];
    }
    
    public function is_collection_characteristic( $collection, $characteristic, $characteristic_name ) {
        return ( $this->get_collection_characteristic_id( $collection, $characteristic, $characteristic_name ) ) ? true : false;
    }
    
    public function get_collection_characteristic_values_by_name( $collection, $characteristic, $characteristic_name ) {
        if ( $this->is_collection_characteristic( $collection, $characteristic, $characteristic_name ) ) {
            $characteristic_id = $this->get_collection_characteristic_id( $collection, $characteristic, $characteristic_name );
        } else {
            return false;
        }
        $results = $this->_get_results( 
            'value',
            $this->wpdb->prefix."ajaxmvc_collection_characteristic_value",
            "WHERE characteristic_id = '%d'",
            array( ( integer ) $characteristic_id ) );
        return $results;
    }
    
    public function get_collection_characteristic_primary_key(  $collection  ) {
        if ( $this->is_collection_characteristic( $collection, 'PRIMARY_KEY', 'primary' ) ) {
            $characteristic_id = $this->get_collection_characteristic_id( $collection, 'PRIMARY_KEY', 'primary' );
            $results = $this->_get_results( 
                'value',
                $this->wpdb->prefix."ajaxmvc_collection_characteristic_value",
                "WHERE characteristic_id = '%d'",
                array( ( integer ) $characteristic_id ) );
            return $results[0]['value'];
        } else {
            return false;
        }
    }
    
    public function set_collection_characteristic_values( $collection, $characteristic, $characteristic_name, $characteristics_values ) {
        if ( $this->is_collection_characteristic( $collection, $characteristic, $characteristic_name ) ) {
            $characteristic_id = $this->get_collection_characteristic_id( $collection, $characteristic, $characteristic_name );
        } else {
            $characteristic_id = $this->set_collection_characteristic_name( $collection, $characteristic, $characteristic_name );
        }
        if ( $this->collection_characteristic_has_values( $characteristic_id ) ) {
            $this->destroy_collection_characteristics_values( $characteristic_id );
        }
        foreach( $characteristics_values as $characteristics_value ) {
            $response =  $this->_insert(
                $this->wpdb->prefix.'ajaxmvc_collection_characteristic_value',
                array( 'characteristic_id'  => ( integer ) $characteristic_id,
                       'value'              => ( string ) $characteristics_value ),
                array( 'characteristic_id'  => '%d',
                       'value'              => '%s' ) );
        }
        return $this->wpdb->insert_id;
    }
    
    public function destroy_collection_characteristics_values( $characteristic_id ) {
        $this->_delete(
            $this->wpdb->prefix.'ajaxmvc_collection_characteristic_value',
            array( 'characteristic_id' => ( integer ) $characteristic_id ),
            array( 'characteristic_id' => '%d' ) );
    }
    
    public function collection_characteristic_has_values( $characteristic_id ) {
        $collection_characteristic_has_values = $this->_get_var(
            'characteristic_id',
            $this->wpdb->prefix.'ajaxmvc_collection_characteristic_value',
            "WHERE characteristic_id = '%d'",
            array( ( integer ) $characteristic_id ) );
        return (  0 < $collection_characteristic_has_values ) ? true : false;
    }
    
    public function set_collection_characteristic_name( $collection, $characteristic, $characteristic_name ) {
        if ( $this->is_collection_characteristic( $collection, $characteristic, $characteristic_name ) ) return false;
        $response = false;
        if ( $this->is_collection( $collection ) && ! $this->is_collection_characteristic( $collection, $characteristic, $characteristic_name ) ) {
            $response =  $this->_insert(
                $this->wpdb->prefix.'ajaxmvc_collection_characteristic',
                array( 'collection_id'         => ( integer ) $this->get_collection_id( $collection ),
                       'characteristic'        => ( string ) strtoupper( $characteristic ),
                       'characteristic_name'   => ( string ) strtoupper( $characteristic_name ),
                ),
                array( 'collection_id'         => '%d',
                       'characteristic'        => '%s',
                       'characteristic_name'   => '%s',
                ) );
        }
        return $this->wpdb->insert_id;
    }
    
    public function get_collection_characteristic_id( $collection, $characteristic, $characteristic_name ) {
        if ( $this->is_collection( $collection ) ) {
            $collection_id = $this->get_collection_id( $collection );
        }
        $characteristic_id = $this->_get_var(
            'characteristic_id',
            $this->wpdb->prefix.'ajaxmvc_collection_characteristic',
            "WHERE collection_id = '%d' AND characteristic = '%s' AND characteristic_name = '%s' ",
            array( ( integer ) $collection_id, ( string ) $characteristic, ( string ) $characteristic_name ) );
        return (  0 < $characteristic_id ) ? $characteristic_id : false;
    }
    
    public function get_collection_characteristic_name( $collection, $characteristic ) {
        if ( $this->is_collection( $collection ) ) {
            $collection_id = $this->get_collection_id( $collection );
        }
        $name = $this->_get_var(
            'characteristic_name',
            $this->wpdb->prefix.'ajaxmvc_collection_characteristic',
            "WHERE collection_id = '%d' AND characteristic = '%s'",
            array( ( integer ) $collection_id, ( string ) $characteristic ) );
        return ( '' != $name ) ? $name : false;
    }
    
    /**
     * physical attribute type methods
     */
     
    public function set_all_collection_physical_attribute_type_properties( ) {
        if ( isset( $this->physical_type ) && is_array( $this->physical_type ) ) {
            foreach( $this->physical_type as $attribute => $physical_db_type ) {
                if( $this->is_collection_attribute( $this->name, $attribute ) ) {
                    $this->set_collection_attribute_physical_db_type( $this->name, $attribute, $physical_db_type );
                }   
            }
        }
    }
    
    public function set_collection_physical_attribute_type_property( $collection, $attribute ) {
        if ( $this->is_collection( $collection ) ) {
            $collection_name = $this->get_collection_name( $collection );;
        }
        if ( isset( $this->physical_type ) && is_array( $this->physical_type ) && array_key_exists( $attribute, $this->physical_type ) ) {
            if( $this->is_collection_attribute( $collection, $attribute ) ) {
                $this->set_collection_attribute_physical_db_type( $collection_name, $attribute, $this->physical_type[$attribute] );
            }
        }
    }
    
    /**
     * fillable methods for mass assignment vulnerability
     * by default all fields are NOT fillable unless 
     * explicitly specified in a models $fillable property
     */
    
    public function set_all_fillable_attributes() {
        if ( $this->is_collection( $this->name ) ) {
            $collection_id = $this->get_collection_id( $this->name  );
        }
        $attributes = $this->get_logical_collection_attribute_set( $collection_id );
        if ( ! is_array( $attributes ) ) return false;
        foreach( $attributes as $attribute_array ) {
            if ( false === array_search( $attribute_array['attribute'], $this->fillable ) ) {
                $fillable = 0;
            } else {
                $fillable = 1;
            }
            $response = $this->_update(
                $this->wpdb->prefix.'ajaxmvc_collection_attribute',
                array( 'fillable'       => ( integer ) $fillable ),
                array( 'collection_id'  => ( integer ) $collection_id,
                       'attribute_id'   => ( integer ) $attribute_array['attribute_id']
                 ),
                array( 'fillable'       => '%d' ) ,
                array( 'collection_id'  => '%d', 
                       'attribute_id'   => '%d' ) );
        }
    }
    
    public function set_fillable_attribute( $collection, $attribute ) {
        if ( $this->is_collection( $collection ) ) {
            $collection_id = $this->get_collection_id( $collection );
        }
        if( $this->is_collection_attribute( $collection, $attribute ) ) {
            $attribute_id = $this->get_collection_attribute_id( $collection, $attribute );
        }
        //necessary for sql operations on tables that are not this model
        if ( $this->name == $collection ) {
            $this_fillable = $this->fillable;
        } else {
            $model = preg_replace( '/^'.$this->wpdb->prefix.'ajaxmvc_/', '', $collection );
            $this_fillable = ( new $model() )->fillable;
        }
        if ( false === array_search( $attribute, $this_fillable ) ) {
            $fillable = 0;
        } else {
            $fillable = 1;
        }
        $this->_update(
            $this->wpdb->prefix.'ajaxmvc_collection_attribute',
            array( 'fillable'       => ( integer ) $fillable ),
            array( 'collection_id'  => ( integer ) $collection_id,
                   'attribute_id'   => ( integer ) $attribute_id
            ),
            array( 'fillable'       => '%d' ) ,
            array( 'collection_id'  => '%d',
                   'attribute_id'   => '%d' ) );
    }
    
    public function collection_attribute_is_fillable( $collection, $attribute ) {
        if ( $this->is_collection( $collection ) ) {
            $collection_id = $this->get_collection_id( $collection );
        }
        if ( $this->is_integer( $attribute ) ) {
            $attribute_is_fillable = $this->_get_var(
                    'fillable',
                    $this->wpdb->prefix.'ajaxmvc_collection_attribute',
                    "WHERE collection_id = '%d' AND attribute_id = '%d'",
                    array( ( integer ) $collection_id, ( integer ) $attribute ) );
        } else {
            $attribute_is_fillable = $this->_get_var(
                    'fillable',
                    $this->wpdb->prefix.'ajaxmvc_collection_attribute',
                    "WHERE collection_id = '%d' AND attribute = '%s'",
                    array( ( integer ) $collection_id, ( string ) $attribute ) );
        }
        return ( $attribute_is_fillable > 0 ) ? true : false;
    }
    
    /**
     * Helper methods of Model ORM methods
     */
    
    public function create_physical_collection_from_logical( $logical_collection ) {
        if ( $this->is_collection( $logical_collection ) && 'logical' == $this->get_collection_state( $logical_collection ) ) {
            $collection_name = $this->get_collection_name( $logical_collection );
            $collection = $this->get_logical_collection( $logical_collection );
            /*
             * any ddl statement in mysql implicity commits a transaction,
             * so we must create this outside of our transaction, worst case scenario
             * we pass a rollback callback into the transaction function
             *  and execute the drop table there eliminating any footprint
             */
            $this->create_physical_collection( "CREATE TABLE {$collection_name} AS SELECT * FROM {$collection}" );
            $this->execute_collection_characteristics( $logical_collection );
            $key = $this->get_collection_characteristic_primary_key( $collection_name );
            $this->query( "ALTER TABLE {$collection_name} ADD PRIMARY KEY($key)" );
            $this->modify_attribute_type_if_physical_type( $logical_collection );
            return $this->transaction( 
                function() use( $logical_collection ) {
                    $this->set_collection_state( $logical_collection, 'physical' );
                    if ( $logical_collection == $this->name ) {
                        $this->state = 'physical';
                    }
                    $this->destroy_logical_collection_entities( $this->get_collection_id( $logical_collection ) );
                }, 
                function() use( $collection_name ) {
                    $sql = "DROP TABLE IF EXISTS {$collection_name}";
                    $this->query( $sql );
            }); 
        }
    }
    
    public function create_logical_collection_from_physical( $physical_collection ) {
        if ( $this->is_collection( $physical_collection ) && 'physical' == $this->get_collection_state( $physical_collection ) ) {
            return $this->transaction( 
                function() use( $physical_collection ) {
                    $collection_id = $this->get_collection_id( $physical_collection );
                    $attributes = $this->get_reconciled_collection_attributes( $collection_id );
                    $this->destroy_logical_collection_attributes( $collection_id );
                    foreach( $attributes as $index => $attribute_array ) {
                        $attribute_set = $this->_get_results( "{$this->primary_key},{$attribute_array['attribute']}", $physical_collection );
                        if ( ! empty( $attribute_set ) ) {
                            foreach( $attribute_set as $key => $array ) {
                                settype( $array[$attribute_array['attribute']], $attribute_array['script_db_type'] );
                                $this->set_collection_entity_attribute(
                                    $physical_collection,
                                    $array[$this->primary_key],
                                    $attribute_array['attribute'],
                                    $array[$attribute_array['attribute']],
                                    $attribute_array['physical_db_type'] );
                            }
                        } else {
                            $force_script_db_type = $this->get_php_type_from_mysql( $physical_collection, $index );
                            $this->set_collection_attribute( $physical_collection, $attribute_array['attribute'], '', $index, $attribute_array['physical_db_type'], $force_script_db_type );
                        }
                    }
                    $this->set_collection_state( $physical_collection, 'logical' );
                    if ( $physical_collection == $this->name ) {
                        $this->state = 'logical';
                    }
                    /*
                     * any ddl statement in mysql implicity commits a transaction,
                     * so we save this statement for last, in this case we know that if
                     * this method is executed we can assume the preceding statements did not throw 
                     * an exception, if they did they would be caught and rolled back, this 
                     * is really an explicit commit in a sense
                     */
                    $this->destroy_physical_collection( $physical_collection );
            });
        }
    }
    
    public function get_physical_collection_attribute_type( $collection, $attribute ) {
        $collection_name = $this->get_collection_name( $collection );
        $result = $this->_get_results(
            'column_name,column_type',
            'information_schema.columns',
            "WHERE table_schema = '%s' AND table_name = '%s' AND column_name = '%s'",
            array( ( string ) $this->wpdb->dbname, ( string ) $collection_name, ( string ) $attribute ) );
        return ( empty( $result ) ) ? false : $result[0]['column_type'];
    }
    
    public function get_physical_collection_attribute_set( $collection ) {
        $collection_name = $this->get_collection_name( $collection );
        $attribute_array = array();
        $attributes = $this->_get_results(
            'DISTINCT column_name AS attribute',
            'information_schema.columns',
            "WHERE table_schema = '%s' AND table_name = '%s' ".
            "ORDER BY ordinal_position",
            array( ( string ) $this->wpdb->dbname, ( string ) $collection_name ) );
        foreach( $attributes as $key => $value ) {
            $attributes[$key]['physical_db_type'] = $this->get_physical_collection_attribute_type( $collection, $value['attribute'] );
        }
        return $attributes;
    }
    
    public function execute_collection_characteristics( $collection ) {
        $collection_id = $this->get_collection_id( $collection );
        $collection_characteristics = $this->_get_results( 
            'DISTINCT characteristic_id, characteristic, characteristic_name',
            $this->wpdb->prefix."ajaxmvc_collection_characteristic",
            "WHERE collection_id = '%d' ORDER BY characteristic_id",
            array( ( integer ) $collection_id ) );
        foreach( $collection_characteristics as $index => $characteristics_array ) {
            $values = $this->get_collection_characteristic_values_by_name( $collection, $characteristics_array['characteristic'], $characteristics_array['characteristic_name'] );
        }
    }
    
    public function get_reconciled_collection_attributes( $collection ) {
        $collection_name = $this->get_collection_name( $collection );
        $logical_attribute_set = $this->get_logical_collection_attribute_set( $collection );
        //we need specific type to preserve the physical types for later conversion back to physical
        $physical_attribute_set = $this->get_physical_collection_attribute_set( $collection );
        foreach( $physical_attribute_set as $p_attr_index => $p_attr_array ) {
            if ( $this->is_collection_attribute( $collection, $p_attr_array['attribute'] ) ) {
                foreach( $logical_attribute_set as $l_attr_index => $l_attr_array ) {
                    if( false !== array_search( $p_attr_array['attribute'], $l_attr_array ) ) {
                        unset($l_attr_array['physical_db_type']);
                        foreach( $l_attr_array as $l_attr_array_key => $l_attr_array_value ) {
                            $physical_attribute_set[$p_attr_index][$l_attr_array_key] = $l_attr_array_value;
                        }
                    }
                }
            } else {
                //we want the simplest approach to infer the php type
                $physical_attribute_set[$p_attr_index]['script_db_type'] = $this->get_php_type_from_mysql( $collection_name, $p_attr_index );
            }
        }
        //unset because we want to preserve the ordinal position
        if ( false !== array_search( $this->primary_key, $physical_attribute_set[0] ) ) unset( $physical_attribute_set[0] );
        return $physical_attribute_set;
    }
    
    public function modify_attribute_type_if_physical_type( $collection ) {
        $collection_name = $this->get_collection_name( $collection );
        if ( ! $this->is_collection( $collection ) ) return false;
        $collection_id = $this->get_collection_id( $collection );
        $logical_attribute_set = $this->get_logical_collection_attribute_set( $collection_id );
        foreach( $logical_attribute_set as $l_attr_index => $l_attr_array ) {
            if ( isset( $l_attr_array['physical_db_type'] ) && '' != $l_attr_array['physical_db_type'] ) {
                $this->query( "ALTER TABLE {$collection_name} MODIFY {$l_attr_array['attribute']} {$l_attr_array['physical_db_type']}" );
            }
        }
    }
    
    public function destroy_logical_collection_attributes( $collection ) {
        if ( ! $this->is_collection( $collection ) ) return false;
        $collection_id = $this->get_collection_id( $collection );
        $this->_delete(
            $this->wpdb->prefix.'ajaxmvc_collection_attribute',
            array( 'collection_id' => ( integer ) $collection_id ),
            array( 'collection_id' => '%d' ) );
    }
    
    public function destroy_logical_collection_entities( $collection ) {
        if ( ! $this->is_collection( $collection ) ) return false;
        $collection_id = $this->get_collection_id( $collection );
        $this->_delete( 
            $this->wpdb->prefix.'ajaxmvc_collection_entity', 
            array( 'collection_id' => ( integer ) $collection_id ), 
            array( 'collection_id' => '%d' ) );
    }
    
    public function destroy_logical_entity( $collection, $collection_entity_id ) {
        if ( ! $this->is_collection( $collection ) ) return false;
        $collection_id = $this->get_collection_id( $collection );
        $this->_delete(
            $this->wpdb->prefix.'ajaxmvc_collection_entity',
            array( 'collection_id'          => ( integer ) $collection_id, 
                   'collection_entity_id'   => ( integer ) $collection_entity_id ),
            array( 'collection_id'          => '%d', 
                   'collection_entity_id'   => '%d' ) );
    }
    
    public function destroy_physical_collection( $collection ) {
        if ( ! $this->is_collection( $collection ) ) return false;
        $collection_name = $this->get_collection_name( $collection );
        $this->query( "DROP TABLE IF EXISTS {$collection_name}" );
    }
    
    public function get_logical_collection( $collection ) {
        if ( ! $this->is_collection( $collection ) ) return false;
        $collection_id = $this->get_collection_id( $collection );
        $prfx = $this->wpdb->prefix;
        $collection_name = $this->get_collection_name( $collection_id );
        $collection_name = $this->sanitize_single_sql_input( $collection_name );
        $key = $this->get_collection_characteristic_primary_key( $collection_id );
        $logical_collection = $this->wpdb->prepare(
            "/*qc=on*/".PHP_EOL.
            "SELECT ".PHP_EOL.
            "CAST({$prfx}ajaxmvc_collection_entity.collection_entity_id AS SIGNED) AS $key, ".PHP_EOL.
            $this->get_logical_collection_attribute_set_statements( $collection_id ).PHP_EOL.
            "FROM       {$prfx}ajaxmvc_collection_entity ".PHP_EOL.
            "INNER JOIN {$prfx}ajaxmvc_collection_entity_attribute ".PHP_EOL.
            "ON         {$prfx}ajaxmvc_collection_entity.entity_id = {$prfx}ajaxmvc_collection_entity_attribute.entity_id ".PHP_EOL.
            "WHERE      {$prfx}ajaxmvc_collection_entity.collection_id = %s ".PHP_EOL.
            "GROUP BY   {$prfx}ajaxmvc_collection_entity.entity_id".PHP_EOL,
            array( $collection_id ) );
        return "($logical_collection) AS {$collection_name}";
    }
    
    public function get_logical_collection_attribute_set_statements( $collection_id ) {
        if ( ! $this->is_collection( $collection_id ) ) return false;
        $prfx = $this->wpdb->prefix;
        $attributes =  $this->get_logical_collection_attribute_set( $collection_id );
        $statement = '';
        foreach( $attributes as $attribute_array ) {
            foreach ( array( 'attribute_id', 'logical_db_type', 'attribute' ) as $key ) {
                $attribute_array[$key] = $this->sanitize_single_sql_input( $attribute_array[$key] );
            }
            $statement .= 
                "MAX(CASE WHEN {$prfx}ajaxmvc_collection_entity_attribute.attribute_id = {$attribute_array['attribute_id']} ".PHP_EOL.
                "THEN CAST({$prfx}ajaxmvc_collection_entity_attribute.value AS {$attribute_array['logical_db_type']}) ELSE NULL END) AS {$attribute_array['attribute']}, ".PHP_EOL;
        }
        return preg_replace( '/, $/', ' ', $statement );
    }
    
    public function get_logical_collection_attribute_set( $collection_id ) {
        if ( ! $this->is_collection( $collection_id ) ) return false;
        $prfx = $this->wpdb->prefix;
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * ".
                "FROM   {$prfx}ajaxmvc_collection_attribute ".
                "WHERE  {$prfx}ajaxmvc_collection_attribute.collection_id = %d ".
                "ORDER BY ordinal_position",
                array( $collection_id ) ), ARRAY_A );
    }
        
    public function get_collection_id( $collection ) {
        if ( $this->is_integer( $collection ) ) {
            return $this->_get_var(
                'collection_id',
                $this->wpdb->prefix.'ajaxmvc_collection',
                "WHERE collection_id = '%d'",
                array( ( integer ) $collection ) );
        } else {
            return $this->_get_var(
                'collection_id',
                $this->wpdb->prefix.'ajaxmvc_collection',
                "WHERE collection = '%s'",
                array( ( string ) $collection ) );
        }
    }
    
    public function get_collection_name( $collection ) {
        if ( $this->is_integer( $collection ) ) {
            return $this->_get_var(
                'collection',
                $this->wpdb->prefix.'ajaxmvc_collection',
                "WHERE collection_id = '%d'",
                array( ( integer ) $collection ) );
        } else {
            return $this->_get_var(
                'collection',
                $this->wpdb->prefix.'ajaxmvc_collection',
                "WHERE collection = '%s'",
                array( ( string ) $collection ) );
        }
    }
    
    public function get_collection_state( $collection ) {
        if ( $this->is_integer( $collection ) ) {
            return $this->_get_var(
                'state',
                $this->wpdb->prefix.'ajaxmvc_collection',
                "WHERE collection_id = '%d'",
                array( ( integer ) $collection ) );
        } else {
            return $this->_get_var(
                'state',
                $this->wpdb->prefix.'ajaxmvc_collection',
                "WHERE collection = '%s'",
                array( ( string ) $collection ) );
        }
    }
    
    public function set_collection_state( $collection, $state ) {
        $collection_id = $this->get_collection_id( $collection );
        return $this->_update(
            $this->wpdb->prefix.'ajaxmvc_collection',
            array( 'state'          => ( string ) $state ),
            array( 'collection_id'  => ( integer ) $collection_id),
            array( 'state'          => '%s' ),
            array( 'collection_id'  => '%d') );
    }
    
    public function set_collection( $collection ) {
        $response =  $this->_insert(
            $this->wpdb->prefix.'ajaxmvc_collection',
            array( 'collection' => ( string ) $collection ),
            array( 'collection' => '%s' ) );
        return $response;
    }
    
    public function is_collection( $collection ) {
        return ( $this->get_collection_id( $collection ) ) ? true : false;
    }

    public function is_collection_entity( $collection, $collection_entity_id ) {
        $collection_id = $this->get_collection_id( $collection );
        $is_collection_entity = $this->_get_var(
                'collection_entity_id',
                $this->wpdb->prefix.'ajaxmvc_collection_entity',
                "WHERE collection_id = '%d' AND collection_entity_id = '%d'",
                array( ( integer ) $collection_id, ( integer ) $collection_entity_id, ) );
        return ( $is_collection_entity ) ? true : false;
    }
    
    public function set_collection_entity( $collection, $collection_entity_id ) {
        if ( $this->is_collection_entity( $collection, $collection_entity_id ) ) return false;
        $response = false;
        if ( $this->is_collection( $collection ) && ! $this->is_collection_entity( $collection, $collection_entity_id ) ) {
            $response =  $this->_insert(
                $this->wpdb->prefix.'ajaxmvc_collection_entity',
                array( 'collection_id'          => ( integer ) $this->get_collection_id( $collection ),
                       'collection_entity_id'   => ( integer ) $collection_entity_id
                ),
                array( 'collection_id'          => '%d',
                       'collection_entity_id'   => '%d'
                ) );
            $response = ( false === $response ) ? false : true;
        } 
        return $response;
    }

    public function is_collection_attribute( $collection, $attribute ) {
        return ( 0 < $this->get_collection_attribute_id( $collection, $attribute ) ) ? true : false;
    }
    
    public function get_collection_attribute_id( $collection, $attribute ) {
        if ( $this->is_collection( $collection ) ) {
            $collection_id = $this->get_collection_id( $collection );
        }
        if ( $this->is_integer( $attribute ) ) {
            $attribute_id = $this->_get_var(
                'attribute_id',
                $this->wpdb->prefix.'ajaxmvc_collection_attribute',
                "WHERE collection_id = '%d' AND attribute_id = '%d'",
                array( ( integer ) $collection_id, ( integer ) $attribute ) );
        } else {
            $attribute_id = $this->_get_var(
                'attribute_id',
                $this->wpdb->prefix.'ajaxmvc_collection_attribute',
                "WHERE collection_id = '%d' AND attribute = '%s'",
                array( ( integer ) $collection_id, ( string ) $attribute ) );
        }
        return $attribute_id;
    }
    
    public function verify_collection_attribute_type( $collection, $attribute, $value ) {
        $attribute_type = $this->get_collection_attribute_script_db_type( $collection, $attribute );
        $attribute_value_type = gettype( $value );
        if ( $attribute_type != $attribute_value_type ) {
            $message = "the value you are attempting to save is of type: <strong>{$attribute_value_type}</strong> and does not match data type of: <strong>{$attribute_type}</strong> for attribute: <strong>{$collection}.{$attribute}</strong>.";
            ajaxmvc_core_exception::throw_error( $this,  $message );
        }
        return true;
    }
    
    public function get_collection_attribute_script_db_type( $collection, $attribute ) {
        if ( $this->is_collection( $collection ) ) {
            $collection_id = $this->get_collection_id( $collection );
        }
        if ( $this->is_integer( $attribute ) ) {
            $attribute_type = $this->_get_var(
                    'script_db_type',
                    $this->wpdb->prefix.'ajaxmvc_collection_attribute',
                    "WHERE collection_id = '%d' AND attribute_id = '%d'",
                    array( ( integer ) $collection_id, ( integer ) $attribute ) );
        } else {
            $attribute_type = $this->_get_var(
                    'script_db_type',
                    $this->wpdb->prefix.'ajaxmvc_collection_attribute',
                    "WHERE collection_id = '%d' AND attribute = '%s'",
                    array( ( integer ) $collection_id, ( string ) $attribute ) );
        }
        return $attribute_type;
    }
    
    public function get_collection_attribute_name( $collection, $attribute ) {
        if ( $this->is_collection( $collection ) ) {
            $collection_id = $this->get_collection_id( $collection );
        }
        if ( $this->is_integer( $attribute ) ) {
            $attribute_name = $this->_get_var(
                'attribute',
                $this->wpdb->prefix.'ajaxmvc_collection_attribute',
                "WHERE collection_id = '%d' AND attribute_id = '%d'",
                array( ( integer ) $collection_id, ( integer ) $attribute ) );
        } else {
            $attribute_name = $this->_get_var(
                'attribute',
                $this->wpdb->prefix.'ajaxmvc_collection_attribute',
                "WHERE collection_id = '%d' AND attribute = '%s'",
                array( ( integer ) $collection_id, ( string ) $attribute ) );
        }
        return $attribute_name;
    }
    
    public function set_collection_attribute( $collection, $attribute, $value, $ordinal_position, $physical_db_type = null, $force_script_db_type = null ) {
        if ( $this->is_integer( $attribute ) ) {
            ajaxmvc_core_exception::throw_error( $this,  'you may not store numeric values as attributes.');
        }
        /**
         * @todo this is a temporary fix to the boolean to integer conversion
         */
        if ( 'boolean' == gettype( $value ) ) $value = ( integer ) ( true === $value ) ? 1 : 0;
        $ordinal_position = ( 0 < $ordinal_position ) ? $ordinal_position : 0;
        $script_db_type = ( null != $force_script_db_type ) ? $force_script_db_type : gettype( $value );
        $response = false;
        if ( $this->is_collection( $collection ) && ! $this->is_collection_attribute( $collection, $attribute ) ) {
            $response =  $this->_insert(
                $this->wpdb->prefix.'ajaxmvc_collection_attribute',
                array( 'collection_id'      => ( integer ) $this->get_collection_id( $collection ),
                       'attribute'          => ( string ) $attribute,
                       'script_db_type'     => ( string ) $script_db_type,
                       'logical_db_type'    => ( string ) $this->get_logical_collection_storage_type( $script_db_type ),
                       'physical_db_type'   => ( string ) ( null == $physical_db_type ) ? '' : $physical_db_type,
                       'ordinal_position'   => ( integer ) $ordinal_position,
                ),
                array( 'collection_id'      => '%d',
                       'attribute'          => '%s',
                       'script_db_type'     => '%s',
                       'logical_db_type'    => '%s',
                       'physical_db_type'   => '%s',
                       'ordinal_position'   => '%d',
                ) );
            $this->set_fillable_attribute( $collection, $attribute );
            $this->set_collection_physical_attribute_type_property( $collection, $attribute );
            $response = ( 1 === $response ) ? $this->wpdb->insert_id  : false;
        } elseif ( $this->is_collection_attribute( $collection, $attribute ) ) {
            $response = $this->_update(
                $this->wpdb->prefix.'ajaxmvc_collection_attribute',
                array( 'script_db_type'     => ( string ) $script_db_type,
                       'logical_db_type'    => ( string ) $this->get_logical_collection_storage_type( $script_db_type ),
                       'physical_db_type'   => ( string ) ( null == $physical_db_type ) ? '' : $physical_db_type,
                       'ordinal_position'   => ( integer ) $ordinal_position,
                ),
                array( 'attribute_id'       => ( integer ) $this->get_collection_attribute_id( $collection, $attribute ) ),
                array( 'script_db_type'     => '%s',
                       'logical_db_type'    => '%s',
                       'physical_db_type'   => '%s',
                       'ordinal_position'   => '%d',
                ) ,
                array( 'attribute_id'       => '%d' ) );
            $this->set_fillable_attribute( $collection, $attribute );
            $this->set_collection_physical_attribute_type_property( $collection, $attribute );
        }
        return $response;
    }
    
    public function set_collection_attribute_physical_db_type( $collection, $attribute, $physical_db_type ) {
        if ( ! $this->is_collection_attribute( $collection, $attribute ) ) return false;
        $response = false;
        $response = $this->_update(
                $this->wpdb->prefix.'ajaxmvc_collection_attribute',
                array( 'physical_db_type'   => ( string ) $physical_db_type ),
                array( 'attribute_id'       => ( integer ) $this->get_collection_attribute_id( $collection, $attribute ) ),
                array( 'physical_db_type'   => '%s' ) ,
                array( 'attribute_id'       => '%d' ) );
        return $response;
    }
    
    public function get_next_collection_attribute_ordinal_position( $collection ) {
        $collection_id = $this->get_collection_id( $collection );
        return $this->get_last_collection_attribute_ordinal_position( $collection_id ) + 1 ;
    }
    
    public function get_collection_attribute_ordinal_position( $collection, $attribute ) {
        $collection_id = $this->get_collection_id( $collection );
        $collection_attribute_ordinal_position = $this->_get_var(
                'ordinal_position',
                $this->wpdb->prefix.'ajaxmvc_collection_attribute',
                "WHERE attribute_id = '%d'",
                array( ( integer ) $this->get_collection_attribute_id( $collection, $attribute ) ) );
        return ( integer ) $collection_attribute_ordinal_position;
    }
    
    public function get_last_collection_attribute_ordinal_position( $collection ) {
        $collection_id = $this->get_collection_id( $collection );
        $last_collection_attribute_ordinal_position = $this->_get_var(
                'MAX(ordinal_position)',
                $this->wpdb->prefix.'ajaxmvc_collection_attribute',
                "WHERE collection_id = '%d'",
                array( ( integer ) $collection_id ) );
        return ( integer ) $last_collection_attribute_ordinal_position;
    }
    
    public function get_entity_id( $collection, $collection_entity_id ) {
        if ( ! $this->is_collection( $collection ) ) return false;
        $collection_id = $this->get_collection_id( $collection );
            return ( integer ) $this->_get_var(
                'entity_id',
                $this->wpdb->prefix.'ajaxmvc_collection_entity',
                "WHERE collection_id = '%d' AND collection_entity_id = '%d'",
                array( ( integer ) $collection_id, ( integer ) $collection_entity_id ) );
    }
    
    public function collection_entity_id_exists( $collection, $collection_entity_id ) {
        $entity_id = $this->get_entity_id( $collection, $collection_entity_id );
        if ( 0 < $entity_id ) {
            return true;
        }
        return false;
    }
    
    public function get_next_collection_entity_id( $collection ) {
        $collection_id = $this->get_collection_id( $collection );
        return $this->get_last_collection_entity_id( $collection_id ) + 1 ;
    }
    
    public function get_last_collection_entity_id( $collection ) {
        $collection_id = $this->get_collection_id( $collection );
        $last_collection_entity_id = $this->_get_var(
            'MAX(collection_entity_id)',
            $this->wpdb->prefix.'ajaxmvc_collection_entity',
            "WHERE collection_id = '%d'",
            array( ( integer ) $collection_id ) );
        return ( integer ) $last_collection_entity_id;
    }
    
    public function is_collection_entity_attribute( $collection, $collection_entity_id, $attribute ) {
        if ( ! $this->is_collection_entity( $collection, $collection_entity_id ) )  return false;
        $entity_id = $this->get_entity_id( $collection, $collection_entity_id );
        $attribute_id = $this->get_collection_attribute_id( $collection, $attribute );
        $is_entity_attribute = $this->_get_var(
            'entity_id',
            $this->wpdb->prefix.'ajaxmvc_collection_entity_attribute',
            "WHERE entity_id = '%d' AND attribute_id = '%d'",
            array( ( integer ) $entity_id, ( integer ) $attribute_id ) );
        return ( $is_entity_attribute ) ? true : false;
    }
    
    public function get_collection_entity_attribute( $collection, $collection_entity_id, $attribute = null ) {
        if ( ! $this->is_collection( $collection ) ) return false;
        if ( ! $this->is_collection_entity( $collection, $collection_entity_id ) )  return false;
        if ( null != $collection_entity_id && null != $attribute ) {
            if ( ! $this->is_collection_entity_attribute( $collection, $collection_entity_id, $attribute ) )  return false;
            $entity_id = $this->get_entity_id( $collection, $collection_entity_id );
            $attribute_id = $this->get_collection_attribute_id( $collection, $attribute );
            return $this->_get_var(
                'value',
                $this->wpdb->prefix.'ajaxmvc_collection_entity_attribute',
                "WHERE entity_id = '%d' AND attribute_id = '%d'",
                array( ( integer ) $entity_id, ( integer ) $attribute_id ) );
        } elseif ( null != $collection_entity_id && null == $attribute ) {
            $entity_id = $this->get_entity_id( $collection, $collection_entity_id );
            return $this->_get_results(
                'attribute,value',
                $this->wpdb->prefix.'ajaxmvc_collection_entity_attribute',
                "WHERE entity_id = '%d' ",
                array( ( integer ) $entity_id ) );
        }
    }
    
    public function is_core_entity_activated( $entity ) {
        $prfx = $this->wpdb->prefix;
        $statement = $this->wpdb->prepare(
            "SELECT {$prfx}ajaxmvc_core.ajaxmvc_activated FROM ".
            $this->get_logical_collection($prfx.'ajaxmvc_core')." ".
            "WHERE ajaxmvc_entity = %s",
            array( $entity ) );
        $result = $this->wpdb->get_results( $statement, ARRAY_A );
        if ( empty( $result ) || empty( $result[0] ) ) {
            return false;
        } else {
            return  ( 0 < $result[0]['ajaxmvc_activated'] ) ? true : false;
        }
    }
    
    public function get_collection_entity_id_by_attribute( $collection, $attribute, $attribute_value ) {
        $prfx = $this->wpdb->prefix;
        $statement = $this->wpdb->prepare(
                "SELECT {$prfx}ajaxmvc_core.id FROM ".
                $this->get_logical_collection( $collection )." ".
                "WHERE $attribute = %s",
                array( $attribute_value ) );
        $result = $this->wpdb->get_results( $statement, ARRAY_A );
        if ( empty( $result ) || empty( $result[0] ) ) {
            return false;
        } else {
            return  ( 0 < $result[0]['id'] ) ? $result[0]['id'] : false;
        }
    }
    
    public function set_collection_entity_attribute( $collection, $collection_entity_id, $attribute, $value, $physical_db_type = null ) {
        if ( $this->primary_key == $attribute ) ajaxmvc_core_exception::throw_error( $this, "you may not save an attribute of name $this->primary_key");
        $this->set_collection_entity( $collection, $collection_entity_id );
        /**
         * @todo this is a temporary fix to the boolean to integer conversion
         */
        if ( 'boolean' == gettype( $value ) ) $value = ( integer ) ( true === $value ) ? 1 : 0;
        if ( $this->is_collection_attribute( $collection, $attribute ) ) {
            $this->verify_attribute_is_fillable( $collection, $attribute );
            $this->verify_collection_attribute_type( $collection, $attribute, $value );
            $ordinal_position = $this->get_collection_attribute_ordinal_position( $collection, $attribute );
            //update the attributes of the attribute
            $this->set_collection_attribute( $collection, $attribute, $value, $ordinal_position, $physical_db_type );
            $attribute_id = $this->get_collection_attribute_id( $collection, $attribute );
        } else {
            $ordinal_position = $this->get_next_collection_attribute_ordinal_position( $collection );
            $attribute_id = $this->set_collection_attribute( $collection, $attribute, $value, $ordinal_position, $physical_db_type );
            try {
                $this->verify_attribute_is_fillable( $collection, $attribute );
            } catch (Exception $e) {
                //roll back the attribute save, change the message, and rethrow the exception
                $this->_delete( 
                        $this->wpdb->prefix.'ajaxmvc_collection_attribute', 
                        array( 'attribute_id' => ( integer ) $attribute_id ), 
                        array( 'attribute_id' => '%d' ) );
                $message = "you are in a logical state and you have set a new attribute of {$attribute} this attribute will not be saved because ";
                $message .= "you are attempting to fill an unfillable attribute: {$attribute}, the data will not be saved, please identify this attribute as fillable in order to proceed.";
                ajaxmvc_core_exception::throw_error( $this,  $message );
            }
        }
        $response = false;
        if ( $this->is_collection_entity( $collection, $collection_entity_id ) && ! $this->is_collection_entity_attribute( $collection, $collection_entity_id, $attribute) ) {
            $response = $this->_insert(
                $this->wpdb->prefix.'ajaxmvc_collection_entity_attribute',
                array( 'entity_id'      => ( integer ) $this->get_entity_id( $collection, $collection_entity_id ),
                       'attribute_id'   => ( integer ) $attribute_id,
                       'value'          => ( string ) $this->cast_as_logical_collection_storage_type( $value )
                ),
                array( 'entity_id'      => '%d',
                       'attribute_id'   => '%d',
                       'value'          => '%s'
                ) );
            $response = ( false === $response ) ? false : true;
        } elseif ( $this->is_collection_entity( $collection, $collection_entity_id ) && $this->is_collection_entity_attribute( $collection, $collection_entity_id, $attribute) ) {
            $response = $this->_update( 
                $this->wpdb->prefix.'ajaxmvc_collection_entity_attribute', 
                array( 'value'          => ( string ) $this->cast_as_logical_collection_storage_type( $value ) ),  
                array( 'entity_id'      => ( integer ) $this->get_entity_id( $collection, $collection_entity_id ),
                       'attribute_id'   => ( integer ) $attribute_id,
                ), 
                array( 'value'          => '%s' ), 
                array( 'entity_id'      => '%d',
                       'attribute_id'   => '%d',
                ) );
            $response = ( false === $response ) ? false : true;
        }
        return $response;
    }
    
    public function process_logical_save( $attributes, $values = null, $collection_entity_id = null ) {
        if ( ! is_array( $attributes ) && null !== $values && null !== $collection_entity_id ) {
            /**
             * @todo do we want to verify this?
             * $this->verify_is_logical_state_attribute( $this->name, $attributes, $collection_entity_id );
             */
            $collection_entity_id = $this->verify_and_cast_collection_entity_id( $collection_entity_id );
            $this->save = function() use( $collection_entity_id, $attributes, $values) {
                $this->set_collection_entity_attribute( $this->name, $collection_entity_id, $attributes, $values );
            };
        } elseif ( is_array( $attributes ) && null === $values && null === $collection_entity_id ) {
            $this->save = function() use( $attributes ) {
                if ( is_array( $attributes[0] ) ) {
                    foreach( $attributes as $attribute_array ) {
                        $this->process_multiple_attributes_logical_save( $attribute_array );
                    }
                } else {
                    $this->process_multiple_attributes_logical_save( $attributes );
                }
            };
        }
    }
    
    public function process_multiple_attributes_logical_save( array $attributes ) {
        $collection_entity_id = $this->verify_and_cast_collection_entity_id( $attributes );
        /**
         * @todo do we want to verify this?
         * $this->verify_is_logical_state_attribute( $this->name, $attributes, $collection_entity_id );
         */
        foreach( $attributes as $key => $val ) {
            $this->set_collection_entity_attribute($this->name, $collection_entity_id, $key, $val );
        }
    }
    
    public function process_physical_save( $attributes, $values = null, $collection_entity_id = null ) {
        if ( ! is_array( $attributes ) && null !== $values && null !== $collection_entity_id ) {
            $this->verify_is_physical_state_attribute( $this->name, $attributes );
            $this->verify_attribute_is_fillable( $this->name, $attributes );
            $collection_entity_id = $this->verify_and_cast_collection_entity_id( $collection_entity_id );
            $this->save = function() use( $collection_entity_id, $attributes, $values) {
                if ( $this->is_physical_collection_entity_id( $collection_entity_id ) ) {
                    $this->_update(
                        $this->name,
                        array( $attributes          => $values ),
                        array( $this->primary_key   => ( integer ) $collection_entity_id ),
                        array( $attributes          => $this->get_statement_prepare_type( gettype( $values ) ) ) ,
                        array( $this->primary_key   => '%d' ) );
                } else {
                    $this->_insert(
                        $this->name,
                        array( $this->primary_key   => ( integer ) $collection_entity_id,
                               $attributes          => $values,
                        ),
                        array( $this->primary_key   => '%d',
                               $attributes          => $this->get_statement_prepare_type( gettype( $values ) ) ) );
                }
            };
        } elseif ( is_array( $attributes ) && null === $values && null === $collection_entity_id ) {
            $this->save = function() use( $attributes ) {
                if ( is_array( $attributes[0] ) ) {
                    foreach( $attributes as $attribute_array ) {
                        $this->process_multiple_attributes_physical_save( $attribute_array );
                    }
                } else {
                    $this->process_multiple_attributes_physical_save( $attributes );
                }
            };
        }
    }
    
    public function verify_attribute_is_fillable( $collection, $attributes ) {
        if ( is_array( $attributes ) ) {
            foreach( $attributes as $attribute => $val ) {
                if ( ! $this->collection_attribute_is_fillable( $collection, $attribute ) ) {
                    $message = "you are attempting to fill an unfillable attribute: {$attribute}, the data will not be saved, please identify this attribute as fillable in order to proceed.";
                    ajaxmvc_core_exception::throw_error( $this,$message);
                }
            }
        } else {
            if ( ! $this->collection_attribute_is_fillable( $collection, $attributes ) ) {
                $message = "you are attempting to fill an unfillable attribute: {$attributes}, the data will not be saved, please identify this attribute as fillable in order to proceed.";
                ajaxmvc_core_exception::throw_error( $this,$message);
            }
        }
    }
    
    public function verify_is_physical_state_attribute( $collection, $attributes ) {
        if ( is_array( $attributes ) ) {
            foreach( $attributes as $attribute => $val ) {
                if( ! $this->is_collection_attribute( $collection, $attribute ) &&  1 != preg_match( '/'.$this->primary_key.'/', $attribute ) ) {
                    $message = "the database is in a physical state and the attribute of <strong>{$collection}.{$attribute} does not exist</strong> in the logical model schema, either the physical collection was altered via direct sql or there was a naming error in your code.";
                    ajaxmvc_core_exception::throw_error( $this,  $message );
                }
            }
        } else {
            if( ! $this->is_collection_attribute( $collection, $attributes ) && 1 != preg_match( '/'.$this->primary_key.'/', $attribute ) ) {
                $message = "the database is in a physical state and the attribute of <strong>{$collection}.{$attributes} does not exist</strong> in the logical model schema, either the physical collection was altered via direct sql or there was a naming error in your code.";
                ajaxmvc_core_exception::throw_error( $this,  $message );
            }
        }
    }
    
    public function verify_is_logical_state_attribute( $collection, $attributes, $collection_entity_id ) {
        if ( is_array( $attributes ) ) {
            foreach( $attributes as $attribute => $val ) {
                if( ! $this->is_collection_attribute( $collection, $attribute ) && ! $this->is_collection_entity( $collection, $collection_entity_id ) ) {
                    if ( AM_ERRORS_VERBOSE ) {
                        $message = "<strong>Notice:</strong> you have saved both a new collection attribute and a new collection entity $this->primary_key, this will result in a null value for all other entities in this new attribute named <strong>: {$attribute}</strong>";
                        echo $message;
                    }
                }
            }
        } else {
            if( ! $this->is_collection_attribute( $collection, $attributes ) && ! $this->is_collection_entity( $collection, $collection_entity_id ) ) {
                if ( AM_ERRORS_VERBOSE ) {
                    $message = "<strong>Notice:</strong> you have saved both a new collection attribute and a new collection entity $this->primary_key, this will result in a null value for all other entities in this new attribute named <strong>: {$attributes}</strong>";
                    echo $message;
                }
            }
        }
    }
    
    public function process_multiple_attributes_physical_save( array $attributes ) {
        /**
         * better to throw an explicit exception than a failed insert
         */
        $this->verify_is_physical_state_attribute( $this->name, $attributes );
        $collection_entity_id = $this->verify_and_cast_collection_entity_id( $attributes );
        $this->verify_attribute_is_fillable( $this->name, $attributes );
        $attribute_types = array();
        foreach( $attributes as $key => $value ) {
            $attribute_types[$key] = $this->get_statement_prepare_type( gettype( $value ) );
        }
        if ( $this->is_physical_collection_entity_id( $collection_entity_id ) ) {
            $this->_update(
                $this->name,
                $attributes,
                array( $this->primary_key   => ( integer ) $collection_entity_id ),
                $attribute_types,
                array( $this->primary_key   => '%d' ) );
        } else {
            $attributes = array_merge( array( $this->primary_key => $collection_entity_id ), $attributes );
            $attribute_types = array_merge( array( $this->primary_key => $this->get_statement_prepare_type( gettype( $collection_entity_id ) ) ), $attribute_types );
            $this->_insert( $this->name, $attributes, $attribute_types );
        }
    }
    
    public function is_physical_collection_entity_id( $collection_entity_id ) {
        $id_exists = $this->_get_var(
            $this->primary_key,
            $this->name,
            "WHERE $this->primary_key = '%d'",
            array( ( integer ) $collection_entity_id ) );
        return ( 0 < $id_exists ) ? true : false;
    }
    
    public function verify_and_cast_collection_entity_id( &$args ) {
        if ( ! is_array( $args ) ) {
            $collection_entity_id = $args;
        } elseif ( array_key_exists( $this->primary_key, $args ) ) {
            $collection_entity_id = $args[$this->primary_key];
            unset( $args[$this->primary_key] );
        } elseif ( is_array( $args ) ) {
            ajaxmvc_core_exception::throw_error( $this,  "you must have an array element key of $this->primary_key containing a valid integer value." );
        }
        if ( ! $this->is_integer( $collection_entity_id ) ) {
            ajaxmvc_core_exception::throw_error( $this, "$this->primary_key must be an integer value.");
        } else {
            return ( integer ) $collection_entity_id;
        }
    }
    
    public function process_logical_destroy( $collection_entities ) {
        $this->destroy = function() use( $collection_entities ) {
            if ( is_array( $collection_entities ) ) {
                foreach( $collection_entities as $collection_entity ) {
                    $this->process_entity_logical_destroy( $collection_entity );
                }
            } else {
                $this->process_entity_logical_destroy( $collection_entities );
            }
        };
    }
    
    public function process_entity_logical_destroy( $collection_entity_id ) {
        $collection_entity_id = $this->verify_and_cast_collection_entity_id( $collection_entity_id );
        $this->destroy_logical_entity( $this->name, $collection_entity_id );
    }
    
    public function process_physical_destroy( $collection_entities ) {
        $this->destroy = function() use( $collection_entities ) {
            if ( is_array( $collection_entities ) ) {
                foreach( $collection_entities as $collection_entity ) {
                    $this->process_entity_physical_destroy( $collection_entity );
                }
            } else {
                $this->process_entity_physical_destroy( $collection_entities );
            }
        };
    }
    
    public function process_entity_physical_destroy( $collection_entity_id ) {
        $collection_entity_id = $this->verify_and_cast_collection_entity_id( $collection_entity_id );
        $this->_delete(
            $this->name,
            array( $this->primary_key => ( integer ) $collection_entity_id ),
            array( $this->primary_key => '%d' ) );
    }
    
    public function verify_query_properties_before_load() {
        if ( 1 != count( $this->query_order ) ) {
            $message = "you have processed the <strong>{$this->query_order[0]}</strong> method incorrectly,";
            $method = array_shift( $this->query_order );
            $this->query_order = array_map( function( $v ){ return "->{$v}()"; }, $this->query_order );
            $message .= "<strong> $method".implode( '', $this->query_order ).'->load()</strong> is an illegal sequence';
            ajaxmvc_core_exception::throw_error( $this, $message );
        }
    }
    
    public function get_child_properties( $method ) {
        if ( 'ajaxmvc_core_model' == get_class( $this ) ) {
            return false;
        }
        $refclass = new ReflectionClass( $this );
        $properties = array();
        $excluded = array( 'fillable', 'excluded', 'physical_type', 'index', 'unique_key', 'foreign_key', 'primary_key' );
        $excluded = array_merge( $excluded, ( ( is_array( $this->excluded ) ) ? $this->excluded : array() ) );
        foreach ( $refclass->getProperties() as $property ) {
            $name = $property->name;
            if ( $property->class == $refclass->name && false === array_search( $name, $excluded ) && isset( $this->$name ) ) {
                $properties[$name] = $this->$name;
            }
        }
        if ( 0 == count( $properties ) ) {
            $message = "you have called {$method} with no parameters and have no properties currently saved.";
            ajaxmvc_core_exception::throw_error( $this, $message );
        }
        return $properties;
    }
    
    public function proccess_save_properties( &$attributes = null, &$values = null, &$collection_entity_id = null ) {
        if ( null == $attributes && null == $values && null == $collection_entity_id ) {
            $attributes = $this->get_child_properties( 'save' );
            if ( 1 == count( $attributes ) && array_key_exists( $this->primary_key, $attributes ) ) {
                $message = "you have called save() with no parameters and have only the \$this->$this->primary_key property currently saved, and no others.";
                ajaxmvc_core_exception::throw_error( $this, $message );
            }
            foreach ( $attributes as $key => $value ) {
                if( is_array( $value ) ) {
                    $message = 'when calling save and using properties you may not save multiple entities.';
                    ajaxmvc_core_exception::throw_error( $this, $message );
                }
            }
        }
    }
    
    public function process_destroy_properties( &$collection_entity_id = null ) {
        if ( null != $collection_entity_id ) return;
        $attributes = $this->get_child_properties( 'destroy' );
        foreach ( $attributes as $key => $value ) {
            if ( $this->primary_key != $key ) {
                unset( $attributes[$key] );
            } elseif( is_array( $value ) ) {
                $message = 'when calling destroy and using properties you may not destroy multiple entities.';
                ajaxmvc_core_exception::throw_error( $this, $message );
            }
        }
        $collection_entity_id = $this->array_fracture( $attributes, 'values' );
    }
    
    /**
     * Model ORM methods
     */
    
    public function create_logical() {
        if ( 'logical' == $this->state ) {
            //ajaxmvc_core_exception::throw_error( $this, "you are already in a logical state." );
        } elseif( 'physical' == $this->state ) {
            $this->create_logical_collection_from_physical( $this->name );
        }
    }
    
    public function create_physical() {
        if ( 'physical' == $this->state ) {
            //ajaxmvc_core_exception::throw_error( $this, "you are already in a physical state." );
        } elseif( 'logical' == $this->state ) {
            $this->create_physical_collection_from_logical( $this->name );
        }
    }
    
    public function save( $attributes = null, $values = null, $collection_entity_id = null ) {
        $this->proccess_save_properties( $attributes, $values, $collection_entity_id );
        if ( 'logical' == $this->state ) {
            $this->process_logical_save( $attributes, $values, $collection_entity_id );
        } elseif ( 'physical' == $this->state ) {
            $this->process_physical_save( $attributes, $values, $collection_entity_id );
        }
        $this->set_query_order( 'save' );
        return $this;
    }
    
    public function get( $args = null ) {
        if ( null == $args ) {
            $this->select( array( '*' ) )->from( $this->name );
        } elseif( is_array( $args ) ) {
            $this->select( $args )->from( $this->name );
        } elseif( $this->is_integer( $args ) ) {
            $this->select( array( '*' ) )->from( $this->name )->where( array($this->primary_key, '=', $args ) );
        }
        $this->set_query_order( 'get' );
        return $this;
    }
    
    public function destroy( $collection_entity_id = null ) {
        $this->process_destroy_properties( $collection_entity_id );
        if ( 'logical' == $this->state ) {
            $this->process_logical_destroy( $collection_entity_id );
        } elseif ( 'physical' == $this->state ) {
            $this->process_physical_destroy( $collection_entity_id );
        }
        $this->set_query_order( 'destroy' );
        return $this;
    }
    
    public function load( $sql_dump = null ) {
        if ( ! $sql_dump && $this->save && is_callable ( $this->save ) ) {
            $this->verify_query_properties_before_load();
            call_user_func( $this->save );
            return $this->destroy_query_properties();
        } elseif ( ! $sql_dump && $this->destroy && is_callable ( $this->destroy ) ) {
            $this->verify_query_properties_before_load();
            call_user_func( $this->destroy );
            return $this->destroy_query_properties();
        } else {
            return $this->exec( $sql_dump );
        }
    }
    
    /**
     * Helper methods of SQL object chain methods
     */
    
    public function get_sql_object_chain_results( $dump_sql = null ) {
        /*
         * statement is prepared as constructed in respective sql methods
         */
        $this->insert_into_str = ( ! empty( $this->insert_into ) ) ? $this->insert_into['statement'] : '';
        $statement = array(
            'insert_into'   =>  $this->insert_into_str  .$this->break,
            'values'        =>  $this->values           .$this->break,
            'update'        =>  $this->update           .$this->break,
            'set'           =>  $this->set              .$this->break,
            'delete'        =>  $this->delete           .$this->break,
            'select'        =>  $this->select           .$this->break,
            'from'          =>  $this->from             .$this->break,
            'where'         =>  $this->where            .$this->break,
            'group_by'      =>  $this->group_by         .$this->break,
            'having'        =>  $this->having           .$this->break,
            'order_by'      =>  $this->order_by         .$this->break,
            'limit'         =>  $this->limit            .$this->break 
        );
        $this->is_modification_query = false;
        if ( is_array( $this->insert_into ) && ! empty( $this->insert_into ) && '' == $this->values ) {
            if ( $this->collection_set_has_a_logical_collection( $this->collections ) && 'logical' == $this->state ) {
                return $this->process_logical_insert_select_query( $statement );
            }
            $destroy = array( 'values', 'update', 'set', 'delete' );
            $this->destroy_query_statement_elements( $destroy, $statement );
            $statement = $this->stringify_query_elements( $statement );
            $this->is_modification_query = true;
        } elseif ( is_array( $this->insert_into ) && ! empty( $this->insert_into ) && '' != $this->values ) {
            if ( $this->collection_set_has_a_logical_collection( $this->collections ) && 'logical' == $this->state ) {
                return $this->process_logical_insert_value_query( $statement );
            }
            $destroy = array( 'update', 'set', 'delete', 'select', 'from', 'where', 'group_by', 'having', 'order_by', 'limit' );
            $this->destroy_query_statement_elements( $destroy, $statement );
            $statement = $this->stringify_query_elements( $statement );
            $this->is_modification_query = true;
        } elseif( isset( $this->update ) && '' != $this->update ) {
            if ( $this->collection_set_has_a_logical_collection( $this->collections ) ) {
                return $this->process_logical_modification_query( $statement, 'update' );
            }
            $destroy = array( 'insert_into', 'values', 'delete', 'select', 'from' );
            $this->destroy_query_statement_elements( $destroy, $statement );
            $statement = $this->stringify_query_elements( $statement );
            $this->is_modification_query = true;
        } elseif( isset( $this->delete ) && '' != $this->delete ) {
            if ( $this->collection_set_has_a_logical_collection( $this->collections ) ) {
                return $this->process_logical_modification_query( $statement, 'delete' );
            }
            $destroy = array( 'insert_into', 'values', 'update', 'set' , 'select' );
            $this->destroy_query_statement_elements( $destroy, $statement );
            $statement = $this->stringify_query_elements( $statement );
            $this->is_modification_query = true;
        } elseif( isset( $this->select ) && '' != $this->select ) {
            $destroy = array( 'insert_into', 'values', 'update', 'set', 'delete' );
            $this->destroy_query_statement_elements( $destroy, $statement );
            $statement = $this->stringify_query_elements( $statement );
        }
        if( $dump_sql ) {
            print( $statement );
            $this->destroy_query_properties();
            return;
        }
        if ( $this->is_modification_query ) {
            //results are already handled in query method
            $results = $this->query( preg_replace( '/\<br\s\/\>/', '', $statement ) );
        } else {
            $results = $this->wpdb->get_results( preg_replace( '/\<br\s\/\>/', '', $statement ), ARRAY_A );
            $this->cast_query_results( $this->collections, $results );
            $this->handle_query_error( 'get_results', $results );
        }
        $this->destroy_query_properties();
        return $results;
    }
    
    public function collection_set_has_a_logical_collection( $collections ) {
        foreach( $collections as $collection ) {
            if ( 'logical'  == $this->get_collection_state( $collection ) ) {
                return true;
            }
        }
        return false;
    }
    
    public function verify_not_ambiguous_attribute( $collections, $update_set_array ) {
        foreach( $update_set_array as $attribute => $value ) {
            if( $this->value_is_a_collection_attribute( $attribute ) ) continue;
            $occurences = 0;
            foreach( $collections as $collection => $collection_data ) {
                if ( $this->is_collection_attribute( $collection, $attribute ) || $attribute == $this->get_collection_characteristic_primary_key( $collection ) ) {
                    $occurences++;
                }
            }
            if ( 1 < $occurences ) {
                ajaxmvc_core_exception::throw_error( $this, "you have ambiguous attributes in your update set statement, no data was updated. " );
            }
        }
    }
    
    public function process_logical_insert_value_query( &$statement ) {
        
        //capture relevent properties
        $insert_into = $this->insert_into;
        $insert_into_array = $this->insert_into_array;
        $insert_into_values_array = $this->insert_into_values_array;
        $destroy = array( 'values', 'update', 'set', 'delete', 'insert_into', 'insert_into_str' );
        $collection = $this->collections[0];
        $insert_into_primary_key = $this->get_collection_characteristic_primary_key( $collection );

        //clear unneeded properties
        $this->destroy_query_statement_elements( $destroy, $statement );
        foreach( $destroy as $property ) {
            $this->$property = '';
            $this->query_order[$property] = null;
        }
        unset( $this->is_modification_query );
        
        //rename record keys to insert_into keys
        if ( is_array( $insert_into_values_array[0] ) ) {
            foreach( $insert_into_values_array as $index => $record ) {
                $this->verify_logical_insert_into_record( $collection, $insert_into_array, $insert_into_values_array[$index] );
            }
        } else {
            $this->verify_logical_insert_into_record( $collection, $insert_into_array, $insert_into_values_array );
        }
        
        //iterate and save attributes
        $collection_entity_id = null;
        foreach( $insert_into_values_array as $key => $value ) {
            if ( is_array( $value ) ) {
                foreach( $value as $attribute => $attribute_value ) {
                    if ( $insert_into_primary_key == $attribute && null == $collection_entity_id ) {
                        if ( $this->collection_entity_id_exists( $collection, $attribute_value ) ) {
                            ajaxmvc_core_exception::throw_error( $this, "id of $attribute_value already exists." );
                        } else {
                            $collection_entity_id = $attribute_value;
                            continue;
                        }
                    } elseif ( $insert_into_primary_key != $attribute && null == $collection_entity_id ) {
                        $collection_entity_id = $this->get_next_collection_entity_id( $collection );
                    }
                    $this->set_collection_entity_attribute( $collection, $collection_entity_id, $attribute, $attribute_value );
                }
                $collection_entity_id = null;
            } else {
                if ( $insert_into_primary_key == $key && null == $collection_entity_id ) {
                    if ( $this->collection_entity_id_exists( $collection, $value ) ) {
                        ajaxmvc_core_exception::throw_error( $this, "id of $value already exists." );
                    } else {
                        $collection_entity_id = $value;
                        continue;
                    }
                } elseif ( $insert_into_primary_key != $key && null == $collection_entity_id ) {
                    $collection_entity_id = $this->get_next_collection_entity_id( $collection );
                }
                $this->set_collection_entity_attribute( $collection, $collection_entity_id, $key, $value );
            }
        }
    }
    
    public function process_logical_insert_select_query( &$statement ) {
        
        //capture relevent properties                                                              
        $insert_into = $this->insert_into;
        $insert_into_array = $this->insert_into_array;
        $destroy = array( 'values', 'update', 'set', 'delete', 'insert_into', 'insert_into_str' );
        $join_meta_data = $this->join_meta_data;
        $collection = $this->collections[0];
        $insert_into_primary_key = $this->get_collection_characteristic_primary_key( $collection );

        //clear unneeded properties
        $this->destroy_query_statement_elements( $destroy, $statement );
        foreach( $destroy as $property ) {
            $this->$property = '';
            $this->query_order[$property] = null;
        }
        unset( $this->is_modification_query );
        
        //get the result
        $result = $this->exec();
        
        //iterate through results and save attributes
        foreach( $result as $index => $record ) {
            $this->verify_logical_insert_into_record( $collection, $insert_into_array, $record );
            foreach( $record as $attribute => $value ) {
                if ( $insert_into_primary_key == $attribute && null == $collection_entity_id ) {
                    if ( $this->collection_entity_id_exists( $collection, $value ) ) {
                        ajaxmvc_core_exception::throw_error( $this, "id of $value already exists." );
                    } else {
                        $collection_entity_id = $value;
                        continue;
                    }
                } elseif ( $insert_into_primary_key != $attribute && null == $collection_entity_id ) {
                    $collection_entity_id = $this->get_next_collection_entity_id( $collection );
                }
                $this->set_collection_entity_attribute( $collection, $collection_entity_id, $attribute, $value );
            }
            $collection_entity_id = null;
        }
    }
    
    public function verify_logical_insert_into_record( $collection, array &$insert_into_array, array &$record ) {
        if ( count( $record ) != count( $insert_into_array ) ) {
            ajaxmvc_core_exception::throw_error( $this, 'your select record attribute count does not equal your insert into attribute count.' );
        }
        $record_keys = $this->array_fracture( $record, 'keys' );
        foreach( $insert_into_array as $index => $attribute ) {
            $primary_key = $this->get_collection_characteristic_primary_key( $collection );
            //default primary key to integer
            if ( $primary_key == $attribute ) {
                $insert_into_type = 'integer';
            } else {
                $insert_into_type = $this->get_collection_attribute_script_db_type( $collection, $attribute );
            }
            $record_type = gettype( $this->cast_as_physical_collection_storage_type( $record[$record_keys[$index]] ) );
            if( $record_type == $insert_into_type ) {
                $new_record[$attribute] = $record[$record_keys[$index]];
            } else {
                ajaxmvc_core_exception::throw_error( $this, 'your select record attribute type does not match your insert into attribute match.' );
            }
        }
        $record = $new_record;
    }
    
    public function process_logical_modification_query( &$statement, $type ) {
        
        //capture relevent properties
        if ( 'update' == $type ) {
            $this->from = preg_replace( '/^UPDATE/', 'FROM', $this->update );
            $update_set_array = $this->update_set_array;
            $this->verify_not_ambiguous_attribute( $this->join_meta_data, $update_set_array );
            $destroy = array( 'insert_into', 'values', 'update', 'set', 'delete' );
            $join_meta_data = $this->join_meta_data;
        } elseif ( 'delete' == $type ) {
            $delete_array = $this->delete_array;
            $destroy = array( 'insert_into', 'values', 'update', 'set' , 'select', 'delete' );
            $join_meta_data = $this->join_meta_data;
        }
        
        //clear unneeded properties
        $this->destroy_query_statement_elements( $destroy, $statement );
        foreach( $destroy as $property ) {
            $this->$property = '';
            $this->query_order[$property] = null;
        }
        unset( $this->is_modification_query );
        
        //get a result set of all relevent join attributes
        $logical_modification_query_select = $this->get_logical_modification_query_select();
        $this->select( $logical_modification_query_select );
        /*
         * IMPORTANT:
         * all of the other query properties conditions are still set
         * resulting in a select query which runs based on all of the from,join,where,having,group by
         * that were populated in the original update statement, we run a select query
         * based on all of these conditions where the select fields or attributes
         * are the fields of the join, based on these fields we can select the collections_entity_id or
         * primary_key, which is a unique identifier for a collection and then we can run a 
         * set_collection_entity_attribute foreach identifier
         * based on the original set clause and these collection_entity_id s'
         * NOTE:
         * This exec() will reset all query properties
         */
        $result = $this->exec();
        
        //iterate through appropriate data structures and split query results into collection based data structures
        $collection_entity_id_parameters_set = array();
        foreach ( $join_meta_data as $collection => $join_attributes ) {
            $collection_entity_id_parameters_record = array();
            foreach( $result as $record_index => $record ) {
                $collection_entity_id_parameters = array();
                foreach( $record as $attribute => $value ) {
                    if ( 1 == preg_match( '/^'.$collection.'/', $attribute ) ) {
                        $attribute_name = preg_replace( '/^'.$collection.'_/', "$collection.", $attribute );
                        $collection_entity_id_parameters[$attribute_name] = $value;
                    }
                }
                $collection_entity_id_parameters_record[] = $collection_entity_id_parameters;
            }
            $collection_entity_id_parameters_set[$collection] = $collection_entity_id_parameters_record;
        }

        //iterate through new data structures
        foreach( $collection_entity_id_parameters_set as $collection => $record_set ) {
            foreach( $record_set as $index => $record ) {
                $primary_key = $this->get_collection_characteristic_primary_key( $collection );
                if ( $this->is_collection( $collection ) && 'logical' == $this->get_collection_state( $collection ) ) {
                    $this_collection = $this->get_logical_collection( $collection );
                } else {
                    $this_collection = $collection;
                }
                $sql = "SELECT $primary_key FROM $this_collection WHERE ".implode( ' AND ', array_map( function ( $v, $k ) { return $k . ' = ' . $v; }, $record, array_keys( $record ) ) );
                $collection_entity_id = $this->wpdb->get_var( $sql );
                if ( 'update' == $type ) {
                    $this->process_logical_update( $update_set_array, $collection, $primary_key, $collection_entity_id );
                } elseif ( 'delete' == $type ) {
                    $this->process_logical_delete( $delete_array, $collection, $primary_key, $collection_entity_id );
                }
            }
        }
    }
    
    public function process_logical_update( $update_set_array, $collection, $primary_key, $collection_entity_id  ) {
        foreach( $update_set_array as $attribute => $value ) {
            //we have already checked for ambigous update attributes
            if ( $this->is_collection_attribute( $collection, $attribute ) || $attribute == $this->get_collection_characteristic_primary_key( $collection ) ) {
                if ( 'logical'  == $this->get_collection_state( $collection ) ) {
                    $this->set_collection_entity_attribute( $collection, $collection_entity_id, $attribute, $value );
                } elseif ( 'physical'  == $this->get_collection_state( $collection ) ) {
                    $this->_update(
                        $collection,
                        array( $attribute   => $value ),
                        array( $primary_key    => $collection_entity_id ),
                        array( $attribute   => $this->get_statement_prepare_type( gettype( $value ) )) ,
                        array( $primary_key => '%d' ) );
                }
            }
        }
    }
    
    public function process_logical_delete( $delete_array, $collection, $primary_key, $collection_entity_id ) {
        if ( is_array( $delete_array ) ) {
            foreach( $delete_array as $delete_collection ) {
                if ( $collection ==  $delete_collection ) {
                    if ( 'logical'  == $this->get_collection_state( $collection ) ) {
                        $this->destroy_logical_entity( $collection, $collection_entity_id );
                    } elseif ( 'physical'  == $this->get_collection_state( $collection ) ) {
                        $this->_delete(
                            $collection,
                            array( $primary_key    => $collection_entity_id ),
                            array( $primary_key => '%d' ) );
                    }
                }
            }
        }
    }
    
    public function get_logical_modification_query_select() {
        if ( ! is_array( $this->join_meta_data ) || 1 == count( $this->join_meta_data ) ) {
            $primary_key = $this->get_collection_characteristic_primary_key( $this->collections[0] );
            return array( "{$primary_key} AS {$this->collections[0]}_{$primary_key}" );
        }
        $logical_modification_query_select = array();
        foreach( $this->join_meta_data as $collection => $join_attributes ) {
            if ( is_array( $join_attributes ) ) {
                foreach( $join_attributes as $index => $join_attribute ) {
                    //guarantees uniqueness of the field name
                    $join_attributes[$index] = $join_attribute.' AS '.preg_replace( '/\./', '_', $join_attribute );
                }
                $logical_modification_query_select = array_merge( $logical_modification_query_select, $join_attributes );
            }
        }
        return $logical_modification_query_select;
    }
    
    public function cast_query_results( array $collections, array &$results ) {
        if ( empty( $collections ) ) return;
        if ( is_array( $results[0] ) ) {
            $attributes = $this->array_fracture( $results[0], 'keys' );
        }
        if ( empty( $attributes ) ) return;
        foreach( $attributes as $attribute ) {
            foreach( $collections as $collection ) {
                //default everything to string
                $attribute_type_map[$attribute] = 'string';
                //if it is id we can assume integer
                $primary_key = $this->get_collection_characteristic_primary_key( $collection );
                if ( $primary_key == $attribute ) {
                    $attribute_type_map[$attribute] = 'integer';
                    break;
                }
                //test for first match, if found then break
                if ( $this->is_collection_attribute( $collection, $attribute ) ) {
                    $attribute_type_map[$attribute] = $this->get_collection_attribute_script_db_type( $collection, $attribute );
                    break;
                }
            }
        }
        foreach( $results as $index => $record ) {
            foreach( $record as $attribute => $value ) {
                settype( $results[$index][$attribute],  $attribute_type_map[$attribute] );
            }
        }
    }
    
    public function destroy_query_statement_elements( array $delete_elements, array &$query_array ) {
        foreach( $delete_elements as $ele ) {
            unset( $query_array[$ele] );
        }
    }
    
    public function stringify_query_elements( array $query_array ) {
        return implode( ' ', $this->array_fracture( $query_array, 'values' ) );
    }
    
    public function get_attribute_names_from_array( array $attributes ) {
        $attributes = array_map( function( $attribute ) {
            $attribute = $this->sanitize_single_sql_input( $attribute );
            return $attribute;
        }, $attributes );
        return implode( ',', $attributes );
    }
    
    public function set_query_order( $clause ) {
        $count = count( $this->query_order );
        $this->last_query_clause_index = ( 0 < $count ) ? ( $count - 1 ) : 0;
        if ( ! empty( $this->query_order ) && $clause != $this->query_order[$this->last_query_clause_index] ) {
            $this->query_order[] = $clause;
            $this->last_query_clause_index = ( count( $this->query_order ) - 1 );
            $this->last_query_clause = $this->query_order[$this->last_query_clause_index];
        } elseif ( empty( $this->query_order ) ) {
            $this->query_order[] = $clause;
            $this->last_query_clause_index = 0;
            $this->last_query_clause = $this->query_order[$this->last_query_clause_index];
        }
    }
    
    public function destroy_query_properties() {
        $this->query_order[] = 'delete_array';
        $this->query_order[] = 'insert_into_array';
        $this->query_order[] = 'update_set_array';
        $this->query_order[] = 'is_from_join';
        $this->query_order[] = 'join_meta_data';
        $this->query_order[] = 'collections';
        foreach( $this->query_order as $key => $property ) {
            $this->{( string )$property} = '';
            $this->query_order[$key] = null;
        }
        $this->query_order = array_filter( $this->query_order );
    }
    
    public function set_conditions_nest( $depth ){
        for($i = 0; $i < $depth; $i++) {
            $nest['escaped'] .= '\)';
            $nest['unescaped'] .= ')';
        }
        return $nest;
    }
    
    public function get_conditions_nest() {
        $clause = $this->last_query_clause;
        preg_match( '/\)+(?!.*\)+)/', $this->$clause, $match );
        return substr_count( $match[0], ')' );
    }
    
    public function process_values_clause( array $values ) {
        $this->verify_values_clause( $values );
        $index = 0; 
        $string = '';
        array_map( function($val) use( &$index, &$string ) {
            $string .= $this->wpdb->prepare( $this->get_statement_prepare_type( gettype( $val ) ), array( $val ) ).', ';
            $index++;
        }, $values );
        return '('.preg_replace( '/, $/', '', $string ).')';
    }

    public function process_logical_conditions( array &$conditions, $internal_multi_type = null, $nesting = null, $inclusive = null ) {
        if ( 1 != preg_match( '/^(where)$/',   $this->last_query_clause )
        && ( 1 != preg_match( '/^(having)$/',  $this->last_query_clause ) )
        && ( 1 != preg_match( '/^(where)$/',   $internal_multi_type ) )
        && ( 1 != preg_match( '/^(having)$/',  $internal_multi_type ) ) ) {
            ajaxmvc_core_exception::throw_error( $this,  'conditions may ony be processed after where() or having() called.' );
        }
        if ( true == $this->is_from_join ) {
            $this->process_join_meta_data( $conditions, 'logical' );
        }
        if ( array_key_exists( 0, $conditions ) && is_array( $conditions[0] ) ) {
            /*
             * if is an array of arrays,which we can 
             * assume is nested logical statements,
             * iterate and process
             */
            foreach( $conditions as $index => $condition ) {
                if ( array_key_exists( 'or', $condition ) ) {
                    /*
                     * recursive call to continually burrow deeper 
                     * into the data structure and process the logical
                     * conditions
                     */
                     $this->process_logical_conditions( $condition['or'], 'or', $nesting, false );
                     array_shift( $condition );
                }
                if ( array_key_exists( 'and', $condition ) ) {
                    /*
                     * recursive call to continually burrow deeper
                     * into the data structure and process the logical
                     * conditions
                     */
                     $this->process_logical_conditions( $condition['and'], 'and', $nesting, false );
                     array_shift( $condition );
                }
                /*
                 * if there is a valid logical comparator stored 
                 * in the first element, and count is equal to 4, example:
                 * array(or,id,=,5,true) 
                 */
                if ( preg_match( '/(and|or)/', $condition[0] ) && 4 == count($condition) ) {
                    $internal_multi_type = strtolower( $condition[0] );
                    array_shift( $condition );
                }
                //test the $internal_multi_type, if it is where or having
                if ( 'where' == $internal_multi_type || 'having' == $internal_multi_type ) {
                    //remove the inclusivity and nesting options
                    $inclusive = null;
                    $nesting = null;
                    /*
                     * set the method to be executed as, where() or having(),
                     * which will actually in turn continue to process the condition 
                     * and update properties, $this->where, $this->having
                     */
                    $logical_method = $internal_multi_type;
                //test the $internal_multi_type, if it is and or or
                } 
                if ( 'and' == $internal_multi_type || 'or' == $internal_multi_type) {
                    /*
                     * if it is the first iteration and $inclusive is true, 
                     * which will only happen when there is recursion and $inclusive
                     * is explicitly set to true, all other times this function is called it will not set the
                     * last optional parameter of $inclusive. based on that fact and that it is 
                     * the first iteration of a new nested array we reset $inclusive to true, to 
                     * explicitly express its state
                     */
                    if ( $index == 0 ) {
                        $inclusive = false;
                    } else {
                        $inclusive = true;
                    }
                    /*
                     * if the previous array processed was a recursive
                     * set inclusion to false because we want to kill recursion
                     * strip the nest of this term so it is appended to the
                     * correct nest
                     */
                    if ( is_array( $conditions[$index - 1] ) ) {
                        if ( array_key_exists( 'and', $conditions[$index - 1] ) ) {
                            $inclusive = false;
                            $strip_nest = true;
                        }
                        if ( array_key_exists( 'or', $conditions[$index - 1] ) ) {
                            $inclusive = false;
                            $strip_nest = true;
                        }
                    }
                    //if inclusive we need to account for nesting
                    if ( $inclusive == true ) {
                        $nesting = $this->get_conditions_nest();
                    }
                    
                    /*
                     * set the method to be executed as, and() or or(),
                     * which will in turn process the condition, update properties etc
                     */
                    $logical_method = '_'.$internal_multi_type.'_';
                }
                /*
                 * finally execute the logical method: where(),having(),and(),or() 
                 * in order to process the actual data structure which at
                 * this point is a three term array(attribute,comparison operator,value)
                 * example: array(id,=,5) etc. etc. and to update the correct properties
                 * $this->where, $this->having etc. etc.
                 */
                $this->$logical_method( $condition, $inclusive, $nesting, $strip_nest );
                $internal_multi_type = null;
            }
        } else {
            /*
             * at this point there is no 
             * recursion and we can actually return
             * a prepared single comparison statement
             * as ( id = 5 )
             */
            if ( ! empty($conditions) ) {
                return $this->sanitize_and_prepare_condition( $conditions );
            }
        }
    }
    
    public function value_is_a_collection_attribute( $condition ) {
        if ( 1 == substr_count ( $condition, '.' ) ) {
            $collection = preg_replace( '/\.(.*)$/', '', $condition );
            $attribute = preg_replace( '/^(.*)\./', '', $condition );
            if ( $this->is_collection_attribute( $collection, $attribute ) || $attribute == $this->get_collection_characteristic_primary_key( $collection ) ) {
                return true;
            }
        }
        return false;
    }
    
    public function sanitize_and_prepare_condition( array $conditions ) {
        if ( 3 !=  count( $conditions ) ) {
            ajaxmvc_core_exception::throw_error( $this,  'logical clause must contain exactly 3 terms: attribute, operator, value.' );
        }
        //sanitize like statements
        if ( 'LIKE' == $conditions[1] ) {
            $this->sanitize_like_condition( $conditions );
        }
        //convert boolean to integer
        $value = $this->cast_as_physical_collection_storage_type( $conditions[2] );
        //set the value place equal to the $wpdb::prepare type of %d,%f,%s
        if ( $this->value_is_a_collection_attribute( $conditions[2] ) ) {
            $conditions[2] = $this->sanitize_single_sql_input( $conditions[2] );
        } else {
            $conditions[2] = $this->get_statement_prepare_type( gettype( $conditions[2] ) );
        }
        $conditions[1] = $this->sanitize_single_sql_input( $conditions[1] );
        $conditions[0] = $this->sanitize_single_sql_input( $conditions[0] );
        //prepare and return the statement
        return $this->wpdb->prepare( implode( ' ', $conditions ), $value );
    }
    
    public function sanitize_like_condition( array &$conditions ) {
        //used to replace any correctly escaped percents with a random 32 byte hex value
        $escaped_percent = md5( rand() );
        if ( preg_match( '/(\\\\%)/', $conditions[2] ) ) {
            $conditions[2] = preg_replace( '/(\\\\%)/', $escaped_percent, $conditions[2] );
        }
        //used to replace any correctly escaped underscores with a random 32 byte hex value
        $escaped_underscore = md5( rand() );
        if ( preg_match( '/(\\\\_)/', $conditions[2] ) ) {
            $conditions[2] = preg_replace( '/(\\\\_)/', $escaped_underscore, $conditions[2] );
        }
        //used to replace any correctly escaped asterisks with a random 32 byte hex value
        $escaped_asterisk = md5( rand() );
        if ( preg_match( '/(\\\\\*)/', $conditions[2] ) ) {
            $conditions[2] = preg_replace( '/(\\\\\*)/', $escaped_asterisk, $conditions[2] );
        }
        $unescaped_percent = md5( rand() );
        $percent_count = 0;
        //split the like condition value into a string array
        $like_array = str_split( $conditions[2] );
        foreach( $like_array as $key => $value ) {
            if ( '_' != $value && '*' != $value && '%' != $value ) continue;
            /*
             * as any properly escaped underscores were already removed, 
             * if an underscore appears anywhere in the string escape it
             */
            if ( '_' == $value ) {
                $like_array[$key] = preg_replace( '/(\\\\_)/', $escaped_underscore, $this->wpdb->esc_like( $value ) );
            }
            /*
             * as any properly escaped asterisks are already removed, and any
             * properly escaped underscores are removed, and any remaining underscores are
             * properly escaped and removed, then if an asterisk appears anywhere,
             * replace it with an unescaped underscore which is a proper SQL wildcard
             */
            if ( '*' == $value ) {
                $like_array[$key] = preg_replace( '/(\*)/', '_', $value );
            }
            /*
             * as any properly escaped percents were already removed, 
             * if a percent appears in any position other than first or last then escape it
             */
            if (  0 != $key && ( count( $like_array ) - 1 ) != $key && '%' == $value ) {
                $like_array[$key] = preg_replace( '/(\\\\%)/', $escaped_percent, $this->wpdb->esc_like( $value ) );
            }
            /*
             * if there is a percent in first or last
             * position then replace it with a random 32 byte hex value
             */
            if ( ( 0 == $key || ( count( $like_array ) - 1 ) == $key ) &&  '%' == $value ) {
                $like_array[$key] = preg_replace( '/(\%)/', $unescaped_percent, $value );
                $percent_count++;
            }
        }
        //replace any $escaped_percent with correctly escaped percent
        $conditions[2] = preg_replace( '/'.$escaped_percent.'/', '\\\\%', implode( '', $like_array ) );
        //replace any $escaped_underscore with correctly escaped underscore
        $conditions[2] = preg_replace( '/'.$escaped_underscore.'/', '\\\\_', $conditions[2] );
        //there was no beginning or ending percent
        if( 0 == $percent_count ) {
            //replace any $escaped_asterisk with correctly escaped asterisk
            $conditions[2] = '%'.preg_replace( '/'.$escaped_asterisk.'/', '\\\\*', $conditions[2] ).'%';
        //there was a beginning or ending percent
        } else {
            //replace any $escaped_asterisk with correctly escaped asterisk
            $conditions[2] = preg_replace( '/'.$escaped_asterisk.'/', '\\\\*', $conditions[2] );
            //replace first or last $unescaped_percent with correctly unescaped percent
            $conditions[2] = preg_replace( '/'.$unescaped_percent.'/', '%', $conditions[2] );
        }
    }
    
    public function verify_values_clause( array $values ) {
        if ( 1 != preg_match( '/^(insert_into)$/', $this->last_query_clause ) ) {
            ajaxmvc_core_exception::throw_error( $this, 'insert_into() must be called before values().');
        }
        $count_f = $this->insert_into['count'];
        $count_v = count( $values );
        if ( $count_f !== $count_v ) {
            $message = "count of <strong>\$attributes array: {$count_f}</strong> does not exactly match count of <strong>\$values array: {$count_v}</strong>.";
            ajaxmvc_core_exception::throw_error( $this,  $message );
        }
    }
    
    public function process_set_clause( array $attributes_to_values ) {
        $string = '';
        foreach( $attributes_to_values as $key => $value ) {
            $string .= $this->sanitize_and_prepare_condition( array( $key, '=', $value ) ).', ';
        }
        return preg_replace( '/,\s$/', '', $string );
    }
    
    public function get_insert_clause( $collection, array $attributes ) {
        $this->collections[] = $collection;
        return "INSERT INTO {$collection} (".$this->get_attribute_names_from_array( $attributes ).') ';
    }
    
    public function get_values_clause( array $values ) {
        if ( ! empty( $values ) && is_array( $values[0] ) ) {
            $string = '';
            foreach( $values as $value_array ) {
                $string .= $this->process_values_clause( $value_array ).', ';
            }
            return 'VALUES '.preg_replace( '/, $/', '', $string );
        }
        return 'VALUES '.$this->process_values_clause( $values );
    }
    
    public function get_update_clause( $collection ) {
        if ( is_array( $collection ) ) {
            $collection_string = '';
            foreach( $collection as $single_collection ) {
                $this->collections[] = $single_collection;
                $this->join_meta_data[$single_collection] = true;
                if ( $this->is_collection( $single_collection ) && 'logical' == $this->get_collection_state( $single_collection ) ) {
                    $collection_string .= $this->get_logical_collection( $single_collection ).', ';
                } elseif ( $this->is_collection( $single_collection ) && 'physical' == $this->get_collection_state( $single_collection ) ) {
                    $collection_string .= $single_collection.', ';
                }
            }
            return 'UPDATE '.preg_replace( '/,\s$/', ' ', $collection_string );
        } else {
            $this->collections[] = $collection;
            $this->join_meta_data[$collection] = true;
            if ( $this->is_collection( $collection ) && 'logical' == $this->get_collection_state( $collection ) ) {
                return 'UPDATE '.$this->get_logical_collection( $collection ).' ';
            } elseif ( $this->is_collection( $collection ) && 'physical' == $this->get_collection_state( $collection ) ) {
                return "UPDATE {$collection} ";
            }
        }
    }
    
    public function get_set_clause( array $attributes_to_values ) {
        return 'SET '.$this->process_set_clause( $attributes_to_values );
    }
    
    public function get_delete_clause( $collection ) {
        if ( is_array( $collection ) ) {
            return 'DELETE '.implode( ', ', $collection );
        } else {
            return 'DELETE '.$collection;
        }
    }
    public function get_select_clause( array $attributes ) {
        return 'SELECT '.$this->get_attribute_names_from_array( $attributes ).' ';
    }

    public function get_from_clause( $collection ) {
        if ( is_array( $collection ) ) {
            $collection_string = '';
            foreach( $collection as $single_collection ) {
                $this->collections[] = $single_collection;
                $this->join_meta_data[$single_collection] = true;
                if ( $this->is_collection( $single_collection ) && 'logical' == $this->get_collection_state( $single_collection ) ) {
                    $collection_string .= $this->get_logical_collection( $single_collection ).', ';
                } elseif ( $this->is_collection( $single_collection ) && 'physical' == $this->get_collection_state( $single_collection ) ) {
                    $collection_string .= $single_collection.', ';
                }
            }
            return 'FROM '.preg_replace( '/,\s$/', ' ', $collection_string );
        } else {
            $this->collections[] = $collection;
            $this->join_meta_data[$collection] = true;
            if ( $this->is_collection( $collection ) && 'logical' == $this->get_collection_state( $collection ) ) {
                return 'FROM '.$this->get_logical_collection( $collection ).' ';
            } else {
                return "FROM {$collection} ";
            }
        }
    }
    
    public function get_join_clause( array $collection ) {
        foreach( $collection as $key => $array ) {
            if ( 'collections' == strtolower( $key ) && is_array( $array ) ) {
                if ( 3 != count( $array ) ) {
                    ajaxmvc_core_exception::throw_error( $this,  'join must contain exactly 3 terms: collection, join type, and collection.' );
                }
                if ( $this->is_collection( $array[2] ) && 'logical' == $this->get_collection_state( $array[2] ) ) {
                    $array[2] = $this->get_logical_collection( $array[2] ).' ';
                }
                $collections = implode( ' ', $array );
            }
            if ( 'attributes' == strtolower( $key ) && is_array( $array ) ) {
                if ( ! is_array( $array[0] ) ) {
                    if ( 3 != count( $array ) ) {
                        ajaxmvc_core_exception::throw_error( $this,  'on must contain exactly 3 terms: attribute, operator(=, !=, etc.), and attribute.' );
                    }
                    $attributes = implode( ' ', $array );
                } else {
                    foreach( $array as $sub_array ) {
                        if ( 3 != count( $sub_array ) ) {
                            ajaxmvc_core_exception::throw_error( $this,  'on must contain exactly 3 terms: attribute, operator(=, !=, etc.), and attribute.' );
                        }
                        $attributes .= implode( ' ', $sub_array ) . ' AND ';
                    }
                    $attributes = preg_replace( '/AND\s$/', '', $attributes );
                }
            }
        }
        return "({$collections} ON  {$attributes})";
    }
    
    public function get_where_clause( $clause = 'where', $conditions, $inclusive = null, $nesting = null, $strip_nest = null ) {
        $nest = $this->set_conditions_nest($nesting);
        if ( $inclusive && ! $nesting ) {
            $this->$clause = preg_replace( '/\)(\s|)$/', '', $this->$clause ).' WHERE '.$conditions.') ';
        } elseif ( $nesting && ! $inclusive && ! $strip_nest ) {
            $this->$clause = preg_replace( '/'.$nest['escaped'].'(\s|)$/', '', $this->$clause ).' WHERE ('.$conditions.$nest['unescaped'].') ';
        } elseif ( $nesting && ! $inclusive && $strip_nest ) {
            $this->$clause = preg_replace( '/'.$nest['escaped'].'(\s|)$/', '', $this->$clause ).' WHERE '.$conditions.$nest['unescaped'].' ';
        } elseif ( $nesting && $inclusive ) {
            $this->$clause = preg_replace( '/'.$nest['escaped'].'(\s|)$/', '', $this->$clause ).' WHERE '.$conditions.$nest['unescaped'].' ';
        } else {
           $this->$clause .= ' WHERE ('.$conditions.') ';
        }
    }
    
    public function get_group_by_clause( array $attributes ) {
        return 'GROUP BY '.$this->get_attribute_names_from_array( $attributes ).' ';
    }
    
    public function get_having_clause( $clause = 'having', $conditions, $inclusive = null, $nesting = null, $strip_nest = null ) {
        $nest = $this->set_conditions_nest($nesting);
        if ( $inclusive && ! $nesting ) {
            $this->$clause = preg_replace( '/\)(\s|)$/', '', $this->$clause ).' HAVING '.$conditions.') ';
        } elseif ( $nesting && ! $inclusive && ! $strip_nest ) {
            $this->$clause = preg_replace( '/'.$nest['escaped'].'(\s|)$/', '', $this->$clause ).' HAVING ('.$conditions.$nest['unescaped'].') ';
        } elseif ( $nesting && ! $inclusive && $strip_nest ) {
            $this->$clause = preg_replace( '/'.$nest['escaped'].'(\s|)$/', '', $this->$clause ).' HAVING '.$conditions.$nest['unescaped'].' ';
        } elseif ( $nesting && $inclusive ) {
            $this->$clause = preg_replace( '/'.$nest['escaped'].'(\s|)$/', '', $this->$clause ).' HAVING '.$conditions.$nest['unescaped'].' ';
        } else {
           $this->$clause .= ' HAVING ('.$conditions.') ';
        }
    }
    
    public function get_order_by_clause( array $attributes ) {
        return 'ORDER BY '.$this->get_attribute_names_from_array( $attributes ).' ';
    }
    
    public function get_limit_clause( $conditions ) {
        return 'LIMIT '.$this->wpdb->prepare( '%d, %d', array( $conditions[0], $conditions[1] ) ).' ';
    }
    
    public function get_and_condition( $clause, $conditions, $inclusive = null, $nesting = null, $strip_nest = null ) {
        $nest = $this->set_conditions_nest($nesting);
        if ( $inclusive && ! $nesting ) {
            $this->$clause = preg_replace( '/\)(\s|)$/', '', $this->$clause ).' AND '.$conditions.') ';
        } elseif ( $nesting && ! $inclusive && ! $strip_nest ) {
            $this->$clause = preg_replace( '/'.$nest['escaped'].'(\s|)$/', '', $this->$clause ).' AND ('.$conditions.$nest['unescaped'].') ';
        } elseif ( $nesting && ! $inclusive && $strip_nest ) {
            $this->$clause = preg_replace( '/'.$nest['escaped'].'(\s|)$/', '', $this->$clause ).' AND '.$conditions.$nest['unescaped'].' ';
        } elseif ( $nesting && $inclusive ) {
            $this->$clause = preg_replace( '/'.$nest['escaped'].'(\s|)$/', '', $this->$clause ).' AND '.$conditions.$nest['unescaped'].' ';
        } else {
            $this->$clause .= ' AND ('.$conditions.') ';
        }
    }
    
    public function get_or_condition( $clause, $conditions, $inclusive = null, $nesting = null, $strip_nest = null ) {
        $nest = $this->set_conditions_nest($nesting);
        if ( $inclusive && ! $nesting ) {
            $this->$clause = preg_replace( '/\)(\s|)$/', '', $this->$clause ).' OR '.$conditions.') ';
        } elseif ( $nesting && ! $inclusive && ! $strip_nest ) {
            $this->$clause = preg_replace( '/'.$nest['escaped'].'(\s|)$/', '', $this->$clause ).' OR ('.$conditions.$nest['unescaped'].') ';
        } elseif ( $nesting && ! $inclusive && $strip_nest ) {
            $this->$clause = preg_replace( '/'.$nest['escaped'].'(\s|)$/', '', $this->$clause ).' OR '.$conditions.$nest['unescaped'].' ';
        } elseif ( $nesting && $inclusive ) {
            $this->$clause = preg_replace( '/'.$nest['escaped'].'(\s|)$/', '', $this->$clause ).' OR '.$conditions.$nest['unescaped'].' ';
        } else {
            $this->$clause .= ' OR ('.$conditions.') ';
        }
    }
    
    /**
     * SQL object chain methods
     */
    
    public function insert_into( $collection, array $attributes ) {
        $this->insert_into_array = $attributes;
        $this->insert_into['statement'] = $this->get_insert_clause( $collection, $attributes );
        $this->insert_into['count'] = count( $attributes );
        $this->set_query_order( 'insert_into' );
        return $this;
    }
    
    public function values( array $values ) {
        $this->insert_into_values_array = $values;
        $this->values = $this->get_values_clause( $values );
        $this->set_query_order( 'values' );
        return $this;
    }
    
    public function update( $collection ) {
        if ( is_array( $collection ) && 1 < count( $collection ) ) {
            $this->is_from_join = true;
        }
        $this->update = $this->get_update_clause( $collection );
        $this->set_query_order( 'update' );
        return $this;
    }
    
    public function set( array $attributes_to_values ) {
        $this->update_set_array = $attributes_to_values;
        $this->set = $this->get_set_clause( $attributes_to_values );
        $this->set_query_order( 'set' );
        return $this;
    }
    
    public function delete( $collection = null ) {
        $this->delete_array = $collection;
        $this->delete = $this->get_delete_clause( $collection );
        $this->set_query_order( 'delete' );
        return $this;
    }
    
    public function select( array $attributes ) {
        if ( is_array( $this->insert_into ) && ! empty( $this->insert_into ) ) {
            if ( '*' != $attributes[0] ) {
                $count_f = count( $attributes );
                if ( $this->insert_into['count'] != $count_f ) {
                    $message = "insert_into <strong>count of {$this->insert_into['count']}</strong> does not equal the select <strong>count of {$count_f}</strong>.";
                    ajaxmvc_core_exception::throw_error( $this,  $message );
                }
            }
        }
        $this->select = $this->get_select_clause( $attributes );
        $this->set_query_order( 'select' );
        return $this;
    }
    
    public function from( $collection ) {
        if ( is_array( $collection ) && 1 < count( $collection ) ) {
            $this->is_from_join = true;
        }
        $this->from = $this->get_from_clause( $collection );
        $this->set_query_order( 'from' );
        return $this;
    }
    
    public function join( $collection, $join_type = null ) {
        if ( $join_type && 1 != preg_match( '/^(INNER|LEFT|RIGHT|LEFT OUTER|RIGHT OUTER)$/', trim( strtoupper( $join_type ) ) ) ) {
            ajaxmvc_core_exception::throw_error( $this,  "{$join_type} is not a valid join type." );
        }
        $join = ( null == $join_type ) ? 'INNER JOIN' : "{$join_type} JOIN";
        if( $this->select ) {
            $this->from = preg_replace( '/^FROM /', '', $this->from );
            $this->join = array( 'collections' => array( $this->from, $join, $collection ) );
            /*
             * at this point even if is logical state $collection is still a one word term
             * and not a derived table, we populate this array in order to correctly
             * return php types to the user
             */
            $this->collections[] = $collection;
        } elseif( $this->delete ) {
            $this->from = preg_replace( '/^FROM /', '', $this->from );
            $this->join = array( 'collections' => array( $this->from, $join, $collection ) );
            $this->collections[] = $collection;
            $this->join_meta_data[$collection] = true;
        } elseif( $this->update ) {
            $this->update = preg_replace( '/^UPDATE /', '', $this->update );
            $this->join = array( 'collections' => array( $this->update, $join, $collection ) );
            $this->collections[] = $collection;
            $this->join_meta_data[$collection] = true;
        }
        $this->set_query_order( 'join' );
        return $this;
    }
    
    public function on( array $join_attributes ) {
        $this->join['attributes'] = $join_attributes;
        $this->process_join_meta_data( $join_attributes, 'on' );
        if( $this->select ) {
            $this->from = 'FROM '.$this->get_join_clause( $this->join );
        } elseif( $this->delete ) {
            $this->from = 'FROM '.$this->get_join_clause( $this->join );
        } elseif( $this->update ) {
            $this->update = 'UPDATE '.$this->get_join_clause( $this->join );
        }
        $this->set_query_order( 'on' );
        return $this;
    }
    
    public function process_join_meta_data( array $join_attributes, $call_type ) {
        if ( is_array( $join_attributes[0] ) ) {
            $first_join_attributes = array();
            $second_join_attributes = array();
            foreach( $join_attributes as $index => $attribute_array ) {
                if ( 1 == preg_match( '/(and|or)/', $attribute_array[0] ) ) array_shift( $attribute_array );
                if ( ! $this->value_is_a_collection_attribute( $attribute_array[2] ) ) continue;
                if ( ! $this->is_join_attribute_in_join_meta_data( $attribute_array[0] ) ) {
                    $first_join_attributes[] = $attribute_array[0];
                    if ( 1 == preg_match( '/\..*$/',  $attribute_array[0] ) ) {
                        $first_join_collection = preg_replace( '/\..*$/', '', $attribute_array[0] );
                    } elseif ( 'on' == $call_type || ( 'logical' == $call_type && $this->is_from_join ) ) {
                        ajaxmvc_core_exception::throw_error( $this, 'you must explicitly identify collection in on() statement, i.e. collection.attribute' );
                    }
                }
                if ( ! $this->is_join_attribute_in_join_meta_data( $attribute_array[2] ) ) {
                    $second_join_attributes[] = $attribute_array[2];
                    if ( 1 == preg_match( '/\..*$/',  $attribute_array[2] ) ) {
                        $second_join_collection = preg_replace( '/\..*$/', '', $attribute_array[2] );
                    } elseif ( 'on' == $call_type || ( 'logical' == $call_type && $this->is_from_join ) ) {
                        ajaxmvc_core_exception::throw_error( $this, 'you must explicitly identify collection in on() statement, i.e. collection.attribute' );
                    }
                }
            }
            $this->join_meta_data[$first_join_collection] = $first_join_attributes;
            $this->join_meta_data[$second_join_collection] = $second_join_attributes;
        } else {
            if ( 1 == preg_match( '/(and|or)/', $join_attributes[0] ) ) array_shift( $join_attributes );
            if ( ! $this->value_is_a_collection_attribute( $join_attributes[2] ) ) return;
            if ( ! $this->is_join_attribute_in_join_meta_data( $join_attributes[0] ) ) {
                if ( 1 == preg_match( '/\..*$/',  $join_attributes[0] ) ) {
                    $first_join_collection = preg_replace( '/\..*$/', '', $join_attributes[0] );
                } elseif ( 'on' == $call_type || ( 'logical' == $call_type && $this->is_from_join ) ) {
                    ajaxmvc_core_exception::throw_error( $this, 'you must explicitly identify collection in on() statement, i.e. collection.attribute' );
                }
                $this->join_meta_data[$first_join_collection ] = array( $join_attributes[0] );
            }
            if ( ! $this->is_join_attribute_in_join_meta_data( $join_attributes[2] ) ) {
                if ( 1 == preg_match( '/\..*$/',  $join_attributes[2] ) ) {
                    $second_join_collection = preg_replace( '/\..*$/', '', $join_attributes[2] );
                } elseif ( 'on' == $call_type || ( 'logical' == $call_type && $this->is_from_join ) ) {
                    ajaxmvc_core_exception::throw_error( $this, 'you must explicitly identify collection in on() statement, i.e. collection.attribute' );
                }
                $this->join_meta_data[$second_join_collection] = array( $join_attributes[2] );
            }
        }
    }
    
    public function is_join_attribute_in_join_meta_data( $string ) {
        if( ! is_array( $this->join_meta_data ) ) return false;
        foreach( $this->join_meta_data as $index => $join_attribute_array ) {
            if ( ! is_array( $join_attribute_array ) ) continue;
            if ( false !== array_search( $string, $join_attribute_array ) ) {
                return true;
            }
        }  
        return false;
    }
    
    public function where( array $conditions, $inclusive = null, $nesting = null, $strip_nest = null ) {
        $conditions = $this->process_logical_conditions( $conditions, 'where' );
        if( $conditions == '' ) {
            return $this;
        }
        $this->get_where_clause( 'where', $conditions, $inclusive, $nesting, $strip_nest );
        $this->set_query_order( 'where' );
        return $this;
    }
    
    public function group_by( array $attributes ) {
        $this->group_by = $this->get_group_by_clause( $attributes );
        $this->set_query_order( 'group_by' );
        return $this;
    }
    
    public function having( array $conditions, $inclusive = null, $nesting = null, $strip_nest = null ) {
        $conditions = $this->process_logical_conditions( $conditions, 'having' );
        if( $conditions == '' ) {
            return $this;
        }
        $this->get_having_clause( 'having', $conditions, $inclusive, $nesting, $strip_nest );
        $this->set_query_order( 'having' );
        return $this;
    }
    
    public function order_by( array $attributes ) {
        $this->order_by = $this->get_order_by_clause( $attributes );
        $this->set_query_order( 'order_by' );
        return $this;
    }
    
    public function limit( array $conditions ) {
        if ( 2 != count( $conditions ) ) {
            ajaxmvc_core_exception::throw_error( $this,  "an array of two arguments: offset, and limit is required for this method." );
        }
        $this->limit = $this->get_limit_clause( $conditions );
        $this->set_query_order( 'limit' );
        return $this;
    }
    
    public function _and_( array $conditions, $inclusive = null, $nesting = null, $strip_nest = null ) {
        $conditions = $this->process_logical_conditions( $conditions, 'and' );
        if( $conditions == '' ) {
            return $this;
        }
        if ( preg_match( '/^(where)$/', $this->last_query_clause ) ) {
            $this->get_and_condition( 'where', $conditions, $inclusive, $nesting, $strip_nest );
        } elseif ( preg_match( '/^(having)$/', $this->last_query_clause ) ) {
            $this->get_and_condition( 'having', $conditions, $inclusive, $nesting, $strip_nest );
        }
        return $this;
    }
    
    public function _or_( array $conditions, $inclusive = null, $nesting = null, $strip_nest = null ) {
        $conditions = $this->process_logical_conditions( $conditions, 'or' );
        if( $conditions == '' ) {
            return $this;
        }
        if ( preg_match( '/^(where)$/', $this->last_query_clause ) ) {
            $this->get_or_condition( 'where', $conditions, $inclusive, $nesting, $strip_nest );
        } elseif ( preg_match( '/^(having)$/', $this->last_query_clause ) ) {
            $this->get_or_condition( 'having', $conditions, $inclusive, $nesting, $strip_nest );
        }
        return $this;
    }
    
    /**
     * this method must only be called internally
     * if load is called internally, prooperties that have saved 
     * closures will be executed, which we only want fired when an
     * ORM method is called
     */
    public function exec( $sql_dump = null ) {
        return $this->get_sql_object_chain_results( $sql_dump );
    }
}