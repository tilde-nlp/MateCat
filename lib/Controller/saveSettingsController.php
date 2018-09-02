<?php

use AuthCookie;
class saveSettingsController {

    private $memory_id;
    private $read;
    private $write;
    private $concordance;

    public function __construct()
    {
        $filterArgs = [
            'id'          => [ 'filter' => FILTER_SANITIZE_STRING ],
            'read'   => ['filter' => FILTER_VALIDATE_BOOLEAN],
            'write'   => ['filter' => FILTER_VALIDATE_BOOLEAN],
            'concordance'   => ['filter' => FILTER_VALIDATE_BOOLEAN],
        ];
        $__postInput = filter_input_array( INPUT_POST, $filterArgs );
        $this->memory_id = $__postInput['id'];
        $this->read = $__postInput['read'];
        $this->write = $__postInput['write'];
        $this->concordance = $__postInput['concordance'];
    }

    public function doAction() {
        $user = AuthCookie::getCredentials();
        $JobsDao = new Jobs_JobDao();
        $JobsDao->saveMemorySetting($user['uid'], $this->memory_id, $this->read, $this->write, $this->concordance);

        echo json_encode("OK");
    }

    public function finalize() {}
}