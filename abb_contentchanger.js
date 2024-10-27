jQuery(document).ready(function($) {

      
    $('#updatecontentpost').click(function() {

        $("#updatecontentpost").prop('disabled', true);

        if(tinymce.activeEditor){
        var editorContent = tinymce.activeEditor.getContent();
        }else{

            if(wp.data){
                if (wp.data.select('core/editor').getEditorSettings().richEditingEnabled) {
                var editorContent = wp.data.select('core/editor').getEditedPostAttribute('content');
                }
            }
       }

        var prompt = $('#gpt3-prompt').val();
        var api_key = $('input[name = api_key]').val();
        var model = $('input[name = model]').val();
        var temperature = $('input[name = temperature]').val();
        var max_tokens = $('input[name = max_tokens]').val();
        var top_p = $('input[name = top_p]').val();
        var frequency_penalty = $('input[name = frequency_penalty]').val();
        var presence_penalty = $('input[name = presence_penalty]').val();



        $.ajax({
            type: 'POST',
            url: abbuddy_contentchangerobj.ajax_url,
            data: {
                action: 'update_post_content',
                content: editorContent,
                prompt: prompt,
                api_key: api_key,
                model: model,
                temperature: temperature,
                max_tokens: max_tokens,
                top_p: top_p,
                frequency_penalty: frequency_penalty,
                presence_penalty: presence_penalty
            },
            success: function(response) {
                $("#updatecontentpost").prop('disabled', false);

               if(response['data'].error_message){
                alert(response['data'].error_message);
               }

                if(tinymce.activeEditor){
                tinymce.activeEditor.setContent(response['data'].updated_content);
                }else{
                    if(wp.data){
                    if (wp.data.select('core/editor').getEditorSettings().richEditingEnabled) {
                        var blocks = wp.blocks.parse(response['data'].response_content);
                        wp.data.dispatch('core/editor').insertBlocks(blocks);
                    } 
               }

            }
                  

            }
        });




    });
});