<?php

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
use Users\JwtSignup;
use Users_UserDao;
use Users_UserStruct;

class AuthCookie {

    //get user name in cookie, if present
    public static function getCredentials() {
        $payload = self::getData();
        return $payload;
    }

    public static function getToken() {
        $jwtToken = '';

        if (INIT::$DEV_MODE) {
            $jwtToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJ2YWx0ZXJzLnNpY3NAdGlsZGUubHYiLCJncnAiOiJ0ZXN0ZXJUaWxkZUBnbWFpbC5jb20iLCJnaXZlbl9uYW1lIjoiVmFsdGVycyIsInJvbGVzIjpbImFkbSIsInRtYWRtIl0sImp0aSI6IjRhMGY2MmUwLTBhYjMtNDI2MC05NmI0LTZkMmI1NjdmNGM0ZiIsIm5iZiI6MTUzMzYzMDI5MCwiZXhwIjoxNTM4OTAwNjkwLCJpc3MiOiJMZXRzTVRTZXJ2aWNlIn0.0yqkUSkSaxvHNnVzG83in2Qq_tunTv2iXm1kcdnhClU';
        }

        $headers = apache_request_headers();
        if ( isset( $headers['Authorization'] ) and !empty( $headers['Authorization'] ) ) {
            $jwtToken = str_replace('Bearer ', '', $headers['Authorization']);
        }

        return $jwtToken;
    }

    /**
     * get data from cookie
     *
     * @return mixed
     */
    private static function getData() {
        try {
            $jwtToken = AuthCookie::getToken();
            $token = (new Parser())->parse((string) $jwtToken);
            $signer = new Sha256();
            $data = new ValidationData();

            if (!$token->verify($signer, INIT::$JWT_KEY)
            || (!$token->validate($data) && !INIT::$DEV_MODE)
            ) {
                header("HTTP/1.1 401 Unauthorized");
                exit;
            }

            $jwtId = $token->getClaim('sub') . ':-:' . $token->getClaim('grp');
            $nameArray = explode(' ', $token->getClaim('given_name'), 2);
            $firstName = isset($nameArray[0]) ? $nameArray[0] : 'jwt_user';
            $lastName = isset($nameArray[1]) ? $nameArray[1] : 'jwt_user';

            $dao  = new Users_UserDao();
            $user = $dao->getByEmail( $jwtId );
            if ($user == null) {
                $signup = new JwtSignup( $jwtId, $firstName, $lastName );
                $signup->process();
                $user = $dao->getByEmail( $jwtId );
            } else {
                Users_UserDao::saveName($user->uid, $firstName, $lastName);
            }

            return array('username' => $user->email, 'uid' => $user->uid);
        } catch ( Exception $e ) {
            header("HTTP/1.1 401 Unauthorized");
            exit;
        }
    }

}

