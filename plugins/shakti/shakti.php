<?php
/*
    Plugin Name: Shakti
    Description: Laxmi
    Author: Parthasarathy
    Version: 0.0.1
*/

namespace Shakti\Main;

use Shakti\Token\TokenCheck;
use Shakti\User\Player;
use Shakti\Service\AuthenticateService;

use Shakti\Service\RefreshTokenService;


class Shakti {
    
    function __construct() {
        

       add_action( 'rest_api_init', function () {

        remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );

        // add_filter( 'rest_pre_serve_request', initCors);
        add_filter( 'rest_pre_serve_request', function( $value ) {
            header( 'Access-Control-Allow-Origin: *' );
            header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
            header( 'Access-Control-Allow-Credentials: true' );
            header( 'Access-Control-Allow-Headers: *' );
            return $value;
        });


            register_rest_route( 'shakti/v1', '/authenticate', array(
                'methods' => 'GET',
                'callback' => array($this,'authenticate_user')
              //   'permission_callback' => '__return_false',
              // 'permission_callback' => $this->jwt_token_check(),
  
  
              ) );




                // Here we are registering our route for a collection of products.
    register_rest_route( 'shakti/v1', '/lllll', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => 'GET',
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => array($this,'yyy'),
    ) );


    // Here we are registering our route for single products. The (?P<id>[\d]+) is our path variable for the ID, which, in this example, can only be some form of positive number.
    register_rest_route( 'shakti/v1', '/register', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => 'GET',
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => array($this,'register_user'),
    ) );




    register_rest_route( 'shakti/v1', '/resign', array(
        'methods' => 'GET',
        'callback' => array($this,'refresh_token')
      //   'permission_callback' => '__return_false',
      // 'permission_callback' => $this->jwt_token_check(),


      ) );



          } );


    }
    
    




function register_user(){

    error_log('SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSS');

    require_once('inc/Register-user.php');

    

    try{
        $yodha = new Player();
        $yodha->Register_sms();



    }catch( \Exception $e ){

        return $e;

    }

    

}





function rest_api_resigter( ){

    // include( plugin_dir_path(__FILE__) . 'inc/Token-check.php');

    

//     add_action('rest_api_init', function() {
    register_rest_route('shakti/v1', '/check', [
        /**
         * Http method can be also PUT, DELETE, POST
         */
        'methods' => 'GET',
        
        /**
         * Response to the user
         */
        'callback' => function() {
          return 'Hello world!';
        },
        
        /**
         * This is where the authentication happens
         */
        // 'permission_callback' =>'jwt_token_check',
      ]);

//     });

    //       // register_rest_route() handles more arguments but we are going to stick to the basics for now.
    // register_rest_route( 'simple-jwt-login/v1', '/shaktiq', array(
    //     // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
    //     'methods'  => "GET",
    //     // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
    //     'callback' => 'prefix_get_endpoint_phrase',
    //     'permission_callback' =>'jwt_token_check',
    // ) );
    

    // register_rest_route() handles more arguments but we are going to stick to the basics for now.
    // register_rest_route( 'simple-jwt-login/v1', '/shaktie', array(
    //     // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
    //     'methods'  => "GET",
    //     // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
    //     'callback' => 'prefix_get_endpoint_phrase',
    // ) );


}
function jwt_token_check(  ) {
error_log('rrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrr');
require_once('inc/Token-check.php');

    

    try{
        $hello = new TokenCheck();
        $hello->jwt_token_check();
        // return '__return_true';
        error_log('YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY');
                return '__return_true';


    }catch( \Exception $e ){
        error_log('OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO');

        return '__return_false';

    }


    // return false;

//    return '__return_true';

}

function authenticate_user( ) {
    // error_log('88888888888888888888888888888888888888888888888');
    error_log('77777777777777777777777777777777777777777777777');

    require_once('inc/Authenticate-token.php');
    

    error_log('%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%');

    try{
        $auth = new AuthenticateService();
        $auth->authenticateUser();
        // return '__return_true';
        // error_log('1111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111');
                // return '__return_true';


    }catch( \Exception $e ){
        // error_log('22222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222');
        error_log( print_r( $e, true ) );
        return '__return_false';

    }


}

function yyy() {

    error_log('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');

    return '__return_true'              ;
}


function refresh_token(){
    error_log('88888888888888888888888888888888888888888888888');

    require_once('inc/Refresh-Token.php');
    

    error_log('%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%');

    try{
        $refreshtoken = new RefreshTokenService();
        $refreshtoken->makeAction();
        // return '__return_true';
        error_log('1111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111');
                return '__return_true';


    }catch( \Exception $e ){
        error_log('22222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222');
        error_log( print_r( $e, true ) );
        return '__return_false';

    }


}

// function jwt_token_check(  ) {

    	
//     $parseRequest = ParseRequest::process($_SERVER);
//     $parsedRequestVariables = [];
//     if (isset($parseRequest['variables'])) {
//         $parsedRequestVariables = (array) $parseRequest['variables'];
//     }

//     $request = array_merge($_REQUEST, $parsedRequestVariables);
	
	
		// $hello = new Classride();


//     $jwtSettings = new SimpleJWTLoginSettings(new WordPressData());
// 	 error_log(print_r("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!", true));
// 		 error_log(print_r( $jwtSettings, true));

// //     
// //     
// //     

	
//     error_log(print_r("ooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo", true));
// 	error_log(print_r($jwtSettings->getGeneralSettings()->isJwtFromURLEnabled(), true));
// 	    error_log(print_r("iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii", true));

// // 		error_log(print_r($request[$requestUrlKey], true));

// 	try{
// 		        JWT::$leeway = 60;
//         $decoded = (array)JWT::decode(
//             $request["JWT"],
//             JwtKeyFactory::getFactory($jwtSettings)->getPublicKey(),
//             [$jwtSettings->getGeneralSettings()->getJWTDecryptAlgorithm()]
//         );
//         return true;
// 		// wp_send_json_success($decoded);
// // 		return json_encode($decoded);

		
// 	}
// 		catch(Exception $e) {
//             return false;
//         //  wp_send_json_error( $e->getMessage());
// }
		
	

	
// //         return $hello->getUserParameterValueFromPayload($decoded, $parameter);

	


   


     

    
}

$Shakti = new Shakti();




?>