<?php

class saveMtSystemController extends ajaxController {

    private $id;
    private $systemId;

    public function __construct()
    {
        $filterArgs = [
            'projectId'          => [ 'filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_SCALAR ],
            'mtSystemId'   => array('filter' => FILTER_SANITIZE_STRING)
        ];
        $__postInput = filter_input_array( INPUT_POST, $filterArgs );
        $this->id = $__postInput['projectId'];
        $this->systemId = $__postInput['mtSystemId'];
    }

    public function doAction() {
        Jobs_JobDao::saveMtSystem($this->id, $this->systemId);

        $this->result = ['status' => 'ok'];
    }
}