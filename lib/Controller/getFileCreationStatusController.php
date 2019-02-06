<?php

use API\V2\Exceptions\AuthorizationError;;
use Exception;
use Exceptions\NotFoundException;
use ProjectQueue\Queue;
use Projects_ProjectDao;

class getFileCreationStatusController extends ajaxController {

    private $project_id;
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
        $this->project_id = $postInput['projectId'];
        $this->password = $postInput['projectPassword'];
    }

    public function doAction() {
        $result = Queue::getPublishedResults( $this->project_id );

        $this->result = [];
        $this->result['projectId'] = intval($this->project_id);
        $projectData = getProjectJobData( $this->project_id );
        $projectData = array_pop($projectData);
        $this->result['jobId'] = intval($projectData['jid']);
        $this->result['jobPassword'] = $projectData['jpassword'];
        $this->result['projectName'] = null;

        if ( empty( $result ) ) {
                $this->result['status'] = 'WAITING';
                return;
        } elseif ( !empty( $result ) && !empty( $result[ 'errors' ] ) ){
            foreach( $result[ 'errors' ] as $error ){
                $this->result['errors'][] = $error[ 'message' ];
            }
            return;
        } else {
            try {
                $project = Projects_ProjectDao::findByIdAndPassword($this->project_id, $this->password ) ;
            } catch( NotFoundException $e ) {
                throw new AuthorizationError( 'Not Authorized.' );
            }
            $this->result['projectName'] = $project->name;
            $featureSet = $project->getFeatures();
            $result = $featureSet->filter('filterCreationStatus', $result, $project);

            if ( empty( $result ) ) {
                $this->result['status'] = 'WAITING';
                return;
            }
            else {
                $this->result['status'] = 'READY';
            }
        }
    }
}