<?php

class getSettingsForProjectController extends ajaxController {
    public function doAction() {
        $projectId = $_GET['project_id'];
        $result = [];
        $result['memories'] = \MemorySettings::getProjectMemorySettings($projectId);
        $JobDao = new Jobs_JobDao();
        $result['update_mt'] = array_pop($JobDao->getUpdateMtForProject($projectId))['update_mt'];
        echo json_encode($result);
    }

    public function finalize() {}
}