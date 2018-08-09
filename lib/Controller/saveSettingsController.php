<?php

use AuthCookie;
class saveSettingsController {

    private $memory_id;
    private $read;
    private $write;

    public function __construct()
    {
        $filterArgs = [
            'id'          => [ 'filter' => FILTER_SANITIZE_STRING ],
            'read'   => ['filter' => FILTER_VALIDATE_BOOLEAN],
            'write'   => ['filter' => FILTER_VALIDATE_BOOLEAN]
        ];
        $__postInput = filter_input_array( INPUT_POST, $filterArgs );
        $this->memory_id = $__postInput['id'];
        $this->read = $__postInput['read'];
        $this->write = $__postInput['write'];
    }

    public function doAction() {
        $user = AuthCookie::getCredentials();
        $JobsDao = new Jobs_JobDao();
        $JobsDao->saveMemorySetting($user['uid'], $this->memory_id, $this->read, $this->write);

        echo json_encode("OK");
    }

    public function finalize() {}
}