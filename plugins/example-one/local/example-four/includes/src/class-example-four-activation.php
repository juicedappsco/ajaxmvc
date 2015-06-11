<?php
class example_four_activation extends ajaxmvc_core_object_factory{
    //this module loads its own data as well
    public function example_four_activate(){
        ( new example_four_model() )->load_data();
    }
}