<?php
/**
 * Ajax MVC Core View Class.
 *
 * Responsible for instantiating a given namespace module's view.
 *
 * @since 1.0.0
 */
class ajaxmvc_core_view extends ajaxmvc_core_object_factory {

    /**
     * Instantiates and returns a given namespace module's view.
     * 
     * The view may or may not consist of multiple template files and or content
     * represented as a string.
     *
     * @since 1.0.0
     *
     * @param  object $injection Optional. Used for dependency injection in core route method.
     * @return Output buffer consisting of multiple template files and or content.
     */
    public function render( $injection = null ) {
        /*
         * If a parameter is passed to this method
         * it must be of type object and a descendant of
         * ajaxmvc_core_model.
         */
        if ( null != $injection ) {
            if ( is_object( $injection ) == false ) {
                ajaxmvc_core_exception::throw_error( $this, "$injection: is not an object" );
            } elseif ( preg_match( '/^ajaxmvc_core_model$/', get_parent_class( $injection ) ) != 1 ) {
                $class = get_class( $injection );
                ajaxmvc_core_exception::throw_error( $this, "$class: is not a descendant of ajaxmvc_core_model" );
            }
        }
        /*
         * We need to inject the model into the view.
         * If render is called from within a given controller with
         * no arguments, the rendered property is set to true
         * and the render function is re-called from the route method.
         * This was done to inject a given model into its associated controller.
         */
        if ( $injection === null && $this->rendered !== true ) return $this->rendered = true;
        $this->model = $injection;
        return $this->get_output_buffer( $this->get_view_content() );
    }
    
    /**
     * Gets view content.
     * 
     * Retrieves template files using include_once or echo's content.
     *
     * @since 1.0.0
     */
    public function get_view_content() {
        // If is an array, then iterate
        if ( is_array( $this->template ) ) {
            foreach ( $this->template as $content){
                // If is a file, then include it
                if ( $this->verify_include( $content ) ){
                    $this->get_include( $content );
                // If is content, then echo it
                } elseif ( $content != '' ){
                    echo $content;
                }
            }
        // If is not an array
        } else {
            // If string matches valid file include it
            if ( $this->verify_include( $this->template ) ) {
                $this->get_include( $this->template );
            // If is content, then echo it
            } elseif ( $this->template != '' ) {
                echo $this->template;
            }
        }
    }
}