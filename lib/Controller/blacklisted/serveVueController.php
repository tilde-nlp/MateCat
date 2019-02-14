<?php

class serveVueController {

    public function doAction() {
        $config = new \stdClass();
        $config->baseUrl = INIT::$RELATIVE_HOST_NAME;
        $config->authRedirect = INIT::$AUTH_REDIRECT;
        $config->mtClientId = INIT::$MT_CLIENT_ID;
        $config->mtBaseUrl = INIT::$MT_BASE_URL;
        $config->mtAppId = INIT::$MT_APP_ID;
        $index = file_get_contents('public/vue_dist/index.html');
        $index = str_replace('HugoCat.main({})', 'HugoCat.main('. json_encode($config) .')', $index);
        $index = str_replace('<baseUrl>', $config->baseUrl, $index);
        echo $index;
    }

    public function finalize() {}
}
