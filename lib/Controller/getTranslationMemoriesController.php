<?php

class getTranslationMemoriesController {
    public function doAction() {
        $TildeTM = new TildeTM(INIT::$TM_BASE_URL, AuthCookie::getCookie());
        echo json_encode($TildeTM->getMemories());
    }

    public function finalize() {}
}