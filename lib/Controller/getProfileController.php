<?php

class getProfileController extends ajaxController {
    public function doAction() {
        $userData = AuthCookie::getCredentials();
        $UserDao = new Users_UserDao();
        $userStruct = $UserDao->getByUid($userData['uid']);

        $user = new \stdClass();
        $user->email = $userStruct->email;
        $user->first_name = $userStruct->first_name;
        $user->last_name = $userStruct->last_name;
        $user->mt_pretranslate = $userStruct->mt_pretranslate;
        $user->tm_pretranslate = $userStruct->tm_pretranslate;
        $user->update_mt = $userStruct->update_mt;
        $user->uid = $userStruct->uid;
        echo json_encode($user);
    }

    public function finalize() {}
}