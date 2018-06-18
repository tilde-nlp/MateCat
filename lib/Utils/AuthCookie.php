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

    /**
     * get data from cookie
     *
     * @return mixed
     */
    private static function getData() {
        if ( isset( $_COOKIE[ 'jwt' ] ) and !empty( $_COOKIE[ 'jwt' ] ) ) {

            try {
                $token = (new Parser())->parse((string) $_COOKIE[ 'jwt' ]);
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

                return array('email' => $user->email, 'uid' => $user->uid);
            } catch ( DomainException $e ) {
                Log::doLog( $e->getMessage() . " " . $_COOKIE[ INIT::$AUTHCOOKIENAME ] );
                self::destroyAuthentication();
            }
        }
    }

}

