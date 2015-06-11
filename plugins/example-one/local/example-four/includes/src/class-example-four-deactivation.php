<?php
class example_four_deactivation extends ajaxmvc_core_object_factory{
    //deconstruct the physical model
    public function example_four_deactivate(){
        ( new example_four_model() )->create_logical();
    }
}