<?php 

class commentsAddController extends commentController {
    public function __construct() {
        parent::__construct();
        $this->__postInput['_sub'] = 'create';
    }
}