<?php

class getSettingsController {

    public function doAction() {
        $result = new \stdClass();

        echo json_encode($result);
    }

    public function finalize() {}
}