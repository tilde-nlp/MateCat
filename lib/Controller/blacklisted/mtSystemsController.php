<?php

class mtSystemsController extends ajaxController {

    public function doAction() {
        $LetsMTLite = new \LetsMTLite(INIT::$MT_BASE_URL, INIT::$MT_CLIENT_ID);
        $systems = $LetsMTLite->getSystems();

        echo json_encode($systems->System);
    }

    public function finalize() {}
}