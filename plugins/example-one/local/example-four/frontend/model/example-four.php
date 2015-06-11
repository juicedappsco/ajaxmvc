<?php
class example_four_model extends ajaxmvc_core_model {

    //no primary key property so key will be id
    
    //identify fillable attributes
    public $fillable = array(
            'example_four_integer',
            'example_four_varchar',
            'example_four_decimal',
            'example_four_boolean'
    );
    
    //load some data
    function load_data(){
        $this->save(
            array(
                array(
                        'id'                    => 1,
                        'example_four_integer'   => 1,
                        'example_four_varchar'   => 'one',
                        'example_four_decimal'   => 11.11,
                        'example_four_boolean'   => true
                ),
                array(
                        'id'                    => 2,
                        'example_four_integer'   => 2,
                        'example_four_varchar'   => 'two',
                        'example_four_decimal'   => 22.22,
                        'example_four_boolean'   => false
                ),
                array(
                        'id'                    => 3,
                        'example_four_integer'   => 3,
                        'example_four_varchar'   => 'three',
                        'example_four_decimal'   => 33.33,
                        'example_four_boolean'   => true
                ),
                array(
                        'id'                    => 4,
                        'example_four_integer'   => 4,
                        'example_four_varchar'   => 'four',
                        'example_four_decimal'   => 44.44,
                        'example_four_boolean'   => false
                ),
                array(
                        'id'                    => 5,
                        'example_four_integer'   => 5,
                        'example_four_varchar'   => 'five',
                        'example_four_decimal'   => 55.55,
                        'example_four_boolean'   => true
                ),
            )
        )->load();
        
    //create physical representation of the model
    $this->create_physical();
    }
}