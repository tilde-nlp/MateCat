<?php

class saveMtSystemController {

    private $id;
    private $systemId;

    public function __construct()
    {
        $filterArgs = [
            'id'          => [ 'filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_SCALAR ],
            'mt_system_id'   => array('filter' => FILTER_SANITIZE_STRING)
        ];
        $__postInput = filter_input_array( INPUT_POST, $filterArgs );
        $this->id = $__postInput['id'];
        $this->systemId = $__postInput['mt_system_id'];
    }

    public function doAction() {
        $JobsDao = new Jobs_JobDao();
        $JobsDao->saveMtSystem($this->id, $this->systemId);

        echo json_encode("OK");
    }

    public function finalize() {}
}