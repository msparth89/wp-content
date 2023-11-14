<?php

namespace Shakti\User;
use Shakti\Channels\Shakti_SMS;

require 'Custom-gateway-sms.php';


class Player
{

function Register_sms(  ) {



////////////////////////////////////////////////////////////////////////////////////////////////////



        // error_log($_GET);
        $usesrname = sanitize_text_field($_GET['username']);
        $method = sanitize_text_field($_GET['method']);
        $shakti =null;
        if(isset($_GET['shakti']))
        {
            $shakti =  sanitize_text_field($_GET['shakti']);
            error_log(print_r("OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO", true));

            error_log(print_r($shakti, true));
        }else{
            $shakti =false;
        }
        $options = get_option('idehweb_lwp_settings');
        if($shakti==false)
        {
            error_log(print_r("tttttttttttttttttttttttttttttttttttttttttttttttttttttttt", true));
        if (!wp_verify_nonce($_GET['nonce'], 'lwp_login')) {
            die ('Busted!');
        }
    }
        if (preg_replace('/^(\-){0,1}[0-9]+(\.[0-9]+){0,1}/', '', $usesrname) == "") {
            error_log(print_r("RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR", true));
            $phone_number = ltrim($usesrname, '0');
            $phone_number = substr($phone_number, 0, 15);
//echo $phone_number;
//die();
            if (strlen($phone_number) < 10) {
                echo json_encode([
                    'success' => false,
                    'phone_number' => $phone_number,
                    'message' => __('phone number is wrong!', 'login-with-phone-number')
                ]);
                die();
            }
            $username_exists = $this->phone_number_exist($phone_number);
//            $registration = get_site_option('registration');
            if (!isset($options['idehweb_user_registration'])) $options['idehweb_user_registration'] = '1';
            $registration = $options['idehweb_user_registration'];
            $is_multisite = is_multisite();
            if ($is_multisite) {
                if ($registration == '0' && !$username_exists) {
                    echo json_encode([
                        'success' => false,
                        'phone_number' => $usesrname,
                        'registeration' => $registration,
                        'is_multisite' => $is_multisite,
                        'username_exists' => $username_exists,
                        'message' => __('users can not register!', 'login-with-phone-number')
                    ]);
                    die();
                }
            } else {
                if (!$username_exists) {

                    if ($registration == '0') {
                        echo json_encode([
                            'success' => false,
                            'phone_number' => $usesrname,
                            'registeration' => $registration,
                            'is_multisite' => $is_multisite,
                            'username_exists' => $username_exists,
                            'message' => __('users can not register!', 'login-with-phone-number')
                        ]);
                        die();
                    }
                }
            }
            $userRegisteredNow = false;
            if (!$username_exists) {
                $info = array();
                $info['user_login'] = $this->generate_username($phone_number);
                $info['user_nicename'] = $info['nickname'] = $info['display_name'] = $this->generate_nickname();
                $info['user_url'] = sanitize_text_field($_GET['website']);
                $user_register = wp_insert_user($info);
                if (is_wp_error($user_register)) {
                    $error = $user_register->get_error_codes();

                    if (in_array('empty_user_login', $error)) {
                        echo json_encode([
                            'success' => false,
                            'phone_number' => $phone_number,
                            'message' => __($user_register->get_error_message('empty_user_login'))
                        ]);
                        die();
                    } elseif (in_array('existing_user_login', $error)) {
                        echo json_encode([
                            'success' => false,
                            'phone_number' => $phone_number,
                            'message' => __('This username is already registered.', 'login-with-phone-number')
                        ]);
                        die();
                    } elseif (in_array('existing_user_email', $error)) {
                        echo json_encode([
                            'success' => false,
                            'phone_number' => $phone_number,
                            'message' => __('This email address is already registered.', 'login-with-phone-number')
                        ]);
                        die();
                    }
                    die();
                } else {
                    add_user_meta($user_register, 'phone_number', sanitize_user($phone_number));
                    update_user_meta($user_register, '_billing_phone', sanitize_user($phone_number));
                    update_user_meta($user_register, 'billing_phone', sanitize_user($phone_number));
//                    update_user_meta($user_register, '_shipping_phone', sanitize_user($phone_number));
//                    update_user_meta($user_register, 'shipping_phone', sanitize_user($phone_number));
                    $userRegisteredNow = true;
                    add_user_meta($user_register, 'updatedPass', 0);
                    $username_exists = $user_register;

                }


            }
            $showPass = false;
            $log = '';


//            $options = get_option('idehweb_lwp_settings');
            if (!isset($options['idehweb_password_login'])) $options['idehweb_password_login'] = '1';
            $options['idehweb_password_login'] = (bool)(int)$options['idehweb_password_login'];
            if (!$options['idehweb_password_login']) {
                error_log(print_r("22222222222222222222222222222222222222222222222222222222222222222222222222222", true));

                $log = $this->lwp_generate_token($username_exists, $phone_number, false, $method);

            } else {
                if (!$userRegisteredNow) {
                    $showPass = true;
                } else {
                    error_log(print_r("111111111111111111111111111111111111111111111111111111111111111111111", true));

                    $log = $this->lwp_generate_token($username_exists, $phone_number, false, $method);
                }
            }
            error_log(print_r("iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii", true));

            echo json_encode([
                'success' => true,
                'ID' => $username_exists,
                'phone_number' => $phone_number,
                'showPass' => $showPass,
//                '$userRegisteredNow' => $userRegisteredNow,
//                '$userRegisteredNow1' => $options['idehweb_password_login'],
                'authWithPass' => (bool)(int)$options['idehweb_password_login'],
                'message' => __('Sms sent successfully!', 'login-with-phone-number'),
                'log' => $log
            ]);
            die();

        } else {
            echo json_encode([
                'success' => false,
                'phone_number' => $usesrname,
                'message' => __('phone number is wrong!', 'login-with-phone-number')
            ]);
            die();
        }









/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // global $idehweb_lwp;

    // $_GET['shakti']="khali";

    // $idehweb_lwp->lwp_ajax_login();
		

}




function phone_number_exist($phone_number)
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




function generate_username($defU = '')
{
    $options = get_option('idehweb_lwp_settings');
    if (!isset($options['idehweb_default_username'])) $options['idehweb_default_username'] = 'user';
    if (!isset($options['idehweb_use_phone_number_for_username'])) $options['idehweb_use_phone_number_for_username'] = '0';
    if ($options['idehweb_use_phone_number_for_username'] == '0') {
        $ulogin = $options['idehweb_default_username'];

    } else {
        $ulogin = $defU;
    }

    // make user_login unique so WP will not return error
    $check = username_exists($ulogin);
    if (!empty($check)) {
        $suffix = 2;
        while (!empty($check)) {
            $alt_ulogin = $ulogin . '-' . $suffix;
            $check = username_exists($alt_ulogin);
            $suffix++;
        }
        $ulogin = $alt_ulogin;
    }

    return $ulogin;
}






function lwp_generate_token($user_id, $contact, $send_email = false, $method = '')
{                      
    
    error_log(print_r("3333333333333333333333333333333333333333333333333333333333333333333333333333333333", true));

    $six_digit_random_number = mt_rand(100000, 999999);
    update_user_meta($user_id, 'activation_code', $six_digit_random_number);
    if ($send_email) {
        $wp_mail = wp_mail($contact, 'activation code', __('your activation code: ', 'login-with-phone-number') . $six_digit_random_number);
        return $wp_mail;
    } else {
        error_log(print_r("444444444444444444444444444444444444444444444444444444444444444444444444444", true));

        return $this->send_sms($contact, $six_digit_random_number, $method);
    }
}




function send_sms($phone_number, $code, $method)
{        error_log(print_r("55555555555555555555555555555555555555555555555555555555555555", true));

    $options = get_option('idehweb_lwp_settings');
    if (!isset($options['idehweb_use_custom_gateway'])) $options['idehweb_use_custom_gateway'] = '1';
    if (!isset($options['idehweb_default_gateways'])) $options['idehweb_default_gateways'] = ['firebase'];
    if ($options['idehweb_use_custom_gateway'] == '1') {
        error_log(print_r($options, true));

        error_log(print_r("777777777777777777777777777777777777777777777777777777777", true));
        if (!in_array($method, $options['idehweb_default_gateways'])) {
            error_log(print_r("88888888888888888888888888888888888888888888888888888888888888", true));

            return false;
        }
        if ($method == 'custom') {
            $custom = new Shakti_SMS();
            error_log(print_r("+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++", $code));

            // return $custom->lwp_send_sms($phone_number, $code);
        } else {
            error_log(print_r("99999999999999999999999999999999999999999999999999999999999999999999999", true));
            error_log(print_r($code, true));
            error_log(print_r($phone_number, true));
            error_log(print_r($method, true));


//                echo 'lwp_send_sms_' . $method;
//                echo $phone_number;
//                echo $code;
            do_action('lwp_send_sms_' . $method, $phone_number, $code);
            error_log(print_r("ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ", true));

//                return true;
        }
    } else {
//        $smsUrl = "https://zoomiroom.idehweb.com/customer/sms/" . $options['idehweb_token'] . "/" . $phone_number . "/" . $code;
        $response = wp_safe_remote_post("https://zoomiroom.idehweb.com/customer/sms/", [
            'timeout' => 60,
            'redirection' => 1,
            'blocking' => true,
            'headers' => array('Content-Type' => 'application/json',
                'token' => $options['idehweb_token']),
            'body' => wp_json_encode([
                'phoneNumber' => $phone_number,
                'message' => $code
            ])
        ]);
        $body = wp_remote_retrieve_body($response);
        return $this->esc_from_server($body);
    }
//        $response = wp_remote_get($smsUrl);
//        wp_remote_retrieve_body($response);

}




function generate_nickname()
{
    $options = get_option('idehweb_lwp_settings');
    if (!isset($options['idehweb_default_nickname'])) $options['idehweb_default_nickname'] = 'user';


    return $options['idehweb_default_nickname'];
}


function esc_from_server($body)
{
//        return json_decode(json_encode($body));
//        return wp_send_json($body);

}

};


?>