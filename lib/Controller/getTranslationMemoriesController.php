<?php

class getTranslationMemoriesController extends ajaxController {
    public function doAction() {
        $result = [];
        $result['memories'] = \MemorySettings::getUserMemorySettings();
        $UserDao = new Users_UserDao();
        $userData = AuthCookie::getCredentials();
        $userStruct = $UserDao->getByUid($userData['uid']);
        $result['update_mt'] = $userStruct->update_mt;
        echo json_encode($result);
    }

    public function finalize() {}
}