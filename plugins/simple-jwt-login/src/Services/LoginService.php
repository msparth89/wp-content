<?php

namespace SimpleJWTLogin\Services;

use Exception;
use SimpleJWTLogin\ErrorCodes;
use SimpleJWTLogin\Helpers\Jwt\JwtKeyFactory;
use SimpleJWTLogin\Libraries\JWT\JWT;
use SimpleJWTLogin\Modules\Settings\LoginSettings;
use SimpleJWTLogin\Modules\SimpleJWTLoginHooks;
use WP_REST_Response;
use WP_User;

class LoginService extends BaseService implements ServiceInterface
{
    public function makeAction()
    {
        error_log(print_r("((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((", true));

        try {
            return $this->makeActionInternal();
        } catch (Exception $e) {
            $redirectOnFail = $this->jwtSettings->getLoginSettings()->getAutologinRedirectOnFail();
            if (!empty($redirectOnFail)) {
                $redirectOnFail = $this->includeRequestParameters($redirectOnFail);
                $redirectOnFail .= (strpos($redirectOnFail, '?') !== false ? '&' : '?')
                    . http_build_query([
                        'error_message' => $e->getMessage(),
                        'error_code' => $e->getCode()
                    ]);

                return $this->wordPressData->redirect($redirectOnFail);
            }
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return WP_REST_Response|null
     * @throws Exception
     */
    public function makeActionInternal()
    {
        error_log(print_r("nnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnn", true));

        $this->validateDoLogin();
        error_log(print_r("lllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllll", true));
        error_log(print_r("pppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppp", true));

        $loginParameter = $this->validateJWTAndGetUserValueFromPayload(
            $this->jwtSettings->getLoginSettings()->getJwtLoginByParameter()
        );
        error_log(print_r("hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh", true));

        error_log(print_r($loginParameter, true));

        /** @var WP_User|null $user */
        $user = $this->getUserDetails($loginParameter);
        if ($user === null) {
            throw new Exception(
                __('User not found.', 'simple-jwt-login'),
                ErrorCodes::ERR_DO_LOGIN_USER_NOT_FOUND
            );
        }

        $this->validateJwtRevoked(
            $this->wordPressData->getUserProperty($user, 'ID'),
            $this->jwt
        );
        $this->wordPressData->loginUser($user);
        if ($this->jwtSettings->getHooksSettings()->isHookEnable(SimpleJWTLoginHooks::LOGIN_ACTION_NAME)) {
            $this->wordPressData->triggerAction(SimpleJWTLoginHooks::LOGIN_ACTION_NAME, $user);
        }

        return (new RedirectService())
            ->withSettings($this->jwtSettings)
            ->withSession($this->session)
            ->withCookies($this->cookie)
            ->withRequest($this->request)
            ->withUser($user)
            ->withServerHelper($this->serverHelper)
            ->makeAction();
    }

    /**
     * @throws Exception
     */
    private function validateDoLogin()
    {
        error_log(print_r("=================================================================================", true));

        $this->jwt = $this->getJwtFromRequestHeaderOrCookie();
        if ($this->jwtSettings->getLoginSettings()->isAutologinEnabled() === false) {
            error_log(print_r("6666666666666666666666666666666666666666666666666666666666666666666666", true));

            throw new Exception(
                __('Auto-login is not enabled on this website.', 'simple-jwt-login'),
                ErrorCodes::ERR_AUTO_LOGIN_NOT_ENABLED
            );
        }
        error_log(print_r("1111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111", true));

        if (empty($this->jwt)) {
            throw new Exception(
                __('Wrong Request.', 'simple-jwt-login'),
                ErrorCodes::ERR_VALIDATE_LOGIN_WRONG_REQUEST
            );
        }
        error_log(print_r("22222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222", true));

        if ($this->jwtSettings->getLoginSettings()->isAuthKeyRequiredOnLogin() && $this->validateAuthKey() === false) {
            error_log(print_r("5555555555555555555555555555555555555555555555555555555555555555", true));

            throw  new Exception(
                sprintf(
                    __('Invalid Auth Code ( %s ) provided.', 'simple-jwt-login'),
                    $this->jwtSettings->getAuthCodesSettings()->getAuthCodeKey()
                ),
                ErrorCodes::ERR_INVALID_AUTH_CODE_PROVIDED
            );
        }
        error_log(print_r("33333333333333333333333333333333333333333333333333333333333333333333333333333333333333333", true));

        $allowedIPs = $this->jwtSettings->getLoginSettings()->getAllowedLoginIps();
        if (!empty($allowedIPs) && !$this->serverHelper->isClientIpInList($allowedIPs)) {
            error_log(print_r("444444444444444444444444444444444444444444444444444444444444444444444444444", true));

            throw new Exception(
                sprintf(
                    __('This IP[ %s ] is not allowed to auto-login.', 'simple-jwt-login'),
                    $this->serverHelper->getClientIP()
                ),
                ErrorCodes::ERR_IP_IS_NOT_ALLOWED_TO_LOGIN
            );
        }
    }
}
