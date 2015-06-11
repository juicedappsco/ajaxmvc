<?php
class example_one_activation extends ajaxmvc_core_object_factory{
    //load the models data
    public function example_one_activate(){
        ( new example_one_model() )->load_data();
        /**
         * Arbitrarily loading data of the other models,
         * written to illustrate all models are available
         */
        ( new example_two_model() )->load_data();
        ( new example_three_model() )->load_data();
    }
}