<?php
class example_two_model extends ajaxmvc_core_model {
    
    //identify primary key
    public $primary_key = 'example_two_id';
    
    //identify fillable attributes
    public $fillable = array(
            'example_one_frgn_ky',
            'example_two_integer',
            'example_two_varchar',
            'example_two_decimal',
            'example_two_boolean'
    );
    
    //identify foreign key
    public $foreign_key = array(
        'foreign_key_example_one_id_example_one_integer'  => array(
            'example_one_frgn_ky',
            'references',
            'example_one_model.example_one_id',
        ),
    );
    
    //identify specific types
    public $physical_type = array(
        'example_two_integer'       => 'int(10)',
        'example_two_varchar'       => 'varchar(100)',
        'example_two_decimal'       => 'float(12,6)',
        'example_two_boolean'       => 'int(1)',
    );
    
    //load some data
    function load_data(){
       $this->save(
            array(
                array(
                        $this->primary_key      => 1,
                        'example_one_frgn_ky'   => 1,
                        'example_two_integer'   => 1,
                        'example_two_varchar'   => 'one',
                        'example_two_decimal'   => 11.11,
                        'example_two_boolean'   => true
                ),
                array(
                        $this->primary_key      => 2,
                        'example_one_frgn_ky'   => 1,
                        'example_two_integer'   => 2,
                        'example_two_varchar'   => 'two',
                        'example_two_decimal'   => 22.22,
                        'example_two_boolean'   => false
                ),
                array(
                        $this->primary_key      => 3,
                        'example_one_frgn_ky'   => 2,
                        'example_two_integer'   => 3,
                        'example_two_varchar'   => 'three',
                        'example_two_decimal'   => 33.33,
                        'example_two_boolean'   => true
                ),
                array(
                        $this->primary_key      => 4,
                        'example_one_frgn_ky'   => 2,
                        'example_two_integer'   => 4,
                        'example_two_varchar'   => 'four',
                        'example_two_decimal'   => 44.44,
                        'example_two_boolean'   => false
                ),
                array(
                        $this->primary_key      => 5,
                        'example_one_frgn_ky'   => 2,
                        'example_two_integer'   => 5,
                        'example_two_varchar'   => 'five',
                        'example_two_decimal'   => 55.55,
                        'example_two_boolean'   => true
                ),
            )
        )->load();
        
        //create physical model
        $this->create_physical();
    }
}