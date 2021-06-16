<?php

use DataAccess\ShapelessConcreteStruct;

class commentController extends ajaxController {

    protected $id_segment;

    protected $__postInput = null;

    protected $job ;

    protected $struct ;
    protected $new_record ;
    protected $current_user ;
    protected $project_data ;

    public function __destruct() {
    }

    public function __construct() {
        parent::__construct();

        $filterArgs = array(
            'id_client'   => array( 'filter' => FILTER_SANITIZE_STRING ),
            'segmentId'      => array( 'filter' => FILTER_SANITIZE_NUMBER_INT ),
            'id_job'      => array( 'filter' => FILTER_SANITIZE_NUMBER_INT ),
            'id_segment'  => array( 'filter' => FILTER_SANITIZE_NUMBER_INT ),
            'username'    => array( 'filter' => FILTER_SANITIZE_STRING ),
            'source_page'   => array( 'filter' => FILTER_SANITIZE_NUMBER_INT ),
            'message'     => array( 'filter' => FILTER_UNSAFE_RAW ),
            'first_seg'   => array( 'filter' => FILTER_SANITIZE_NUMBER_INT ),
            'last_seg'    => array( 'filter' => FILTER_SANITIZE_NUMBER_INT ),
            'password'    => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags'  => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
            ),
        );

        $this->__postInput = filter_input_array( INPUT_POST, $filterArgs );
        $this->__postInput['message'] = htmlspecialchars( $this->__postInput['message'] );
        $this->__postInput['id_segment'] = $this->__postInput['segmentId'];
        $this->__postInput['source_page'] = 1;

        $this->__postInput['id_job'] = Jobs_JobDao::getSegmentJobId($this->__postInput['id_segment']);
        $this->__postInput['password'] = Jobs_JobDao::getSegmentJobPassword($this->__postInput['id_segment']);

    }

    public function doAction() {

        $this->job = Jobs_JobDao::getByIdAndPassword( $this->__postInput[ 'id_job' ], $this->__postInput['password'], 60 * 60 * 24 );

        if( empty( $this->job ) ){
            $this->result['errors'][] = array("code" => -10, "message" => "wrong password");
            return;
        }

        $this->readLoginInfo();
        if ( $this->userIsLogged ) {
            $this->loadUser();
        }

        $this->route();
    }

    protected function route() {
        switch( $this->__postInput['_sub'] ) {
        case 'getRange':
            $this->getRange();
            break;
        case 'resolve':
            $this->resolve();
            break;
        case 'create':
            $this->create();
            break;
        default:
            $this->result[ 'errors' ][ ] = array(
                "code" => -127, "message" => "Unable to route action." );
        }
    }

    protected function getRange() {
        $this->struct = new Comments_CommentStruct() ;
        $this->struct->id_job        = $this->__postInput[ 'id_job' ] ;
        $this->struct->first_segment = $this->__postInput[ 'first_seg' ] ;
        $this->struct->last_segment  = $this->__postInput[ 'last_seg' ] ;

        $commentDao = new Comments_CommentDao( Database::obtain() );

        $this->result[ 'data' ][ 'entries' ] = array(
            'comments'    => $commentDao->getCommentsInJob( $this->struct )
        );
        $this->appendUser();
    }

    protected function resolve() {
        $this->prepareCommentData();

        $commentDao = new Comments_CommentDao( Database::obtain() );
        $this->new_record = $commentDao->resolveThread( $this->struct );

        $this->enqueueComment();
        $this->users = $this->resolveUsers();
        $this->result = $this->filterCommentData($this->payload);
    }

    protected function filterCommentData($comment) {
        return [
            'messageType' => $comment['message_type'],
            'fullName' => $comment['full_name'],
            'timestamp' => $comment['timestamp'],
            'message' => $comment['message'],
            'threadId' => $comment['thread_id']
        ];
    }

    protected function appendUser() {
        if ( $this->userIsLogged ) {
            $this->result[ 'data' ][ 'user' ] = array(
                'full_name' => $this->current_user->fullName()
            );
        }
    }

    protected function create() {
        $this->prepareCommentData();

        $commentDao = new Comments_CommentDao( Database::obtain() );
        $this->new_record = $commentDao->saveComment( $this->struct );

        foreach($this->users_mentioned as $user_mentioned){
            $mentioned_comment = $this->prepareMentionCommentData($user_mentioned);
            $commentDao->saveComment($mentioned_comment);
        }

        $this->enqueueComment();
        $this->users = $this->resolveUsers();
        $this->appendUser();
        $this->result = $this->filterCommentData($this->payload);
    }

    protected function prepareCommentData() {
        $this->struct = new Comments_CommentStruct() ;

        $this->struct->id_segment = $this->__postInput[ 'id_segment' ];
        $this->struct->id_job     = $this->__postInput[ 'id_job' ];
        $this->struct->full_name  = $this->current_user->first_name . ' ' . $this->current_user->last_name;
        $this->struct->source_page  = $this->__postInput[ 'source_page' ];
        $this->struct->message    = $this->__postInput[ 'message' ];
        $this->struct->email      = $this->getEmail();
        $this->struct->uid        = $this->getUid();

        $user_mentions = $this->resolveUserMentions();
        $user_team_mentions = $this->resolveTeamMentions();
        $userDao = new Users_UserDao( Database::obtain() );
        $this->users_mentioned_id = array_unique(array_merge($user_mentions, $user_team_mentions));


        $this->users_mentioned = $this->filterUsers($userDao->getByUids( $this->users_mentioned_id ));
    }

    protected function prepareMentionCommentData(Users_UserStruct $user){
        $struct = new Comments_CommentStruct();

        $struct->id_segment   = $this->__postInput[ 'id_segment' ];
        $struct->id_job       = $this->__postInput[ 'id_job' ];
        $struct->full_name    = $user->fullName();
        $struct->source_page  = $this->__postInput[ 'source_page' ];
        $struct->message      = "";
        $struct->message_type = Comments_CommentDao::TYPE_MENTION;
        $struct->email        = $user->getEmail();
        $struct->uid          = $user->getUid();
        return $struct;
    }

    protected function sendEmail() {
        // TODO: fix this, replace the need for referer with a server side
        // function to build translate or revise paths based on job and
        // segmnt ids.

        if (empty($_SERVER['HTTP_REFERER'])) {
            Log::doLog('Skipping email due to missing referrer link');
            return;
        }
        @list($url, $anchor) = explode('#', $_SERVER['HTTP_REFERER']);
        $url .= '#' . $this->struct->id_segment ;
        Log::doLog($url);

        $project_data = $this->projectData();

        foreach($this->users_mentioned as $user_mentioned) {
            $email = new \Email\CommentMentionEmail($user_mentioned, $this->struct, $url, $project_data[0], $this->job);
            $email->send();
        }

        foreach($this->users as $user) {
            if($this->struct->message_type == Comments_CommentDao::TYPE_RESOLVE){
                $email = new \Email\CommentResolveEmail($user, $this->struct, $url, $project_data[0], $this->job);
            } else{
                $email = new \Email\CommentEmail($user, $this->struct, $url, $project_data[0], $this->job );
            }

            $email->send();
        }
    }

    protected function projectData() {
        if ( $this->project_data == null ) {

            // FIXME: this is not optimal, should return just one record, not an array of records.
            /**
             * @var $projectData ShapelessConcreteStruct[]
             */
            $this->project_data = ( new \Projects_ProjectDao() )->setCacheTTL( 60 * 60 )->getProjectData( $this->job[ 'id_project' ] );

        }

        return $this->project_data ;
    }

    protected function resolveUsers() {
        $commentDao = new Comments_CommentDao( Database::obtain() );
        $result = $commentDao->getThreadContributorUids( $this->struct );

        $userDao = new Users_UserDao( Database::obtain() );
        $users = $userDao->getByUids( $result );
        $userDao->setCacheTTL( 60 * 60 * 24 );
        $owner = $userDao->getProjectOwner( $this->job['id'] );

        if ( !empty( $owner->uid ) && !empty( $owner->email ) ) {
            array_push( $users, $owner );
        }

        $userDao->setCacheTTL( 60 * 10 );
        $assignee = $userDao->getProjectAssignee( $this->job[ 'id_project' ] );
        if ( !empty( $assignee->uid ) && !empty( $assignee->email ) ) {
            array_push( $users, $assignee );
        }

        return $this->filterUsers($users, $this->users_mentioned_id);

    }


    protected function resolveUserMentions() {
        return Comments_CommentDao::getUsersIdFromContent($this->struct->message);
    }

    protected function resolveTeamMentions() {
        $users = [];

        if ( strstr( $this->struct->message, "{@team@}" ) ) {
            $project =  $this->job->getProject();
            $memberships = ( new \Teams\MembershipDao() )->setCacheTTL( 60 * 60 * 24 )->getMemberListByTeamId( $project->id_team, false );
            foreach ( $memberships as $membership ) {
                $users[] = $membership->uid;
            }
        }

        return $users;
    }

    protected function filterUsers($users, $uidSentList = array()){
        $userIsLogged = $this->userIsLogged ;
        $current_uid = $this->current_user->uid ;

        // find deep duplicates
        $users = array_filter($users, function($item) use (  $userIsLogged, $current_uid, &$uidSentList ) {
            if ( $userIsLogged && $current_uid == $item->uid ) {
                return false;
            }

            // find deep duplicates
            if ( array_search( $item->uid, $uidSentList ) !== false ) {
                return false;
            }
            $uidSentList[] = $item->uid;
            return true ;

        });
        return $users;
    }

    protected function getEmail() {
        if ( $this->userIsLogged ) {
            return $this->current_user->email ;
        } else {
            return null;
        }
    }

    protected function getUid() {
        if ( $this->userIsLogged ) {
            return $this->current_user->uid;
        } else {
            return null;
        }
    }

    protected function isOwner() {
        return $this->userIsLogged &&
            $this->current_user->email == $this->job['owner'] ;
    }

    protected function loadUser() {
        $userStruct = new Users_UserStruct();
        $userStruct->uid = $this->user->uid;

        $userDao = new Users_UserDao( Database::obtain() ) ;
        $result = $userDao->read( $userStruct );

        if ( empty($result) ) {
            throw new Exception( "User not found by UID." );
        }

        $this->current_user = $result[0] ;
    }

    protected function enqueueComment() {
        $this->payload = array(
            'message_type'   => $this->new_record->message_type,
            'message'        => $this->new_record->message,
            'id_segment'     => $this->new_record->id_segment,
            'full_name'      => $this->new_record->full_name,
            'email'          => $this->new_record->email,
            'source_page'    => $this->new_record->source_page,
            'formatted_date' => $this->new_record->getFormattedDate(),
            'thread_id'      => $this->new_record->thread_id,
            'timestamp'      => (int) $this->new_record->timestamp,
        ) ;

        $message = json_encode( array(
            '_type' => 'comment',
            'data' => array(
                'id_job'    => $this->__postInput['id_job'],
                'passwords'  => $this->getProjectPasswords(),
                'id_client' => $this->__postInput['id_client'],
                'payload'   => $this->payload
            )
        ));

        $stomp = new Stomp( INIT::$QUEUE_BROKER_ADDRESS );
        $stomp->connect();
        $stomp->send( INIT::$SSE_NOTIFICATIONS_QUEUE_NAME,
            $message,
            array( 'persistent' => 'true' )
        );
    }

    protected function getProjectPasswords() {
        $pws = array();
        foreach($this->projectData() as $chunk) {
            array_push( $pws, $chunk['jpassword'] );
        }
        return $pws;
    }

}

?>
