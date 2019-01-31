<?php

use AuthCookie;
class saveSettingsForProjectController extends ajaxController {

    private $memory_id;
    private $read;
    private $write;
    private $projectId;

    public function __construct()
    {
        $filterArgs = [
            'id'          => [ 'filter' => FILTER_SANITIZE_STRING ],
            'readMemory'   => ['filter' => FILTER_VALIDATE_BOOLEAN],
            'writeMemory'   => ['filter' => FILTER_VALIDATE_BOOLEAN],
            'project_id' => [ 'filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_SCALAR ],
        ];
        $__postInput = filter_input_array( INPUT_POST, $filterArgs );
        $this->memory_id = $__postInput['id'];
        $this->read = $__postInput['readMemory'];
        $this->write = $__postInput['writeMemory'];
        $this->projectId = $__postInput['project_id'];
    }

    public function doAction() {
        $JobsDao = new Jobs_JobDao();
        $JobsDao->saveMemorySettingsForProject($this->projectId, $this->memory_id, $this->read, $this->write);

        echo json_encode("OK");
    }

    public function finalize() {}
}