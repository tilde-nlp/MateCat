<?php

use AuthCookie;
class setEditingTimeController extends ajaxController {

    private $projectId;
    private $editingTime;
    private $jobId;

    public function __construct()
    {
        $filterArgs = [
            'projectId'          => [ 'filter' => FILTER_SANITIZE_STRING ],
            'editingTime'          => [ 'filter' => FILTER_VALIDATE_INT ],
        ];
        $__postInput = filter_input_array( INPUT_POST, $filterArgs );

        $this->projectId = $__postInput['projectId'];
        $this->editingTime = $__postInput['editingTime'];

        $this->jobId = $this->getJobIdFromProjectId($this->projectId);
    }

    public function doAction() {
        $JobsDao = new Jobs_JobDao();
        $JobsDao->saveEditingTime($this->jobId, $this->editingTime);

        $this->result = ['status' => 'ok'];
    }
}