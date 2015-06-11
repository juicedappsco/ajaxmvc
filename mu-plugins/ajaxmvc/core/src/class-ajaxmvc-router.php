<?php
/**
 * Ajax MVC Router Class
 *
 * Hooks into ajax actions and routes the Ajax call to the appropriate controller.
 *
 * @package WordPress
 * @subpackage Database
 * @since 0.71
 */
class ajaxmvc_core_router extends ajaxmvc_core_object_factory{

    /**
     * Prepares the request object and routes request via core route method.
     *
     * This method is bound to the wp_ajax and wp_ajax_nopriv actions and is executed on ajax call,
     * it sanitizes the request object and calls core route method.
     *
     * @since 1.0.0
     */
    public function route_request() {
        $request_uri = trim( preg_replace( '/^\//', '', $_SERVER['REQUEST_URI'] ) );
        if ( preg_match( '/ajax.php$/', $request_uri ) == 1 ) {
            try {
                $request = $this->verify_and_prepare_request( $_REQUEST );
                $this->route( $request );
                die();
            } catch ( Exception $e ) {
                if ( AM_ERRORS_VERBOSE == true ) {
                    echo "<p><strong>Fatal error</strong>: {$e->getMessage()}</p>";
                    die();
                }
            }
        } 
        die();
    }
    
    /**
     * Verify and prepare the request object for routing.
     *
     * @since 1.0.0
     *
     * @param array $request $_REQUEST object.
     * @return array Sanitized $_REQUEST object.
     */
    public function verify_and_prepare_request( array $request ) {
        /*
         * Throw Exceptions if nonce is invalid or
         * any of the following are missing from the $request array:
         * namespace,module,method,or response.
         */
        if ( ! wp_verify_nonce( $request['nonce'], 'ajaxmvc-nonce' ) ) ajaxmvc_core_exception::throw_error( $this, 'invalid nonce' );
        if ( ! $request['plugin'] )         ajaxmvc_core_exception::throw_error( $this, 'plugin must be specified' );
        if ( ! $request['namespace'] )      ajaxmvc_core_exception::throw_error( $this, 'namespace must be specified' );
        if ( ! $request['module'] )         ajaxmvc_core_exception::throw_error( $this, 'module must be specified' );
        if ( ! $request['instance'] )       ajaxmvc_core_exception::throw_error( $this, 'instance must be specified' );
        if ( ! $request['method'] )         ajaxmvc_core_exception::throw_error( $this, 'method must be specified' );
        if ( ! $request['response'] )       ajaxmvc_core_exception::throw_error( $this, 'response must be specified' );
        $this->sanitize_request( $request );
        $this->set_response_header( $request['response'] );
        return $request;
    }

    /**
     * Sanitize the $request object.
     * 
     * Removes any slashes potentially added by the deprecated magic quotes or
     * any custom php.ini directives, and then re-escape them for security purposes.
     * 
     * @link Example #2 http://php.net/manual/en/security.magicquotes.disabling.php
     *
     * @since 1.0.0
     *
     * @param array reference $request Array of request parameters.
     */
    public function sanitize_request( array &$request ) {
        $this->sanitize_to_dashes( $request );
        $process = array( &$request );
        while ( list( $key, $val ) = each( $process ) ) {
            foreach ( $val as $k => $v ) {
                unset( $process[$key][$k] );
                if ( is_array( $v ) ) {
                    $process[$key][addslashes( stripslashes( $k ) )] = $v;
                    $process[] = &$process[$key][addslashes( stripslashes( $k ) )];
                } else {
                    $process[$key][addslashes( stripslashes( $k ) )] = addslashes( stripslashes( $v ) );
                }
            }
        }
        unset( $process );
    }
    
    /**
     * Set the response header.
     * 
     * Response must be explicity specified in $_REQUEST['response'].
     *
     * @since 1.0.0
     */
    public function set_response_header( $response ) {
        $response = strtolower( $response );
        switch ( $response ) {
            case 'html'    : return header( 'Content-Type: text/html' );
            case 'xml'     : return header( 'Content-Type: application/xml' );
            case 'json'    : return header( 'Content-Type: application/json' );
            default        : ajaxmvc_core_exception::throw_error( $this, 'response must be specified as: html,xml, or json' );
        }
    }

    /**
     * Route the request.
     *
     * Binds a model and view to a newly instantiated controller for a given ajax request.
     *
     * @since 1.0.0
     */
    public function route( array $request ) {
        // Instantiate controller for the given request
        $ajaxmvc_core_controller = new ajaxmvc_core_controller();
        $controller = $ajaxmvc_core_controller->create_controller_object( $request );
        // Bind a model and view to this controller
        $ajaxmvc_core_model = new ajaxmvc_core_model();
        $controller->model = $ajaxmvc_core_model->create_model_object( $request );
        $controller->view  = new ajaxmvc_core_view();
        // Execute the controller and capture its output
        $output = $controller->{$request['method']}( $request['parameters'] );
        /*
         * If the render() method is called in a given instantiated controller it will 
         * then set its rendered property to true and the method is exited, 
         * we then check for this boolean property and if rendered is set to true, we re-execute the
         * render method with a injection parameter of model, which allows for the rest of the render method to be
         * executed, else we echo the captured output of the given controller.
         */
        echo ( true === $controller->view->rendered ) ? $controller->view->render( $controller->model ) : $output;
    }
}