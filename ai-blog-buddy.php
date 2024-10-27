<?php
/*
Plugin Name: AI Blog Buddy
Plugin URI: https://mediabytes.gumroad.com
Description: Say goodbye to writer's block and hello to endless creative inspiration with AI Blog Buddy, the Wordpress plugin that harnesses the power of OpenAI's GPT-3 technology to help you write and publish content like never before.
Version: 3.1
Author: MediaBytes
*/



function abbuddy_reload_content_of_post() {
    // Enqueue the script that will handle the AJAX request
    wp_enqueue_script( 'abbuddy_contentchanger', plugins_url( 'abb_contentchanger.js', __FILE__ ), array( 'jquery' ) );
    // Localize the script so we can access the admin-ajax.php URL
    wp_localize_script( 'abbuddy_contentchanger', 'abbuddy_contentchangerobj', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'admin_enqueue_scripts', 'abbuddy_reload_content_of_post' );




function abbuddy_model_form_meta_box() {
    add_meta_box(
        'abbuddy_model_form_meta_box', // ID
        'OpenAPI Chat', // Title
        'abbuddy_model_form_callback', // Callback
        'post', // Post Type
        'normal', // Context
        'default' // Priority
    );
    add_meta_box(
        'abbuddy_model_form_meta_box', // ID
        'OpenAPI Chat', // Title
        'abbuddy_model_form_callback', // Callback
        'page', // Post Type
        'normal', // Context
        'default' // Priority
    );
}
add_action( 'add_meta_boxes', 'abbuddy_model_form_meta_box' );

function abbuddy_get_custom_model_data() {
    // Use get_option() to retrieve the data
    return get_option('abbuddy_custom_model_data');
}

// Function to save custom data
function abbuddy_save_model_custom_data($data) {
    // Use add_option() to add the data if it doesn't exist, or update_option() to update it if it does
    add_option('abbuddy_custom_model_data', $data);
}

// Function to update custom data
function abbuddy_update_model_custom_data($new_data) {
    // Use update_option() to update the data
    update_option('abbuddy_custom_model_data', $new_data);
}

function abbuddy_delete_model_custom_data(){
    delete_option('abbuddy_custom_model_data');
}


function abbuddy_model_form_callback( $post ) {
    // Output the form fields

    $data = abbuddy_get_custom_model_data();
    $model = isset($data['model'])?$data['model']:'text-davinci-003';
    $api_key = isset($data['api_key'])?$data['api_key']:'';
    $prompt= isset($data['prompt'])?$data['prompt']:'';
    $temperature= isset($data['temperature'])?$data['temperature']:1;
    $max_tokens=isset($data['max_tokens'])?$data['max_tokens']:16;
    $top_p = isset($data['top_p'])?$data['top_p']:1;
    $frequency_penalty = isset($data['frequency_penalty'])?$data['frequency_penalty']:0;
    $presence_penalty=isset($data['presence_penalty'])?$data['presence_penalty']:0;

?>
    <label for="model" style="width: 100%">Model :</label>
    <input style="margin-top: inherit;width: 100%;" type="text" id="model" name="model" value="<?php echo esc_attr($model); ?>" />
    <br>
    <label for="api_key" style="width: 100%">API Key:</label>
    <input style="margin-top: inherit;width: 100%;" type="text" id="api_key" name="api_key"  value="<?php echo esc_attr($api_key); ?>"  />
    <br>
    <label for="gpt3-prompt" style="width: 100%">Prompt:</label>
    <textarea style="margin-top: inherit; vertical-align: super;width: 100%;" id="gpt3-prompt" name="prompt"  value="<?php echo esc_attr($prompt);?>" ><?php echo esc_attr($prompt); ?></textarea>
    <br>
    <label for="gpt3-temperature" style="width: 100%">Temperature:</label>
    <input style="margin-top: inherit;width: 100%;" type="number" min="0" max="2" step="0.1" id="gpt3-temperature" name="temperature" value="<?php echo floatval($temperature);?>">
    <br>
    <label for="gpt3-max-tokens" style="width: 100%">Max Tokens:</label>
    <input style="margin-top: inherit;width: 100%;" type="number" min="1" id="gpt3-max-tokens" name="max_tokens" value="<?php echo (int)$max_tokens; ?>">
    <br>
    <label for="gpt3-top-p" style="width: 100%">Top P:</label>
    <input style="margin-top: inherit;width: 100%;" type="number" min="0" max="1" step="0.1" id="gpt3-top-p" name="top_p" value="<?php echo floatval($top_p); ?>">
    <br>
    <label for="gpt3-frequency-penalty" style="width: 100%">Frequency Penalty:</label>
    <input style="margin-top: inherit;width: 100%;" type="number" min="-2" max="2" step="0.1" id="gpt3-frequency-penalty" name="frequency_penalty" value="<?php echo floatval($frequency_penalty);?>">
    <br>
    <label for="gpt3-presence-penalty" style="width: 100%">Presence Penalty:</label>
    <input style="margin-top: inherit;width: 100%;" type="number" min="-2" max="2" step="0.1" id="gpt3-presence-penalty" name="presence_penalty"  value="<?php echo floatval($presence_penalty);?>">
    <br>
    <button style="margin-top: inherit;" type="button" id="updatecontentpost" class="button button-primary button-large" >Send</button> 
    <br>


<?php

}


function abbuddy_validate_all($allfields){

    $result = [];
    $result['error'] = false;
    $result['errorMsg'] = '';
    
    if(!is_numeric($allfields['presence_penalty']) || !is_numeric($allfields['top_p']) 
        ||!is_numeric($allfields['frequency_penalty'])
        || !is_numeric($allfields['max_tokens']) || !is_numeric($allfields['temperature'])
     ){
        $result['error'] = true;
        $result['errorMsg']= 'Please enter numeric value for numeric fields.';
    }

    if(!($allfields['presence_penalty']>=-2.0 && $allfields['presence_penalty']<=2.0)){
        $result['error'] = true;
        $result['errorMsg']= 'Presence Penalty Should be Number between -2.0 and 2.0';
    }


    if(!($allfields['top_p']>=0 && $allfields['top_p']<=1)){
        $result['error'] = true;
        $result['errorMsg']= 'Top P Should be Number between 0 and 1';
    }

    if(!($allfields['frequency_penalty']>=-2.0 && $allfields['frequency_penalty']<=2.0)){
        $result['error'] = true;
        $result['errorMsg']= 'Frequency Penalty Should be Number between -2.0 and 2.0';
    }

    if(!($allfields['max_tokens']<=4096)){
        $result['error'] = true;
        $result['errorMsg']= 'Max token is 4096';
    }
    if(!($allfields['temperature']>=0 && $allfields['temperature']<=2.0)){
        $result['error'] = true;
        $result['errorMsg']= 'Temperature Number Should be between 0 and 2';
    }

    return $result;
}

add_action( 'wp_ajax_update_post_content', 'abbuddy_update_post_content' );

function abbuddy_update_post_content() {

    $content = wp_kses_post(trim($_POST['content']));

    $dataupdate = [];
    $respose_content = '';
    $error_message = '';

    if(isset($_POST['api_key']) &&
     isset($_POST['prompt']) &&
     isset($_POST['temperature']) &&
     isset($_POST['max_tokens'] )&&
     isset($_POST['top_p']) &&
     isset($_POST['frequency_penalty']) &&
     isset($_POST['presence_penalty'])&&
     isset($_POST['model']))
     {

            //trim all and sanitize
            $prompt = sanitize_text_field(trim($_POST['prompt']));
            $temperature = floatval(sanitize_text_field(trim($_POST['temperature'])));
            $max_tokens = intval(sanitize_text_field(trim($_POST['max_tokens'])));//60 ;
            $top_p =  floatval(sanitize_text_field(trim($_POST['top_p']))) ;//1 ;
            $frequency_penalty =  floatval(sanitize_text_field(trim($_POST['frequency_penalty'])));//0.5;
            $presence_penalty =  floatval(sanitize_text_field(trim($_POST['presence_penalty'])));//0 ;
            $model = sanitize_text_field(trim($_POST['model']));// 'text-davinci-003';
            $api_key=   sanitize_text_field(trim($_POST['api_key']));

            $dataupdate = array(
                'prompt' => $prompt,
                'temperature' => $temperature,
                'max_tokens' => $max_tokens,
                'top_p' => $top_p,
                'frequency_penalty' => $frequency_penalty,
                'presence_penalty' => $presence_penalty,
                'model' => $model
            );


            $savedata = array('model' => $model,'api_key'=>$api_key,
            'prompt'=> htmlspecialchars($prompt),'presence_penalty' => $presence_penalty,
            'top_p'=>$top_p,'frequency_penalty'=>$frequency_penalty,'max_tokens'=>$max_tokens,'temperature'=>$temperature);

            //validate all
            $validate =   abbuddy_validate_all($savedata);

            if(!$validate['error']){

                abbuddy_update_model_custom_data($savedata);


                if($prompt)
                {
                    $api_url = "https://api.openai.com/v1/completions";

                    // var_dump(json_encode($dataupdate));die();
                    // Send API request using example_field as the data
                    $response = wp_remote_post( $api_url, array(
                        'method' => 'POST',
                        'timeout' => 45,
                        'redirection' => 5,
                        'httpversion' => '1.0',
                        'blocking' => true,
                        'headers' => array(
                            'Authorization' => "Bearer $api_key",
                            'Content-Type' => 'application/json',
                        ),
                        'body' => json_encode( $dataupdate) ,
                        'cookies' => array()
                    ) );



                    if ( is_wp_error( $response ) ) {
                        $error_message = $response->get_error_message();     
                    } else {
                        $response_body = json_decode( $response['body'], true );

                        if (isset($response_body['choices'][0]['text'])) {
                            $respose_content = $response_body['choices'][0]['text'];
                            $content .= $response_body['choices'][0]['text'];
                        }
                        
                        if(isset($response_body['error'])){
                            $error_message =  $response_body['error']['message'];
                        }

                        
                    }
                }
            }else
            {
                $error_message = $validate['errorMsg'];
            }



    }else
    {
        $error_message = "All input fields are required. ";
    }



    $response = array(
        'message' => 'Content updated successfully',
        'response_content'=> $respose_content,
        'updated_content' => $content,
        'error_message' => $error_message
    );

    wp_send_json_success( $response );


}






?>