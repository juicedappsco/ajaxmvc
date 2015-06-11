<?php
class example_one_model extends ajaxmvc_core_model {
    
    //identify primary key
    public $primary_key = 'example_one_id';
    
    /**
     * properties attributes need to be explicitly defined
     * in order to use them to save or destroy
     */
    public $example_one_id;
    public $example_one_integer;
    
    //identify fillable attributes
    public $fillable = array(
        'example_one_integer',
        'example_one_varchar',
        'example_one_decimal',
        'example_one_boolean',
    );
    
    //identify specific types
    public $physical_type = array(
        'example_one_integer'       => 'int(10)',
        'example_one_varchar'       => 'varchar(100)',
        'example_one_decimal'       => 'float(12,5)',
        'example_one_boolean'       => 'int(1)',
    );
    
    //create an index for the model
    public $index = array(
        'index_example_one_id_example_one_integer'  => array(
            'example_one_id',
            'example_one_integer'
        ),
    );
    
    //create a unque key for the model
    public $unique_key = array(
        'unique_key_example_one_id_example_one_integer'  => array(
            'example_one_id',
            'example_one_integer'
        ),
    );
    
    /**
     * this method is called from the plugin activation method
     */
    function load_data(){
        
        //load this models data
        $this
        ->save(
            array(
                array(
                    $this->primary_key      => 1,
                    'example_one_integer'   => 1,
                    'example_one_varchar'   => 'one',
                    'example_one_decimal'   => 11.11,
                    'example_one_boolean'   => true
                ),
                array(
                    $this->primary_key      => 2,
                    'example_one_integer'   => 2,
                    'example_one_varchar'   => 'two',
                    'example_one_decimal'   => 22.22,
                    'example_one_boolean'   => false
                ),
                array(
                    $this->primary_key      => 3,
                    'example_one_integer'   => 3,
                    'example_one_varchar'   => 'three',
                    'example_one_decimal'   => 33.33,
                    'example_one_boolean'   => true
                ),
                array(
                    $this->primary_key      => 4,
                    'example_one_integer'   => 4,
                    'example_one_varchar'   => 'four',
                    'example_one_decimal'   => 44.44,
                    'example_one_boolean'   => false
                ),
                array(
                    $this->primary_key      => 5,
                    'example_one_integer'   => 5,
                    'example_one_varchar'   => 'five',
                    'example_one_decimal'   => 55.55,
                    'example_one_boolean'   => true
                ),
            )
        )->load();
        
        //create a physical instance of the model
        $this->create_physical();
    }
    
    function example_one_results() {
             
        //load all records of this model     
        $result = $this->get()->load();
        
        //get with a where, all of the sql object query methods may be called on get() as well
        //including but not limited to: join,where,having,group by etc. etc. (see below)
        $result = $this->get()->where( array( 'example_one_varchar', 'LIKE', '%ee*' ) )->load();
        
        //load all records of another model
        $result = ( new example_two_model() )->get()->load();
        
        //save one attribute of this model
        $this->example_one_id = 37;
        $this->example_one_integer = 37;
        $this->save()->load();
        //this must be unset
        unset( $this->example_one_integer );
        
        //save one attribute of this model
        $result = $this->save( 'example_one_decimal', 7.77, 12985 )->load();
        
        //save one record to this model
        $this->save(
            array(
                $this->primary_key      => 35,
                'example_one_integer'   => 5000,
                'example_one_varchar'   => 'five thousand',
                'example_one_decimal'   => 5555.5555,
                'example_one_boolean'   => true
            )
        )->load();
        
        //save multiple records to this model
        $this->save( 
            array(
                array(
                    $this->primary_key      => 548,
                    'example_one_integer'   => 200, 
                    'example_one_varchar'   => 'two hundred', 
                    'example_one_decimal'   => 34.34, 
                    'example_one_boolean'   => true
                ),
                array(
                    $this->primary_key      => 578,
                    'example_one_integer'   => 1111,
                    'example_one_varchar'   => 'twenty million five',
                    'example_one_decimal'   => 54.54,
                    'example_one_boolean'   => false
                )
            )
        )->load();
        
        //delete one attribute of this model
        $this->example_one_id = 5;
        $this->destroy()->load();
        
        //delete one attribute of this model
        $this->destroy(2)->load();
        
        /**
         * IMPORTANT!!!
         *
         * PHYSICAL STATE is required for the following methods or an exception will be thrown:
         *         delete(), update(), insert()
         *
         * developers are encouraged to use the following methods for the below illustrated operations
         * they are valid in either a logical or physical state:
         *        destroy(), save()
         * 
         * PHYSICAL state is HIGHLY recommended for production
         * LOGICAL state is recommended for early development, once you are confident in the integrity
         * of your model it is encouraged to convert to PHYSICAL state in late stages of development
         * 
         * that is the point of this framework, the advantadges and the flexibility of EAV in development
         * and the advantadges of Traditional DB Schema in production, the framework can easily
         * transition between both states, any modifications made to the physical schema will be retained when
         * moving to logical state, including types, keys etc. etc., all data will be retained as well
         * 
         * if you must you may transition between physical state and logical state in order to use
         * these methods in development
         *        $this->create_logical();
         *        $this->create_physical();
         */
        //delete everything from model one based on join with model two
        $this->delete( array('ajaxmvc_ajaxmvc_example_one_model') )
             ->from( 'ajaxmvc_ajaxmvc_example_one_model' )
             ->join( 'ajaxmvc_ajaxmvc_example_two_model', $join_type = null )
             ->on( array( 'ajaxmvc_ajaxmvc_example_one_model.'.$this->primary_key, '=', 'ajaxmvc_ajaxmvc_example_two_model.example_two_id' ) )
             ->load();
         
        //insert some data into model one based on select from example three
        $this->insert_into( 
                'ajaxmvc_ajaxmvc_example_one_model', 
                array( $this->primary_key,'example_one_integer', 'example_one_varchar', 'example_one_decimal', 'example_one_boolean' ) 
             )
             ->select(array('*'))
             ->from('ajaxmvc_ajaxmvc_example_three_model')
             ->load();
             
        //update model one based on join with model two
        $this->update('ajaxmvc_ajaxmvc_example_one_model')
             ->join( 'ajaxmvc_ajaxmvc_example_two_model', $join_type = null )
             ->on( array( 'ajaxmvc_ajaxmvc_example_one_model.'.$this->primary_key, '=', 'ajaxmvc_ajaxmvc_example_two_model.example_two_id' ) )
             ->set( array( 'example_one_integer' => 14, 'example_one_varchar' => 'hi' ) )
             ->where( array( $this->primary_key, '=', 27 ) )
             ->load();
         
         //clear record before inserting
         $this->destroy(7)->load();
         //insert one record into model one
         $this->insert_into( 
                'ajaxmvc_ajaxmvc_example_one_model', 
                array( $this->primary_key,'example_one_integer', 'example_one_varchar', 'example_one_decimal', 'example_one_boolean' ) 
             )
             ->values( array( 7, 2500, '2.00', 1.00, false ) )
             ->load();
         
         //clear record before inserting
         $this->destroy(1235678)->load();
         $this->destroy(15854)->load();
         //insert multiple records into model one
         $this->insert_into( 
                'ajaxmvc_ajaxmvc_example_one_model', 
                array( $this->primary_key,'example_one_integer', 'example_one_varchar', 'example_one_decimal', 'example_one_boolean' ) 
             )
             ->values( array(array( 1235678, 2500, 'ten', 1.2200, false ), array( 15854,2535, 'nine', 999.99, false )))
             ->load();
        
        /**
         * I realize this query is ridiculous but it is written in order to show
         * the versatility and that the syntactical integrity of the SQL language
         * is preserved when using the sql object chain methods
         */
        $result = $this->select( 
            array(
                    'ajaxmvc_ajaxmvc_collection_entity.entity_id',
                    'COUNT(*) as count',
                    'ajaxmvc_ajaxmvc_collection_attribute.attribute',
                    'ajaxmvc_ajaxmvc_collection_entity_attribute.value'
                ) 
            )
            ->from( 'ajaxmvc_ajaxmvc_collection' )
                ->join( 'ajaxmvc_ajaxmvc_collection_entity', $join_type = null )
                    ->on( array( array( 'ajaxmvc_ajaxmvc_collection.collection_id', '=', 'ajaxmvc_ajaxmvc_collection_entity.collection_id' ), array( 'ajaxmvc_ajaxmvc_collection.collection_id', '=', 'ajaxmvc_ajaxmvc_collection_entity.collection_id' ) ) )
                        ->join( 'ajaxmvc_ajaxmvc_collection_entity_attribute', $join_type = null )
                            ->on( array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '=', 'ajaxmvc_ajaxmvc_collection_entity_attribute.entity_id' ) )
                                ->join( 'ajaxmvc_ajaxmvc_collection_attribute', $join_type = null )
                                    ->on( array( 'ajaxmvc_ajaxmvc_collection_entity_attribute.attribute_id', '=', 'ajaxmvc_ajaxmvc_collection_attribute.attribute_id' ) )
            ->where(
                array( 
                    array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'a' ),
                    array( 'and','ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'b' ),
                    array( 'and' =>  
                        array(
                            array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'c' ),
                            array( 'and', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'd' ),
                            array( 'and' => 
                                array(
                                    array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'e' ),
                                    array( 'or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'f' ),
                                    array( 'or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'g' ),
                                    array( 'and' =>
                                        array(
                                            array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'h' ),
                                            array( 'or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'i' ),
                                            array( 'and' =>
                                                array(
                                                    array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'j' ),
                                                    array( 'or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'k' ),
                                                )
                                            ),
                                            array( 'or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'HIJK' )
                                        )
                                    )
                                )
                            ),
                            array( 'or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'lopper' ),
                            array( 'or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'scooter' )
                        )
                    ),
                    array( 'or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'lautner' ),
                    array( 'or' =>
                        array(
                            array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'm' ),
                            array( 'or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'n' ),
                            array( 'or' =>
                                array(
                                    array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'o' ),
                                    array( 'or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'p' ),
                                    array( 'or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'q' ),
                                    array( 'or' =>
                                        array(
                                            array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'r' ),
                                            array( 'or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 's' ),
                                            array( 'or' =>
                                                array(
                                                    array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 't' ),
                                                    array( 'or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'u' ),
                                                )
                                            )
                                        )
                                    ),
                                )
                            )
                        )
                    )
                )
            )
            ->_or_( array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'v' ) )
            ->_and_( array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'v' ) )
            ->_and_( 
                array(
                    array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'w' ),
                    array( 'or',  'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'x' ),
                    array( 'and', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'y' ),
                    array( 'or'=>
                        array(
                            array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'z' ),
                            array('or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'a' )
                        )
                    ),
                    array('or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'def' ),
                    array('and', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'def' ),
                    array( 'or'=>
                        array(
                            array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'zww' ),
                            array('or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'aww' )
                        )
                    ),
                    array( 'or',  'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'nacho' ),
                    array( 'and', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'libre' ),
                ) 
            )            
            ->_and_( array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'z' ) )
            ->group_by( array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id' ) )
            ->having(
                array(
                    array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'a' ),
                    array( 'and'=> 
                        array( 
                            array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'b' ) 
                        )
                    ),
                    array( 'and' =>
                        array(
                            array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'c' ),
                            array( 'and', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'd' ),
                            array( 'or' =>
                                array(
                                    array( 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'e' ),
                                    array( 'or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'f' )
                                )
                            )
                        )
                    )
                )
            )
            ->_and_(
                array(
                    array(  'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'g' ),
                    array( 'or', 'ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'h' ),
                )
            )
            ->order_by( 
                array(
                    'ajaxmvc_ajaxmvc_collection_entity.entity_id',
                    'ajaxmvc_ajaxmvc_collection_entity_attribute.attribute_id',
                    'ajaxmvc_ajaxmvc_collection_entity_attribute.value'
                )         
            )
            ->limit( array( '0', '100' ) )
            ->load( $sql_dump = 0 );
            
        //load model one
        $result = $this->get()->order_by(array('example_one_id'))->load();
        return $result;
    }
}