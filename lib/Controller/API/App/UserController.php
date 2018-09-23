<?php

namespace API\App;

use API\App\Json\ConnectedService;
use API\V2\Json\Team;
use API\V2\Json\User;

use API\V2\Validators\LoginValidator;
use ConnectedServices\ConnectedServiceDao;
use Exceptions\NotFoundError;
use TeamModel;
use Teams\MembershipDao;
use Teams\TeamStruct;
use Users_UserDao;
use Utils;

class UserController extends AbstractStatefulKleinController {

    /**
     * @var \Users_UserStruct
     */
    protected $user;
    protected $connectedServices;

    public function show() {
        $metadata = $this->user->getMetadataAsKeyValue();

        $membersDao = new MembershipDao();
        $userTeams  = array_map(
                function ( $team ) use ( $membersDao ) {
                    $teamModel = new TeamModel( $team );
                    $teamModel->updateMembersProjectsCount();

                    /** @var $team TeamStruct */
                    return $team;
                },
                $membersDao->findUserTeams( $this->user )
        );

        // TODO Check header for auth token


        // TODO: move this into a formatter class
        $this->response->json( [
                'user'               => User::renderItem( $this->user ),
                'connected_services' => ( new ConnectedService( $this->connectedServices ) )->render(),

            // TODO: this is likely to be unsafe to be passed here without a whitelist.
                'metadata'           => ( empty( $metadata ) ? null : $metadata ),

                'teams' => ( new Team() )->render( $userTeams )

        ] );
    }

    public function updatePassword() {
        $new_password = filter_var( $this->request->param( 'password' ), FILTER_SANITIZE_STRING );

        \Users_UserValidator::validatePassword( $new_password );

        $this->user->pass = Utils::encryptPass( $new_password, $this->user->salt );
        \Users_UserDao::updateStruct( $this->user, [ 'fields' => [ 'pass' ] ] );
        ( new Users_UserDao )->destroyCacheByEmail( $this->user->email );
        ( new Users_UserDao )->destroyCacheByUid( $this->user->uid );

        $this->response->code( 200 );
    }

    protected function afterConstruct() {
        $loginValidator = new LoginValidator( $this );
        $loginValidator->onSuccess( function () {
            $this->__findConnectedServices();
        } );
        $this->appendValidator( $loginValidator );
    }

    private function __findConnectedServices() {
        $dao      = new ConnectedServiceDao();
        $services = $dao->findServicesByUser( $this->user );
        if ( !empty( $services ) ) {
            $this->connectedServices = $services;
        }

    }

}