<?php

class saveUpdateMtForProjectController extends ajaxController {

    private $update_mt;
    private $projectId;

    public function __construct()
    {
        $filterArgs = [
            'updateMt' => [ 'filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_SCALAR ],
            'projectId' => [ 'filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_SCALAR ],
        ];
        $__postInput = filter_input_array( INPUT_POST, $filterArgs );
        $this->update_mt = intval($__postInput['updateMt']);
        $this->projectId = intval($__postInput['projectId']);
    }

    public function doAction() {
        Jobs_JobDao::saveUpdateMtForProject($this->projectId, $this->update_mt);

        $this->result = ['status' => 'ok'];
    }
}