<?php

use AuthCookie;
class jobInfoController {

    private $id;
    private $password;

    public function __construct()
    {
        $filterArgs = [
            'id'          => [ 'filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_SCALAR ],
            'password'   => array(
                'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
            )
        ];
        $__postInput = filter_input_array( INPUT_POST, $filterArgs );
        $this->id = $__postInput['id'];
        $this->password = $__postInput['password'];
    }

    public function doAction() {
        $user = AuthCookie::getCredentials();
        $JobsDao = new Jobs_JobDao();
        $data = array_pop($JobsDao->getActiveSegment($user['uid'], $this->id));
        $result = new \stdClass();
        $result->active_segment_id = $data['segment_id'];

        echo json_encode($result);
    }

    public function finalize() {}
}