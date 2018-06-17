<?php

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
use Users\JwtSignup;
use Users_UserDao;
use AuthCookie;
use Users_UserStruct ;

class serveVueController {

    public function doAction() {
        $authorized = false;
        if (!empty($_COOKIE['jwt'])) {
            $authorized = $this->processJwt($_COOKIE['jwt']);
        }
        $config = new \stdClass();
        $config->baseUrl = INIT::$RELATIVE_HOST_NAME;
        $config->googleClientId = INIT::$OAUTH_CLIENT_ID;
        $index = file_get_contents('public/vue_dist/index.html');
        $index = str_replace('HugoCat.main({})', 'HugoCat.main('. json_encode($config) .')', $index);
        $index = str_replace('<baseUrl>', $config->baseUrl, $index);
        echo $index;
    }

    public function finalize() {}

    protected function processJwt($jwt) {
        try {
            $token = (new Parser())->parse((string) $jwt); // Parses from a string
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

            AuthCookie::setCredentials( $user->email, $user->uid );

        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
