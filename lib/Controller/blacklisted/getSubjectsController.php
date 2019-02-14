<?php
use Exceptions\NotFoundError;
use Teams\MembershipDao;
use Teams\MembershipStruct;
use \API\V2\Json\Error;


/**
 * Description of manageController
 *
 * @author andrea
 */
class getSubjectsController extends ajaxController {

    private $subject_handler;

    public function __construct() {
        parent::__construct();
        $this->subject_handler    = Langs_LanguageDomains::getInstance();
    }

    public function doAction() {
        $this->result['subjects'] = $this->subject_handler->getEnabledDomains();
    }
}
