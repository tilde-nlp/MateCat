<?php

class pretranslateController extends ajaxController {

    private $useTm;
    private $useMt;
    private $projectId;
    private $password;
    private $mtSystem;

    public function __construct()
    {
        $filterArgs = [
            'useTm'          => [ 'filter' => FILTER_VALIDATE_INT ],
            'useMt'          => [ 'filter' => FILTER_VALIDATE_INT ],
            'projectId'          => [ 'filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_SCALAR ],
            'mtSystem'  => [ 'filter' => FILTER_SANITIZE_STRING],
            'projectPassword'   => array (
                'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
            )
        ];
        $__postInput = filter_input_array( INPUT_POST, $filterArgs );
        $this->id = $this->getJobIdFromProjectId($__postInput['projectId']);
        $this->password = $this->getJobPasswordFromProjectId($__postInput['projectId']);
        $this->useTm = intval($__postInput['useTm']) > 0;
        $this->useMt = intval($__postInput['useMt']) > 0;
        $this->mtSystem = $__postInput[ 'mtSystem' ];
    }

    public function doAction() {
        $jobData = Jobs_JobDao::getById($this->id);
        $jobData = array_pop($jobData);

        if ($this->useTm || $this->useMt) {

            WorkerClient::init( new AMQHandler() );
            $pretranslateStruct = new \Pretranslate\PretranslateStruct();
            $pretranslateStruct->id = $this->id;
            $pretranslateStruct->password = $this->password;
            $pretranslateStruct->job_first_segment = $jobData->job_first_segment;
            $pretranslateStruct->job_last_segment = $jobData->job_last_segment;
            $pretranslateStruct->useTm = $this->useTm;
            $pretranslateStruct->useMt = $this->useMt;
            $pretranslateStruct->source = $jobData->source;
            $pretranslateStruct->target = $jobData->target;
            $pretranslateStruct->mtSystem = $this->mtSystem;
            $pretranslateStruct->jwtToken = AuthCookie::getToken();
            $pretranslateStruct->jwtRefreshToken = AuthCookie::getRefreshToken();
            $pretranslateStruct->uid = AuthCookie::getCredentials()['uid'];
            $pretranslateStruct->projectId = $jobData->id_project;
            $pretranslateStruct->start();
            $rowCount = \Jobs_JobDao::setPretranslating($pretranslateStruct->id, $this->useTm ? 1 : 0, $this->useMt ? 1 : 0);
        }

        $this->result = ['status' => 'ok'];
    }
}
