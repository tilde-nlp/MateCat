<?php

class getMachineTranslatorsController extends ajaxController {
    public function doAction() {
        $LetsMTLite = new \LetsMTLite(INIT::$MT_BASE_URL, AuthCookie::getToken(), 'CAT-DEPRECATED');
        $lang = 'en';
        if (!empty($_GET['lang'])) {
            $lang = $_GET['lang'];
        }
        $systems = $LetsMTLite->getSystems($lang);

        echo json_encode($systems);
    }

    public function finalize() {}
}
