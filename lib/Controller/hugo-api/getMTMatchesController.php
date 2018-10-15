<?php

class getMTMatchesController extends ajaxController
{
    private $User;
    private $Job;
    private $postInput;

    public function __construct()
    {
        parent::__construct();
        $this->User = AuthCookie::getCredentials();
        $this->filterInput();
    }

    private function filterInput() {
        $filterArgs = [
            'id_job' => [ 'filter' => FILTER_SANITIZE_NUMBER_INT ],
        ];

        $this->postInput = filter_input_array( INPUT_POST, $filterArgs );
    }

    function doAction()
    {

    }
}

