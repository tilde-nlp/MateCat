<?php

class getTranslationMemoriesController extends ajaxController {
    public function doAction() {
        echo json_encode(\MemorySettings::getUserMemorySettings());
    }

    public function finalize() {}
}