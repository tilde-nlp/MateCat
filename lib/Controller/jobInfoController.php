<?php

class jobInfoController extends ajaxController {

    private $id;
    private $password;

    public function __construct()
    {
        $filterArgs = [
            'projectId'          => [ 'filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_SCALAR ],
            'projectPassword'   => array(
                'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
            )
        ];
        $__postInput = filter_input_array( INPUT_POST, $filterArgs );

        $project = Projects_ProjectDao::findByIdAndPassword(
                $__postInput['projectId'],
                $__postInput['projectPassword']
        );

        if ( !$project ) {
            throw new NotFoundException();
        }

        $projectData = getProjectJobData( $__postInput['projectId'] );
        $projectData = array_pop($projectData);

        $this->id = $projectData['jid'];
        $this->password   = $projectData['jpassword'];
    }

    public function doAction() {
        $user = AuthCookie::getCredentials();
        $JobsDao = new Jobs_JobDao();
        $lastSegmentData = $JobsDao->getActiveSegment($user['uid'], $this->id);
        $lastSegmentData = array_pop($lastSegmentData);
        $fileNameData = $JobsDao->getFileName($this->id);
        $fileNameData = array_pop($fileNameData);
        $jobData = Jobs_JobDao::getById($this->id);
        $jobData = array_pop($jobData);
        $mtSystemId = $JobsDao->getMtSystem($jobData->id_project);
        $mtSystemId = array_pop($mtSystemId)['mt_system_id'];
        $result = new \stdClass();
        $result->activeSegmentId = intval($lastSegmentData['segment_id']);
        if ($result->activeSegmentId == null) {
            $result->activeSegmentId = intval($jobData->job_first_segment);
        }
        $result->fileName = $fileNameData['name'];
        $result->source = $jobData['source'];
        $result->target = $jobData['target'];
        $result->editingTime = intval($jobData['editing_time']);
        $result->firstSegment = intval($jobData['job_first_segment']);
        $result->lastSegment = intval($jobData['job_last_segment']);
        $result->tmPretranslate = intval($jobData['tm_pretranslate']);
        $result->mtPretranslate = intval($jobData['mt_pretranslate']);
        $result->mtSystemId = $mtSystemId;
        $result->termBaseUrl = INIT::$TERM_BASE_URL;
        $result->synonymBaseUrl = INIT::$SYNONYM_BASE_URL;

        echo json_encode($result);
    }

    public function finalize() {}
}