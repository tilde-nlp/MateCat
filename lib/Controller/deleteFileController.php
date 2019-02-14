<?php

class deleteFileController extends ajaxController {

    private $project;
    private $job_id;
    private $password;

    public function __construct() {
        parent::__construct();
        
        $filterArgs = array(
                'projectId' => array(
                        'filter' => FILTER_SANITIZE_NUMBER_INT
                ),
                'projectPassword'   => array(
                        'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
                ),
        );

        $postInput = filter_input_array( INPUT_POST, $filterArgs );

        $this->project = Projects_ProjectDao::findByIdAndPassword(
            $postInput['projectId'],
            $postInput['projectPassword']
        );

        if ( !$this->project ) {
            throw new NotFoundException();
        }

        $projectData = getProjectJobData( $postInput['projectId'] );
        $projectData = array_pop($projectData);

        $this->job_id = $projectData['jid'];
        $this->password   = $projectData['jpassword'];
    }

   public function doAction() {
        updateJobsStatus( 'job', $this->job_id, 'cancelled', $this->password );
        $this->result = [];
        $this->result[ 'status' ] = 'OK';
    }

}
