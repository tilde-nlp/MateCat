<?php

use AuthCookie;
class setEditingTimeController {

    private $id;
    private $editingTime;

    public function __construct()
    {
        $filterArgs = [
            'id'          => [ 'filter' => FILTER_SANITIZE_STRING ],
            'editingTime'          => [ 'filter' => FILTER_VALIDATE_INT ],
        ];
        $__postInput = filter_input_array( INPUT_POST, $filterArgs );
        $this->id = $__postInput['id'];
        $this->editingTime = $__postInput['editingTime'];
    }

    public function doAction() {
        $user = AuthCookie::getCredentials();
        $JobsDao = new Jobs_JobDao();
        $JobsDao->saveEditingTime($this->id, $this->editingTime);

        echo json_encode("OK");
    }

    public function finalize() {}
}