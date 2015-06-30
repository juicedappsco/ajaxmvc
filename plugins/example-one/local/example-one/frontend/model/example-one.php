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
        'example_one_decimal'       => 'float(12,6)',
        'example_one_boolean'       => 'int(1)',
    );
    
    //identify an index for the model
    public $index = array(
        'index_example_one_id_example_one_integer'  => array(
            'example_one_id',
            'example_one_integer'
        ),
    );
    
    //identify a unique key for the model
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
        $this->save(
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
        $this->example_one_integer = 99;
        $this->save()->load();
        
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
        
        //change state of this model to logical
        $this->create_logical();
         
        //delete everything from model one based on FROM join with model two
        $this->delete( array('ajaxmvc_ajaxmvc_example_one_model') )
             ->from(array('ajaxmvc_ajaxmvc_example_one_model','ajaxmvc_ajaxmvc_example_two_model'))
              ->where(array( 'ajaxmvc_ajaxmvc_example_one_model.'.$this->primary_key, '=', 'ajaxmvc_ajaxmvc_example_two_model.example_two_id' ))
              ->load();
        
        //insert some data into model one based on select with join
        $this->insert_into( 
                'ajaxmvc_ajaxmvc_example_one_model', 
                array( $this->primary_key,'example_one_integer', 'example_one_varchar', 'example_one_decimal', 'example_one_boolean' ) 
             )
             ->select(array('example_two_id',
                            'example_two_integer',
                            'example_two_varchar',
                            'example_two_decimal',
                            'example_two_boolean'))
             ->from('ajaxmvc_ajaxmvc_example_two_model')
             ->join( 'ajaxmvc_ajaxmvc_example_three_model', $join_type = null )
             ->on( array( 'ajaxmvc_ajaxmvc_example_two_model.example_two_id', '=', 'ajaxmvc_ajaxmvc_example_three_model.example_three_id' ) )
             ->load();
         
         //clear records before inserting them
         $this->destroy(786)->load();
         $this->destroy(888)->load();
         
         //insert some random values
         $this->insert_into( 
                'ajaxmvc_ajaxmvc_example_one_model', 
                array( 'example_one_id','example_one_integer', 'example_one_varchar', 'example_one_decimal', 'example_one_boolean' ) 
            )
            ->values( array(array( 786, 2500, 'ten', 1.2200, false ), array( 888,2535, 'nine', 999.99, false )))->load();

         //update some records based on a join
         $result = $this->update('ajaxmvc_ajaxmvc_example_one_model')
             ->join( 'ajaxmvc_ajaxmvc_example_two_model', $join_type = null )
             ->on( array( array( 'ajaxmvc_ajaxmvc_example_one_model.'.$this->primary_key, '=', 'ajaxmvc_ajaxmvc_example_two_model.example_two_id' ), array( 'ajaxmvc_ajaxmvc_example_one_model.example_one_decimal', '=', 'ajaxmvc_ajaxmvc_example_two_model.example_two_decimal' ) ) )
             ->join( 'ajaxmvc_ajaxmvc_example_three_model', $join_type = null )
             ->on( array( 'ajaxmvc_ajaxmvc_example_two_model.example_two_id', '=', 'ajaxmvc_ajaxmvc_example_three_model.example_three_id' ) )
             ->set( array( 'example_one_integer' => 46, 'example_one_varchar' => 'hoot', 'example_three_integer' => 123, 'example_two_integer' => 456 ) )
             ->load();
             
         //update some more records
         $result = $this->update('ajaxmvc_ajaxmvc_example_one_model')
             ->set( array( 'example_one_integer' => 32, 'example_one_varchar' => 'scoot', ) )
             ->where( array( 'ajaxmvc_ajaxmvc_example_one_model.'.$this->primary_key, '<', 4 ) )
             ->load();

         //update some more records based on a FROM join
         $result = $this->update(array('ajaxmvc_ajaxmvc_example_one_model','ajaxmvc_ajaxmvc_example_two_model','ajaxmvc_ajaxmvc_example_three_model'))
              ->set( array( 'example_one_integer' => 97, 'example_one_varchar' => 'hi', 'example_three_integer' => 200, 'example_two_integer' => 500 ) )
              ->where( array( array( 'ajaxmvc_ajaxmvc_example_one_model.'.$this->primary_key, '=', 'ajaxmvc_ajaxmvc_example_two_model.example_two_id' ), array( 'and', 'ajaxmvc_ajaxmvc_example_one_model.example_one_decimal', '=', 'ajaxmvc_ajaxmvc_example_two_model.example_two_decimal' ) ) )
              ->_and_(array( 'ajaxmvc_ajaxmvc_example_two_model.example_two_id', '=', 'ajaxmvc_ajaxmvc_example_three_model.example_three_id' ))
              ->_and_( array( 'ajaxmvc_ajaxmvc_example_one_model.'.$this->primary_key, '<', 3 ) )
              ->load();
              
         //run an arbitrary select statement
         $result = $this->select(array('*'))
              ->from('ajaxmvc_ajaxmvc_example_one_model')
             ->join( 'ajaxmvc_ajaxmvc_example_two_model', $join_type = null )
             ->on( array( array( 'ajaxmvc_ajaxmvc_example_one_model.'.$this->primary_key, '=', 'ajaxmvc_ajaxmvc_example_two_model.example_two_id' ), array( 'ajaxmvc_ajaxmvc_example_one_model.example_one_decimal', '=', 'ajaxmvc_ajaxmvc_example_two_model.example_two_decimal' ) ) )
             ->join( 'ajaxmvc_ajaxmvc_example_three_model', $join_type = null )
             ->on( array( 'ajaxmvc_ajaxmvc_example_two_model.example_two_id', '=', 'ajaxmvc_ajaxmvc_example_three_model.example_three_id' ) )
             ->load();

        /**
         * I realize this query is ridiculous but it is written in order to show
         * the versatility and that the syntactical integrity of the SQL language
         * is preserved when using the sql object chain methods
         */
        $this->print_results( $this->select( 
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
                    array( 'and','ajaxmvc_ajaxmvc_collection_entity.entity_id', '!=', 'b' ),
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
            ->load( $sql_dump = 0 ) );
        
        //load model one and print
        $results = ( new example_one_model() )->get()->order_by([( new example_one_model() )->primary_key])->load();
        $this->print_results($results);
        
        //return result
        return $result;
    }
}    