<?php

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Hmac\Sha256 as HmacSha256;
use Lcobucci\JWT\Signer\Rsa\Sha256 as RsaSha256;
use Lcobucci\JWT\ValidationData;
use Users\JwtSignup;
use Users_UserDao;
use Users_UserStruct;

class AuthCookie {

    public static function getCredentials() {
        self::checkAccess();
        $payload = self::getDataFromHeader();
        return $payload;
    }

    public static function checkAccess() {
        try {
            $jwt = self::getToken();

            $parsedToken = (new Parser())->parse((string) $jwt);
            $signer = new HmacSha256();
            $signerKeyCloak = new RsaSha256();
            $data = new ValidationData();

            if (!$parsedToken->verify($signer, INIT::$JWT_KEY) && !$parsedToken->verify($signerKeyCloak, new Key("-----BEGIN PUBLIC KEY-----\n".INIT::$JWT_KEY_KEYCLOAK."\n-----END PUBLIC KEY-----"))) {
                header("HTTP/1.1 401 Unauthorized");
                die();
            }

            if (!INIT::$DEV_MODE && (!$parsedToken->validate($data) || $parsedToken->isExpired())) {
                header("HTTP/1.1 401 Unauthorized");
                die();
            }
        } catch (Throwable $e) {
            header("HTTP/1.1 401 Unauthorized");
            die();
        }
    }

    public static function getCredentialsFromCookie($jwt) {
        return AuthCookie::getData($jwt);
    }

    public static function getToken() {
        $jwtToken = '';

        $headers = getallheaders();
        if ( isset( $headers['Authorization'] ) and !empty( $headers['Authorization'] ) ) {
            $jwtToken = str_replace('Bearer ', '', $headers['Authorization']);
        }
        
        if (empty($jwtToken)) {
            $jwtToken = $_POST['jwt'];
        }

        if ($jwtToken === '' && INIT::$DEV_MODE) {
            $jwtToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJ2YWx0ZXJzLnNpY3NAdGlsZGUubHYiLCJncnAiOiJ0ZXN0ZXJUaWxkZUBnbWFpbC5jb20iLCJnaXZlbl9uYW1lIjoiVmFsdGVycyIsInJvbGVzIjpbImFkbSIsInRtYWRtIl0sImp0aSI6IjRhMGY2MmUwLTBhYjMtNDI2MC05NmI0LTZkMmI1NjdmNGM0ZiIsIm5iZiI6MTUzMzYzMDI5MCwiZXhwIjoxNTM4OTAwNjkwLCJpc3MiOiJMZXRzTVRTZXJ2aWNlIn0.0yqkUSkSaxvHNnVzG83in2Qq_tunTv2iXm1kcdnhClU';
        }

        return $jwtToken;
    }

    public static function getRefreshToken() {
        $refreshToken = '';

        $headers = getallheaders();
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

    private static function getData($jwt) {
        try {
            $parsedToken = (new Parser())->parse((string) $jwt);
            $group = "";
            $userId = "";

            if ($parsedToken->getClaim('iss') == INIT::$JWT_ISSUER_KEYCLOAK) { // iss: KeyCloak url
                $userId = $parsedToken->getClaim('email');
                $group = end(explode("/", $parsedToken->getClaim('membership')[0]));
            }
            else { // iss: LetsMTService
                $userId = $parsedToken->getClaim('sub');
                $group = $parsedToken->getClaim('grp');
            }

            $jwtId = $userId . ':-:' . $group;
            $nameArray = explode(' ', $parsedToken->getClaim('given_name'), 2);
            $firstName = isset($nameArray[0]) ? $nameArray[0] : '';
            $lastName = isset($nameArray[1]) ? $nameArray[1] : '';
            if ($firstName == '' && $lastName == '') {
                $firstName = $userId;
            }
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
            die();
        }
    }

    protected static function log($data) {
        file_put_contents('/var/tmp/cookie.log', var_export($data, true), FILE_APPEND);
        file_put_contents('/var/tmp/cookie.log', "\n", FILE_APPEND);
    }

}

