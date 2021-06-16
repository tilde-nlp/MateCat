<?php

class getSettingsForProjectController extends ajaxController {
    public function doAction() {
        $projectId = $_GET['projectId'];
        $this->result = \MemorySettings::getProjectMemorySettings($projectId);
    }
}