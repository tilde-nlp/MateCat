<?php

class serveVueController {

    public function doAction() {
        $config = new \stdClass();
        $config->baseUrl = INIT::$RELATIVE_HOST_NAME;
        $config->authRedirect = INIT::$AUTH_REDIRECT;
        $index = file_get_contents('public/vue_dist/index.html');
        $index = str_replace('HugoCat.main({})', 'HugoCat.main('. json_encode($config) .')', $index);
        $index = str_replace('<baseUrl>', $config->baseUrl, $index);
        echo $index;
    }

    public function finalize() {}
}
