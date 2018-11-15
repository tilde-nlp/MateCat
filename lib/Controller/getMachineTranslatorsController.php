<?php

class getMachineTranslatorsController {
    public function doAction() {
        $LetsMTLite = new \LetsMTLite(INIT::$MT_BASE_URL, AuthCookie::getToken(), INIT::$MT_APP_ID);
        $lang = 'en';
        if (!empty($_GET['lang'])) {
            $lang = $_GET['lang'];
        }
        $systems = $LetsMTLite->getSystems($lang);

        echo json_encode($systems);
    }

    public function finalize() {}
}
