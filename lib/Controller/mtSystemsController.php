<?php

class mtSystemsController {

    public function doAction() {
        $LetsMTLite = new \LetsMTLite(INIT::$LETSMT_BASE_URL, INIT::$LETSMT_CLIENT_ID);
        $systems = $LetsMTLite->getSystems();

        echo json_encode($systems->System);
    }

    public function finalize() {}
}