<?php

class getTranslationMemoriesController extends ajaxController {
    public function doAction() {
        $this->result = \MemorySettings::getUserMemorySettings();
    }
}