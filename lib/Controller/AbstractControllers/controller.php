<?php
/**
 * Created by PhpStorm.
 * Date: 27/01/14
 * Time: 18.57
 *
 */

use AbstractControllers\IController;

/**
 * Abstract Class controller
 */
abstract class controller implements IController {

    protected $model;
    protected $userRole = TmKeyManagement_Filter::ROLE_TRANSLATOR;

    private static $requestToClassMap = [
        'GET' => [
            'profile' => 'getProfileController',
        ],
        'POST' => [
            'files' => 'getProjectsController'
        ]
    ];

    /**
     * @var Users_UserStruct
     */
    protected $user;

    protected $uid;
    protected $userIsLogged = false;

    /**
     * @var FeatureSet
     */
    protected $featureSet;

    /**
     * @return FeatureSet
     */
    public function getFeatureSet() {
        return $this->featureSet;
    }

    /**
     * @param FeatureSet $featuresSet
     *
     * @return $this
     */
    public function setFeatureSet( FeatureSet $featuresSet ) {
        $this->featureSet = $featuresSet;

        return $this;
    }

    /**
     * @return Users_UserStruct
     */
    public function getUser() {
        return $this->user;
    }

    public function userIsLogged(){
        return $this->userIsLogged;
    }

    private function log($data) {
        file_put_contents('/var/tmp/worker.log', var_export($data, true), FILE_APPEND);
        file_put_contents('/var/tmp/worker.log', "\n", FILE_APPEND);
    }

    /**
     * Controllers Factory
     *
     * Initialize the Controller Instance and route the
     * API Calls to the right Controller
     *
     * @return mixed
     */
    public static function getInstance() {
        AuthCookie::checkAccess();
                
        $requestPath = ltrim(strtolower(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), '/');
        $method = strtoupper($_SERVER['REQUEST_METHOD']);

        $handlerClassName = self::getHandlerClassName($requestPath, $method);

        //Put here all actions we want to be performed by ALL controllers
        require_once INIT::$MODEL_ROOT . '/queries.php';

        return new $handlerClassName();
    }

    private static function getHandlerClassName($request, $method) {
        try {
            $className = self::$requestToClassMap[$method][$request];
        } catch (Throwable $e) {
            $className = null;
        }

        if ($className == null || empty($className)) {
            header('HTTP/1.1 404 Not Found');
            die();
        }

        return $className;
    }

    /**
     * When Called it perform the controller action to retrieve/manipulate data
     *
     * @return mixed
     */
    abstract function doAction();

    /**
     * Called to get the result values in the right format
     *
     * @return mixed
     */
    abstract function finalize();

    /**
     * Set No Cache headers
     *
     */
    protected function nocache() {
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    public function sessionStart(){
        Bootstrap::sessionStart();
    }

    /**
     * Explicitly disable sessions for ajax call
     *
     * Sessions enabled on INIT Class
     *
     */
    public function disableSessions(){
        Bootstrap::sessionClose();
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    public function setUserCredentials() {

        $this->user        = new Users_UserStruct();
        $username_from_cookie = AuthCookie::getCredentials();

        $this->user->uid   = $username_from_cookie['uid'];
        $this->user->email = $username_from_cookie['username'];

        try {

            $userDao    = new Users_UserDao( Database::obtain() );
            $loggedUser = $userDao->setCacheTTL( 0 )->read( $this->user )[ 0 ]; // one hour cache
            $this->userIsLogged = (
                    !empty( $loggedUser->uid ) &&
                    !empty( $loggedUser->email ) &&
                    !empty( $loggedUser->first_name ) &&
                    !empty( $loggedUser->last_name )
            );

        } catch ( Exception $e ) {
            Log::doLog( 'User not logged.' );
        }
        $this->user = ( $this->userIsLogged ? $loggedUser : $this->user );

    }

    /**
     *  Try to get user name from cookie if it is not present and put it in session.
     *
     */
    protected function _setUserFromAuthCookie() {
        $username_from_cookie = AuthCookie::getCredentials();
        if ( $username_from_cookie ) {
            $_SESSION[ 'cid' ] = $username_from_cookie['username'];
            $_SESSION[ 'uid' ] = $username_from_cookie['uid'];
        }
    }

    public function readLoginInfo() {
        $this->_setUserFromAuthCookie();
        $this->setUserCredentials();
    }

    /**
     * isLoggedIn
     *
     * @return bool
     */
    public function isLoggedIn() {
        return $this->userIsLogged;
    }

}
