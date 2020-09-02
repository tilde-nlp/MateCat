<?php

class getVolumeAnalysisController extends ajaxController {
    protected $id_project;
    protected $appId;

    public function __construct() {

        parent::__construct();

        $filterArgs = array(
                'projectId'       => array( 'filter' => FILTER_SANITIZE_NUMBER_INT ),
                'projectPassword' => array(
                        'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH,
                ),
                'appId'  => [ 'filter' => FILTER_SANITIZE_STRING]
        );

        $__postInput = filter_input_array( INPUT_POST, $filterArgs );

        $this->id_project = $__postInput[ 'projectId' ];
        $this->ppassword  = $__postInput[ 'projectPassword' ];
        $this->appId  = $__postInput[ 'appId' ];
    }

    public function doAction() {

        if ( empty( $this->id_project ) ) {
            $this->result[ 'errors' ] = array( -1, "No id project provided" );
            return -1;
        }

        $_project_data = getProjectJobData( $this->id_project );

        $passCheck = new AjaxPasswordCheck();
        $access    = $passCheck->grantProjectAccess( $_project_data, $this->ppassword ) || $passCheck->grantProjectJobAccessOnJobPass( $_project_data, null, null );

        if ( !$access ) {
            $this->result[ 'errors' ] = array( -10, "Wrong Password. Access denied" );
            return -1;
        }

        $analysisStatus = new Analysis_WEBStatus( $_project_data, $this->featureSet );
        $analysisData = $analysisStatus->fetchData()->getResult();
        $this->result = [];
        $this->result['projectId'] = $analysisData['data']['project_id'];
        $this->result['jobId'] = $analysisData['data']['job_id'];
        $this->result['jobPassword'] = $analysisData['data']['job_password'];
        $this->result['status'] = $analysisData['data']['summary']['STATUS'];
        $this->result['wordCount'] = $analysisData['data']['summary']['TOTAL_RAW_WC'];
        $this->result['segmentCount'] = $analysisData['data']['summary']['TOTAL_SEGMENTS'];
        $this->result['segmentsAnalyzed'] = $analysisData['data']['summary']['SEGMENTS_ANALYZED'];

        if (isset($this->result['status']) && $this->result['status'] === 'DONE') {
            $data = $this->result;
            $pretranslateData = \Jobs_JobDao::getPretranslateData($data['jobId']);
            $pretranslateData = array_pop($pretranslateData);
             if ($pretranslateData['start_tm_pretranslate'] || $pretranslateData['start_mt_pretranslate']) {
                 $jobData = array_pop(Jobs_JobDao::getById($data['jobId']));
                 $mtSystem = array_pop(Jobs_JobDao::getMtSystem($data['projectId']));

                 WorkerClient::init( new AMQHandler() );
                 $pretranslateStruct = new \Pretranslate\PretranslateStruct();
                 $pretranslateStruct->id = $data['jobId'];
                 $pretranslateStruct->password = $data['jobPassword'];
                 $pretranslateStruct->job_first_segment = $jobData->job_first_segment;
                 $pretranslateStruct->job_last_segment = $jobData->job_last_segment;
                 $pretranslateStruct->useTm = $pretranslateData['start_tm_pretranslate'];
                 $pretranslateStruct->useMt = $pretranslateData['start_mt_pretranslate'];
                 $pretranslateStruct->source = $jobData->source;
                 $pretranslateStruct->target = $jobData->target;
                 $pretranslateStruct->mtSystem = $mtSystem['mt_system_id'];
                 $pretranslateStruct->jwtToken = AuthCookie::getToken();
                 $pretranslateStruct->jwtRefreshToken = AuthCookie::getRefreshToken();
                 $pretranslateStruct->uid = AuthCookie::getCredentials()['uid'];
                 $pretranslateStruct->projectId = $jobData->id_project;
                 $pretranslateStruct->appId = $jobData->appId;
                 $pretranslateStruct->start();
                 $rowCount = \Jobs_JobDao::removeStartPretranslate($pretranslateStruct->id);
                 $this->result['status'] = 'PRETRANSLATING';
             } else if ($pretranslateData['tm_pretranslate'] || $pretranslateData['mt_pretranslate']) {
                 $this->result['status'] = 'PRETRANSLATING';
             }
        }
    }

}

