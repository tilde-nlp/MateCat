<?php

namespace Users ;

use Database;
use Teams\TeamDao;
use Users_UserStruct ;
use Utils ;
use Users_UserDao;

class JwtSignup {

    /**
     * @var Users_UserStruct
     */
    protected $user ;

    public function __construct( $jwtId ) {

        $this->user = new Users_UserStruct( array('email' => $jwtId, 'first_name' => 'jwt_user', 'last_name' => 'jwt_user') );
    }

    public function process() {
        $this->__prepareNewUser() ;
        $this->user->uid = Users_UserDao::insertStruct( $this->user, array('raise' => TRUE ) );

        Database::obtain()->begin();
        ( new TeamDao() )->createPersonalTeam( $this->user ) ;
        Database::obtain()->commit();
    }

    private function __prepareNewUser() {
        $this->user->create_date = Utils::mysqlTimestamp( time() );
        $this->user->salt = Utils::randomString() ;
        $this->user->pass = Utils::encryptPass( $this->params['password'], $this->user->salt ) ;

        $this->user->confirmation_token = Utils::randomString() ;
        $this->user->confirmation_token_created_at = Utils::mysqlTimestamp( time() );
    }
}