<?php

class saveMtPretranslateController {

    private $pretranslate;

    public function __construct()
    {
        $filterArgs = [
            'pretranslate' => [ 'filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_SCALAR ],
        ];
        $__postInput = filter_input_array( INPUT_POST, $filterArgs );
        $this->pretranslate = intval($__postInput['pretranslate']) > 0;
    }

    public function doAction() {
        $user = AuthCookie::getCredentials();
        Jobs_JobDao::saveMtPretranslate($user['uid'], $this->pretranslate ? 1 : 0);

        echo json_encode("OK");
    }

    public function finalize() {}
}