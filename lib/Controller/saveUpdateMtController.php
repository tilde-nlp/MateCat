<?php

class saveUpdateMtController extends ajaxController {

    private $update_mt;

    public function __construct()
    {
        $filterArgs = [
            'update_mt' => [ 'filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_SCALAR ],
        ];
        $__postInput = filter_input_array( INPUT_POST, $filterArgs );
        $this->update_mt = intval($__postInput['update_mt']) > 0;
    }

    public function doAction() {
        $user = AuthCookie::getCredentials();
        Jobs_JobDao::saveUpdateMt($user['uid'], $this->update_mt ? 1 : 0);

        echo json_encode("OK");
    }

    public function finalize() {}
}