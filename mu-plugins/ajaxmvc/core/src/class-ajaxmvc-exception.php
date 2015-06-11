<?php
/**
 * Ajax MVC Core Exception Class.
 * 
 * Responsible for instantiating a given namespace module's exception class.
 * 
 * @since 1.0.0
 */
class ajaxmvc_core_exception extends ajaxmvc_core_object_factory {
    
    public static function throw_error( $class, $message ) {
        $class = get_class( $class );
        $error_array = array();
        $backtrace = debug_backtrace();
        $count = 0;
        foreach( $backtrace as $backtrace_array ) {
            if( get_class( $backtrace_array['object'] ) == $class ) {
                $error_array = $backtrace_array;
                /**
                 * @todo make this the ajaxmvc_core_exception::throw_application_error
                 *       use the older version which will be archived
                 *       < 20150601 to use as ajaxmvc_core_exception::throw_core_error, see below
                 * changed 20150601
                 * we dont want the first instance as the first instance 
                 * will be in the core level, we want the next level up
                 * which will be in the application level
                 */ 
                if ( 1 == $count ) {
                    break;
                }
                $count++;
            }
        }
        $message = "{$message} found in function: <strong>{$error_array['function']}</strong>, called in file: <strong>{$error_array['file']}</strong> on line: <strong>{$error_array['line']}</strong>";
        throw new Exception( $message );
    } 
    
    public static function throw_core_error( $class, $message ) {
        $class = get_class( $class );
        $error_array = array();
        $backtrace = debug_backtrace();
        foreach( $backtrace as $backtrace_array ) {
            if( $backtrace_array['class'] == $class ) {
                $error_array = $backtrace_array;
                break;
            }
        }
        $message = "{$message} found in function: <strong>{$error_array['function']}</strong>, called in file: <strong>{$error_array['file']}</strong> on line: <strong>{$error_array['line']}</strong>";
        throw new Exception( $message );
    }
}