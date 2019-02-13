<?php

class getUpdateMtForFileController extends ajaxController {
    public function doAction() {
        $projectId = $_GET['projectId'];
        $JobDao = new Jobs_JobDao();
        $this->result = intval(array_pop($JobDao->getUpdateMtForProject($projectId))['update_mt']);
    }
}