/**
 * Ajax MVC jQuery plugin.
 *
 * Utility for Ajax requests.
 *
 * @since 1.0.0
 */
(function ( $ ) {
     
    $.ajaxmvc = ({
        
        /**
         * Gets localized object literal of WordPress root dir.
         *
         * @since 1.0.0
         *
         * @return string $root WordPress root dir path.
         */
        getRoot: function() {
            var root = ( typeof ajaxmvc.root != 'undefined' ) ? ajaxmvc.root : '';
            return root;
        },
        
        /**
         * Prepend items to requestOptions literal.
         * 
         * Prepends route_request method as action,
         * and localized nonce to requestOptions literal.
         *
         * @since 1.0.0
         *
         * @param literal $requestOptions Literal to be prepended.
         * @return literal $literal Prepended literal.
         */
        prependRequestOptions: function( requestOptions ) {
            var literal = ({
                'action'    : 'route_request',
                'nonce'        : ( typeof ajaxmvc.nonce != 'undefined' ) ? ajaxmvc.nonce : '',
            });
            $.each( requestOptions, function( key, value ) { 
                literal[key] = value ;
            });
            return literal;
        },
        
        /**
         * Wrapper for $.ajax({}).
         *
         * @since 1.0.0
         *
         * @param literal $requestOptions Literal consisting of request options.
         * @param function $callBackSuccess Function to be executed on success.
         * @param function $callBackError Function to be executed on error.
         */
        request: function( requestOptions, callBackSuccess, callBackError ) {
            if ( callBackSuccess ) var callBackSuccess = callBackSuccess;
            if ( callBackError ) var callBackError = callBackError;
            requestOptions = this.prependRequestOptions( requestOptions );
            $.ajax({
                url     : ( typeof ajaxmvc.url != 'undefined' ) ? 
                            ajaxmvc.url : 
                            this.getRoot() + '/wp-admin/admin-ajax.php',
                data    : requestOptions,
                type    : 'post',
                dataType: null,
                success : function ( data, textStatus, xhr ) {
                    /*
                     * I realize that all of these conditions perform the same operation,
                     * I am still entertaining the idea of specific parsing rules based
                     * on the response header so I am leaving this for now.
                     */
                    if ( this.dataType =='html' || xhr.getResponseHeader( 'content-type' ).indexOf( 'html' ) > 0 ) {
                        if ( callBackSuccess ) callBackSuccess( data, textStatus, xhr );
                    } else if ( this.dataType == 'xml' || xhr.getResponseHeader( 'content-type' ).indexOf( 'xml' ) > 0 ) {
                        if ( callBackSuccess ) callBackSuccess( data, textStatus, xhr );
                    } else if ( this.dataType == 'json' || xhr.getResponseHeader( 'content-type' ).indexOf( 'json' ) > 0 ) {
                        if ( callBackSuccess ) callBackSuccess( data, textStatus, xhr );
                    }
                },
                error    : function ( data, textStatus, xhr ) {
                    if ( callBackError ) callBackError( data, textStatus, xhr );
                }
            });
        },
    });
}( jQuery ));