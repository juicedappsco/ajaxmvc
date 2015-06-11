<?php
class example_one_controller extends ajaxmvc_core_controller{

    public function example_one_controller_action( $parameters ){
        
        //generate result set
        $this->view->result_set = $this->model->example_one_results();
        
        //set template
        $this->view->template = array(
                AM_EXAMPLE_ONE_MODULE_PATH.'example-one/frontend/view/html/example-one.php'
        );
        
        //render view
        $this->view->render();
        
        return ;
    }
}