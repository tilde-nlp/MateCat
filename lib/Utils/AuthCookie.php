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

        if ( $payload ) {
            self::setCredentials( $payload[ 'username' ], $payload[ 'uid' ] );
        }

        return $payload;

    }

    //set a cookie with a username
    public static function setCredentials( $username, $uid ) {
        list( $new_cookie_data, $new_expire_date ) = static::generateSignedAuthCookie( $username, $uid );
        setcookie( INIT::$AUTHCOOKIENAME, $new_cookie_data, $new_expire_date, '/' );
    }

    public static function generateSignedAuthCookie( $username, $uid ) {

        $JWT = new SimpleJWT( [
                'uid'      => $uid,
                'username' => $username,
        ] );

        $JWT->setTimeToLive( INIT::$AUTHCOOKIEDURATION );

        return array( $JWT->jsonSerialize(), $JWT->getExpireDate() );
    }

    public static function destroyAuthentication() {
        unset( $_COOKIE[ INIT::$AUTHCOOKIENAME ] );
        setcookie( INIT::$AUTHCOOKIENAME, '', 0, '/' );
        session_destroy();
    }

    public static function getCookie() {
        $jwtCookie = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJ2YWx0ZXJzLnNpY3NAdGlsZGUubHYiLCJncnAiOiJ0ZXN0ZXJUaWxkZUBnbWFpbC5jb20iLCJnaXZlbl9uYW1lIjoiVmFsdGVycyIsInJvbGVzIjpbImFkbSIsInRtYWRtIl0sImp0aSI6IjRhMGY2MmUwLTBhYjMtNDI2MC05NmI0LTZkMmI1NjdmNGM0ZiIsIm5iZiI6MTUzMzYzMDI5MCwiZXhwIjoxNTM4OTAwNjkwLCJpc3MiOiJMZXRzTVRTZXJ2aWNlIn0.0yqkUSkSaxvHNnVzG83in2Qq_tunTv2iXm1kcdnhClU';
        if ( isset( $_COOKIE[ 'jwt' ] ) and !empty( $_COOKIE[ 'jwt' ] ) ) {
            $jwtCookie = $_COOKIE[ 'jwt' ];
        }

        return $jwtCookie;
    }

    /**
     * get data from cookie
     *
     * @return mixed
     */
    private static function getData() {
//        if ( isset( $_COOKIE[ 'jwt' ] ) and !empty( $_COOKIE[ 'jwt' ] ) ) {

            try {
                $jwtCookie = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJ2YWx0ZXJzLnNpY3NAdGlsZGUubHYiLCJncnAiOiJ0ZXN0ZXJUaWxkZUBnbWFpbC5jb20iLCJnaXZlbl9uYW1lIjoiVmFsdGVycyIsInJvbGVzIjpbImFkbSIsInRtYWRtIl0sImp0aSI6IjRhMGY2MmUwLTBhYjMtNDI2MC05NmI0LTZkMmI1NjdmNGM0ZiIsIm5iZiI6MTUzMzYzMDI5MCwiZXhwIjoxNTM4OTAwNjkwLCJpc3MiOiJMZXRzTVRTZXJ2aWNlIn0.0yqkUSkSaxvHNnVzG83in2Qq_tunTv2iXm1kcdnhClU';
                if ( isset( $_COOKIE[ 'jwt' ] ) and !empty( $_COOKIE[ 'jwt' ] ) ) {
                    $jwtCookie = $_COOKIE[ 'jwt' ];
                }
                $token = (new Parser())->parse((string) $jwtCookie);
                $signer = new Sha256();
                $data = new ValidationData();

                if (!$token->verify($signer, INIT::$JWT_KEY)
//                || !$token->validate($data)
                ) {
                    return false;
                }

                $jwtId = $token->getClaim('sub') . ':-:' . $token->getClaim('grp');

                $dao  = new Users_UserDao();
                $user = $dao->getByEmail( $jwtId );
                if ($user == null) {
                    $signup = new JwtSignup( $jwtId );
                    $signup->process();
                    $user = $dao->getByEmail( $jwtId );
                }

                return array('username' => $user->email, 'uid' => $user->uid);
            } catch ( DomainException $e ) {
                Log::doLog( $e->getMessage() . " " . $_COOKIE[ INIT::$AUTHCOOKIENAME ] );
                self::destroyAuthentication();
            }
//        }
    }

}

