<?php

use AuthCookie;

class setCurrentSegmentController extends ajaxController {

    private $segmentId;

    public function __construct() {

        parent::__construct();

        $filterArgs = array(
            'segmentId' => array( 'filter' => FILTER_SANITIZE_NUMBER_INT ),
        );

        $__postInput = filter_input_array( INPUT_POST, $filterArgs );

        $this->segmentId = $__postInput[ 'segmentId' ];
    }

    public function doAction() {

        if ( empty( $this->segmentId ) ) {
            $this->result[ 'errors' ][ ] = array( "code" => -1, "message" => "missing segment id" );
        }

        if ( !empty( $this->result[ 'errors' ] ) ) {
            //no action on errors
            return;
        }

        $user = AuthCookie::getCredentials();
        $JobsDao = new Jobs_JobDao();
        $JobsDao->setActiveSegment($user['uid'], $this->segmentId);

        $this->result = [];
        $this->result['status'] = 'ok';
    }
}