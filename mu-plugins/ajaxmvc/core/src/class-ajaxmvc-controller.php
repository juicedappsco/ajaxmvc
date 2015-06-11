<?php
/**
 * Ajax MVC Core Controller Class.
 * 
 * Responsible for instantiating a given namespace module's controller class.
 * 
 * @since 1.0.0
 */
class ajaxmvc_core_controller extends ajaxmvc_core_object_factory {
    
    /**
     * Instantiates and returns a given namespace module's controller class.
     * 
     * Receives the request parameter array from the router,
     * sanitizes the instance parameter, creates the controller
     * object via parent method and then returns it to the router.
     *
     * @since 1.0.0
     *
     * @param  array $request Request parameters passed in from router.
     * @return controller Class of given namespace module's controller.
     */
    public function create_controller_object( array $request ) {
        $controller_class = $this->sanitize_to_underscore( $request['instance'] ).'_controller';
        return $this->create_class_object( $controller_class, $request );
    }
}