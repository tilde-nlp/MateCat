<?php

class pretranslateController {

    private $useTm;
    private $useMt;
    private $id;
    private $password;
    private $mtSystem;

    public function __construct()
    {
        $filterArgs = [
            'use_tm'          => [ 'filter' => FILTER_VALIDATE_INT ],
            'use_mt'          => [ 'filter' => FILTER_VALIDATE_INT ],
            'id'          => [ 'filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_SCALAR ],
            'mt_system'  => [ 'filter' => FILTER_SANITIZE_STRING],
            'password'   => array (
                'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
            )
        ];
        $__postInput = filter_input_array( INPUT_POST, $filterArgs );
        $this->id = $__postInput['id'];
        $this->password = $__postInput['password'];
        $this->useTm = intval($__postInput['use_tm']) > 0;
        $this->useMt = intval($__postInput['use_mt']) > 0;
        $this->mtSystem = $__postInput[ 'mt_system' ];
    }

    public function doAction() {
        set_time_limit ( 3600 );
        $jobData = array_pop(Jobs_JobDao::getById($this->id));

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
            $pretranslateStruct->start();
        }

        echo json_encode([]);
    }

    public function finalize() {}
}
