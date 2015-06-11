(function( $ ){
    
    $(document).ready(function(){
        
         (function(){
            
            $(
             '<div class="example-one-container">'
                +'<div id="example-one-button" class="example-one-button">run request</div>'
                +'<div class="example-one-response"></div>'
            +'</div>'
            ).insertBefore('.entry-footer');
            
         })();
        
         $(document).on('click','#example-one-button',function(e){

            $('.example-one-response').html('');
             
            var requestOptions = ({
                plugin           : 'example-one',
                namespace        : 'example_one',
                module           : 'frontend',
                instance         : 'example_one',
                method           : 'example_one_controller_action',
                response         : 'html',
                parameters:({
                    parameterOne    : 'value-one',
                    parameterTwo    : 'value-two',
                    parameterThree  : 'value-three'
                })
            });
                
            $.ajaxmvc.request(requestOptions,function(data){
                    
                $('.example-one-response').html(data);
                
            });
             
         });
        
    });
    
})( jQuery );