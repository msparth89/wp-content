<?php

namespace SimpleJWTLogin\Services;

use SimpleJWTLogin\ErrorCodes;
use SimpleJWTLogin\Helpers\Jwt\JwtKeyFactory;
use SimpleJWTLogin\Libraries\JWT\JWT;
use SimpleJWTLogin\Modules\Settings\AuthenticationSettings;
use SimpleJWTLogin\Modules\SimpleJWTLoginHooks;
use SimpleJWTLogin\Modules\SimpleJWTLoginSettings;
use SimpleJWTLogin\Modules\WordPressDataInterface;
use WP_REST_Response;
use Exception;
use WP_User;

class AuthenticateService extends BaseService implements ServiceInterface
{
    /**
     * @param array $payload
     * @param WordPressDataInterface $wordPressData
     * @param SimpleJWTLoginSettings $jwtSettings
     * @param WP_User $user
     *
     * @return array
     */
    public static function generatePayload(
        $payload,
        $wordPressData,
        $jwtSettings,
        $user
    ) {
        $payload[AuthenticationSettings::JWT_PAYLOAD_PARAM_IAT] = time();

        foreach ($jwtSettings->getAuthenticationSettings()->getJwtPayloadParameters() as $parameter) {
            if ($parameter === AuthenticationSettings::JWT_PAYLOAD_PARAM_IAT
                || $jwtSettings->getAuthenticationSettings()->isPayloadDataEnabled($parameter) === false
            ) {
                continue;
            }

            switch ($parameter) {
                case AuthenticationSettings::JWT_PAYLOAD_PARAM_EXP:
                    $ttl = (int)$jwtSettings->getAuthenticationSettings()->getAuthJwtTtl() * 60;
                    $payload[$parameter] = time() + $ttl;
                    break;
                case AuthenticationSettings::JWT_PAYLOAD_PARAM_ID:
                    $payload[$parameter] = $wordPressData->getUserProperty($user, 'ID');
                    break;
                case AuthenticationSettings::JWT_PAYLOAD_PARAM_EMAIL:
                    $payload[$parameter] = $wordPressData->getUserProperty($user, 'user_email');
                    break;
                case AuthenticationSettings::JWT_PAYLOAD_PARAM_SITE:
                    $payload[$parameter] = $wordPressData->getSiteUrl();
                    break;
                case AuthenticationSettings::JWT_PAYLOAD_PARAM_USERNAME:
                    $payload[$parameter] = $wordPressData->getUserProperty($user, 'user_login');
                    break;
            }
        }
        
        // Allow developers to create their own payload values inside of the returned JWT
        if ($jwtSettings->getHooksSettings()->isHookEnable(SimpleJWTLoginHooks::HOOK_GENERATE_PAYLOAD)) {
            $payload = $wordPressData->triggerFilter(SimpleJWTLoginHooks::HOOK_GENERATE_PAYLOAD, $payload, $user);
        }

        return $payload;
    }

    /**
     * @return WP_REST_Response
     * @throws Exception
     */
    public function makeAction()
    {
        $this->checkAuthenticationEnabled();
        $this->checkAllowedIPAddress();
        $this->validateAuthenticationAuthKey(ErrorCodes::ERR_INVALID_AUTH_CODE_PROVIDED);

        return $this->authenticateUser();
    }

    /**
     * @SuppressWarnings(StaticAccess)
     * @return WP_REST_Response
     * @throws Exception
     */

     /////////////////////////////////////////////////////////////////////////////////////////////////
    public function authenticateUser()
    {
        $user=null;
        if (isset($this->request['phone_number'])) {
            error_log('haiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii');
            $phoneNumber = sanitize_text_field($this->request['phone_number']);
            if (preg_replace('/^(\-){0,1}[0-9]+(\.[0-9]+){0,1}/', '', $phoneNumber) == "") {
                $phone_number = ltrim($phoneNumber, '0');
                $phone_number = substr($phone_number, 0, 15);

                if ($phone_number < 10) {
                    echo json_encode([
                        'success' => false,
                        'phone_number' => $phone_number,
                        'message' => __('phone number is wrong!', 'login-with-phone-number')
                    ]);
                    die();
                }
            }
            $username_exists = $this->phone_number_exist($phone_number);
        }else{
            throw new Exception(
                __('phone number error.', 'simple-jwt-login'),
                ErrorCodes::AUTHENTICATION_WRONG_CREDENTIALS
            );
        }

        if ($username_exists) {
            error_log('"yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy');
            $activation_code = get_user_meta($username_exists, 'activation_code', true);
            $secod = sanitize_text_field($this->request['secod']);
            $passwordMatch=false;


            if ($activation_code == $secod){
                error_log('ssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss');
                $passwordMatch=true;
                $user = get_user_by('ID', $username_exists);
            }



            if ($passwordMatch === false) {
                error_log('ooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo');

                throw new Exception(
                    __('Wrong user credentials.', 'simple-jwt-login'),
                    ErrorCodes::AUTHENTICATION_WRONG_CREDENTIALS
                );
            }




        }else{
            throw new Exception(
                __('Wrong user credentials.', 'simple-jwt-login'),
                ErrorCodes::AUTHENTICATION_WRONG_CREDENTIALS
            );

        }


/////////////////////////////////////////////////////////////////////


        // error_log($this->request);
        // if (!isset($this->request['email']) && !isset($this->request['username'])) {
        //     throw new Exception(
        //         __('The email or username parameter is missing from request.', 'simple-jwt-login'),
        //         ErrorCodes::AUTHENTICATION_MISSING_EMAIL
        //     );
        // }
        // if (!isset($this->request['password']) && !isset($this->request['password_hash'])) {
        //     throw new Exception(
        //         __('The password or password_hash parameter is missing from request.', 'simple-jwt-login'),
        //         ErrorCodes::AUTHENTICATION_MISSING_PASSWORD
        //     );
        // }

        // $user = isset($this->request['username'])
        //     ? $this->wordPressData->getUserByUserLogin(
        //         $this->wordPressData->sanitizeTextField($this->request['username'])
        //     )
        //     : $this->wordPressData->getUserDetailsByEmail(
        //         $this->wordPressData->sanitizeTextField($this->request['email'])
        //     );

        // if (empty($user)) {
        //     throw new Exception(
        //         __('Wrong user credentials.', 'simple-jwt-login'),
        //         ErrorCodes::AUTHENTICATION_WRONG_CREDENTIALS
        //     );
        // }

        // $password = isset($this->request['password'])
        //     ? $this->wordPressData->sanitizeTextField($this->request['password'])
        //     : null;
        // $passwordHash = isset($this->request['password_hash'])
        //     ? $this->wordPressData->sanitizeTextField($this->request['password_hash'])
        //     : null;

        // if ($this->jwtSettings->getAuthenticationSettings()->isAuthPasswordBase64Encoded()) {
        //     if ($password !== null) {
        //         $password = base64_decode($password);
        //     }
        //     if ($passwordHash !== null) {
        //         $passwordHash = base64_decode($passwordHash);
        //     }
        // }

        // $dbPassword = $this->wordPressData->getUserPassword($user);
        // $passwordMatch = $this->wordPressData->checkPassword($password, $passwordHash, $dbPassword);


        // if ($passwordMatch === false) {
        //     throw new Exception(
        //         __('Wrong user credentials.', 'simple-jwt-login'),
        //         ErrorCodes::AUTHENTICATION_WRONG_CREDENTIALS
        //     );
        // }

                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


        //Generate payload
        $payload = isset($this->request['payload'])
            ? json_decode(
                stripslashes(
                    $this->wordPressData->sanitizeTextField($this->request['payload'])
                ),
                true
            )
            : [];

        $payload = self::generatePayload(
            $payload,
            $this->wordPressData,
            $this->jwtSettings,
            $user
        );

        if ($this->jwtSettings->getHooksSettings()->isHookEnable(SimpleJWTLoginHooks::JWT_PAYLOAD_ACTION_NAME)) {
            $payload = $this->wordPressData->triggerFilter(
                SimpleJWTLoginHooks::JWT_PAYLOAD_ACTION_NAME,
                $payload,
                $this->request
            );
        }

        $response = [
            'success' => true,
            'data' => [
                'jwt' => JWT::encode(
                    $payload,
                    JwtKeyFactory::getFactory($this->jwtSettings)->getPrivateKey(),
                    $this->jwtSettings->getGeneralSettings()->getJWTDecryptAlgorithm()
                ),
                'hint' => 'what are you talking about'
            ]
        ];
        if ($this->jwtSettings->getHooksSettings()->isHookEnable(SimpleJWTLoginHooks::HOOK_RESPONSE_AUTH_USER)) {
            $response = $this->wordPressData->triggerFilter(
                SimpleJWTLoginHooks::HOOK_RESPONSE_AUTH_USER,
                $response,
                $user
            );
        }

        return $this->wordPressData->createResponse($response);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @throws Exception
     */
    protected function checkAllowedIPAddress()
    {
        $allowedIpsString = trim($this->jwtSettings->getAuthenticationSettings()->getAllowedIps());
        if (!empty($allowedIpsString) && !$this->serverHelper->isClientIpInList($allowedIpsString)) {
            throw new Exception(
                sprintf(
                    __('You are not allowed to Authenticate from this IP: %s', 'simple-jwt-login'),
                    $this->serverHelper->getClientIP()
                ),
                ErrorCodes::ERR_DELETE_INVALID_CLIENT_IP
            );
        }
    }

    /**
     * @throws Exception
     */
    protected function checkAuthenticationEnabled()
    {
        if ($this->jwtSettings->getAuthenticationSettings()->isAuthenticationEnabled() === false) {
            throw new Exception(
                __('Authentication is not enabled.', 'simple-jwt-login'),
                ErrorCodes::AUTHENTICATION_IS_NOT_ENABLED
            );
        }
    }

    /**
     * @param int $errrCode
     *
     * @throws Exception
     */
    protected function validateAuthenticationAuthKey($errrCode)
    {
        if ($this->jwtSettings->getAuthenticationSettings()->isAuthKeyRequired()
            && $this->validateAuthKey() === false
        ) {
            throw  new Exception(
                sprintf(
                    __('Invalid Auth Code ( %s ) provided.', 'simple-jwt-login'),
                    $this->jwtSettings->getAuthCodesSettings()->getAuthCodeKey()
                ),
                $errrCode
            );
        }
    }

    protected  function phone_number_exist($phone_number)
    {
        $args = array(
            'meta_query' => array(
                array(
                    'key' => 'phone_number',
                    'value' => $phone_number,
                    'compare' => '='
                )
            )
        );

        $member_arr = get_users($args);
        if ($member_arr && $member_arr[0])
            return $member_arr[0]->ID;
        else
            return 0;

    }
}
