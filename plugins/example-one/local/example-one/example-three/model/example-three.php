<?php
class example_three_model extends ajaxmvc_core_model {
    
    //identify primary key
    public $primary_key = 'example_three_id';
    
    //identify fill,able attributes
    public $fillable = array(
            'example_three_integer',
            'example_three_varchar',
            'example_three_decimal',
            'example_three_boolean'
    );
    
    //load some data
    function load_data(){
        $this->save(
            array(
                array(
                        'example_three_id'        => 1,
                        'example_three_integer'   => 1,
                        'example_three_varchar'   => 'one',
                        'example_three_decimal'   => 11.11,
                        'example_three_boolean'   => true
                ),
                array(
                        'example_three_id'        => 2,
                        'example_three_integer'   => 2,
                        'example_three_varchar'   => 'two',
                        'example_three_decimal'   => 22.22,
                        'example_three_boolean'   => false
                ),
                array(
                        'example_three_id'        => 3,
                        'example_three_integer'   => 3,
                        'example_three_varchar'   => 'three',
                        'example_three_decimal'   => 33.33,
                        'example_three_boolean'   => true
                ),
                array(
                        'example_three_id'        => 4,
                        'example_three_integer'   => 4,
                        'example_three_varchar'   => 'four',
                        'example_three_decimal'   => 44.44,
                        'example_three_boolean'   => false
                ),
                array(
                        'example_three_id'        => 5,
                        'example_three_integer'   => 5,
                        'example_three_varchar'   => 'five',
                        'example_three_decimal'   => 55.55,
                        'example_three_boolean'   => true
                ),
            )
        )->load();
        
        //create physical representation of the model
        $this->create_physical();
    }
}