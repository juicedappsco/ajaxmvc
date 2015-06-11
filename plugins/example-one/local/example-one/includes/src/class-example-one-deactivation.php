<?php
class example_one_deactivation extends ajaxmvc_core_object_factory{
    //deconstruct the physical models
    public function example_one_deactivate(){
        ( new example_one_model() )->create_logical();
        ( new example_two_model() )->create_logical();
        ( new example_three_model() )->create_logical();
    }
}