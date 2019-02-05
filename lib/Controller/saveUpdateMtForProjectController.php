<?php

class saveUpdateMtForProjectController extends ajaxController {

    private $update_mt;
    private $projectId;

    public function __construct()
    {
        $filterArgs = [
            'update_mt' => [ 'filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_SCALAR ],
            'project_id' => [ 'filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_SCALAR ],
        ];
        $__postInput = filter_input_array( INPUT_POST, $filterArgs );
        $this->update_mt = intval($__postInput['update_mt']);
        $this->projectId = intval($__postInput['project_id']);
    }

    public function doAction() {
        Jobs_JobDao::saveUpdateMtForProject($this->projectId, $this->update_mt);

        echo json_encode("OK");
    }

    public function finalize() {}
}