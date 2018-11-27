<?php

class getVolumeAnalysisController extends ajaxController {
    protected $id_project;

    public function __construct() {

        parent::__construct();

        $filterArgs = array(
                'pid'       => array( 'filter' => FILTER_SANITIZE_NUMBER_INT ),
                'ppassword' => array(
                        'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
                ),
                'jpassword' => array(
                        'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
                ),
        );

        $__postInput = filter_input_array( INPUT_POST, $filterArgs );

        $this->id_project = $__postInput[ 'pid' ];
        $this->ppassword  = $__postInput[ 'ppassword' ];
        $this->jpassword  = $__postInput[ 'jpassword' ];

    }

    public function doAction() {

        if ( empty( $this->id_project ) ) {
            $this->result[ 'errors' ] = array( -1, "No id project provided" );
            return -1;
        }

        $_project_data = getProjectJobData( $this->id_project );

        $passCheck = new AjaxPasswordCheck();
        $access    = $passCheck->grantProjectAccess( $_project_data, $this->ppassword ) || $passCheck->grantProjectJobAccessOnJobPass( $_project_data, null, $this->jpassword );

        if ( !$access ) {
            $this->result[ 'errors' ] = array( -10, "Wrong Password. Access denied" );
            return -1;
        }

        $analysisStatus = new Analysis_WEBStatus( $_project_data, $this->featureSet );
        $this->result = $analysisStatus->fetchData()->getResult();

        if (isset($this->result['data']['summary']['STATUS']) && $this->result['data']['summary']['STATUS'] === 'DONE') {
            $data = $this->result['data'];
            $pretranslateData = array_pop(\Jobs_JobDao::getPretranslateData($data['job_id']));
             if ($pretranslateData['start_tm_pretranslate'] || $pretranslateData['start_mt_pretranslate']) {
                 $jobData = array_pop(Jobs_JobDao::getById($data['job_id']));
                 $mtSystem = array_pop(Jobs_JobDao::getMtSystem($data['project_id']));

                 WorkerClient::init( new AMQHandler() );
                 $pretranslateStruct = new \Pretranslate\PretranslateStruct();
                 $pretranslateStruct->id = $data['job_id'];
                 $pretranslateStruct->password = $data['job_password'];
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
                 $pretranslateStruct->start();
                 $rowCount = \Jobs_JobDao::removeStartPretranslate($pretranslateStruct->id);
                 $this->result['data']['summary']['STATUS'] = 'PRETRANSLATING';
             } else if ($pretranslateData['tm_pretranslate'] || $pretranslateData['mt_pretranslate']) {
                 $this->result['data']['summary']['STATUS'] = 'PRETRANSLATING';
                 $this->log_text('Still pretranslating.');
             }
        }
    }

}

