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
        $payload = self::getDataFromHeader();
        return $payload;
    }

    public static function getCredentialsFromCookie($jwt) {
        return AuthCookie::getData($jwt);
    }

    public static function getToken() {
        $jwtToken = '';

        $headers = apache_request_headers();
        if ( isset( $headers['Authorization'] ) and !empty( $headers['Authorization'] ) ) {
            $jwtToken = str_replace('Bearer ', '', $headers['Authorization']);
        }

        if ($jwtToken === '' && INIT::$DEV_MODE) {
            $jwtToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJ2YWx0ZXJzLnNpY3NAdGlsZGUubHYiLCJncnAiOiJ0ZXN0ZXJUaWxkZUBnbWFpbC5jb20iLCJnaXZlbl9uYW1lIjoiVmFsdGVycyIsInJvbGVzIjpbImFkbSIsInRtYWRtIl0sImp0aSI6IjRhMGY2MmUwLTBhYjMtNDI2MC05NmI0LTZkMmI1NjdmNGM0ZiIsIm5iZiI6MTUzMzYzMDI5MCwiZXhwIjoxNTM4OTAwNjkwLCJpc3MiOiJMZXRzTVRTZXJ2aWNlIn0.0yqkUSkSaxvHNnVzG83in2Qq_tunTv2iXm1kcdnhClU';
        }

        return $jwtToken;
    }

    public static function getRefreshToken() {
        $refreshToken = '';

        $headers = apache_request_headers();
        if ( isset( $headers['Refresh'] ) and !empty( $headers['Refresh'] ) ) {
            $refreshToken = $headers['Refresh'];
        }

        return $refreshToken;
    }

    /**
     * get data from cookie
     *
     * @return mixed
     */
    private static function getDataFromHeader() {
        $jwtToken = AuthCookie::getToken();
        return AuthCookie::getData($jwtToken);
    }

    private static function getData($jwtToken) {
        try {
            $token = (new Parser())->parse((string) $jwtToken);
            $signer = new Sha256();
            $data = new ValidationData();

            if (!$token->verify($signer, INIT::$JWT_KEY)) {
                header("HTTP/1.1 401 Unauthorized");
                exit;
            }

            if (!INIT::$DEV_MODE && (!$token->validate($data) || $token->isExpired())) {
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

