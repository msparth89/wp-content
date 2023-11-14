<?php

namespace Shakti\Token;


use SimpleJWTLogin\Libraries\ParseRequest;
use SimpleJWTLogin\Modules\SimpleJWTLoginSettings;
use SimpleJWTLogin\Modules\WordPressData;
use SimpleJWTLogin\Services\ValidateTokenService;
use SimpleJWTLogin\Helpers\Jwt\JwtKeyFactory;
use SimpleJWTLogin\Libraries\JWT\JWT;


class TokenCheck extends ValidateTokenService
{

function jwt_token_check(  ) {

    	
    $parseRequest = ParseRequest::process($_SERVER);
    $parsedRequestVariables = [];
    if (isset($parseRequest['variables'])) {
        $parsedRequestVariables = (array) $parseRequest['variables'];
    }

    $request = array_merge($_REQUEST, $parsedRequestVariables);
	

    $jwtSettings = new SimpleJWTLoginSettings(new WordPressData());
	 error_log(print_r("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!", true));
		//  error_log(print_r( $jwtSettings, true));

	
    // error_log(print_r("ooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo", true));
	// error_log(print_r($jwtSettings->getGeneralSettings()->isJwtFromURLEnabled(), true));
	    // error_log(print_r("iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii", true));

// 		error_log(print_r($request[$requestUrlKey], true));

	try{
		        JWT::$leeway = 60;
        $decoded = (array)JWT::decode(
            $request["JWT"],
            JwtKeyFactory::getFactory($jwtSettings)->getPublicKey(),
            [$jwtSettings->getGeneralSettings()->getJWTDecryptAlgorithm()]
        );
        return true;
		
	}
		catch(\Exception $e) {
            // return $e;
            throw $e;
        //  wp_send_json_error( $e->getMessage());
}
		

}

};


?>