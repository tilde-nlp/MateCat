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
class getLanguagesController extends ajaxController {

    private $lang_handler;

    public function __construct() {
        parent::__construct();
        $this->lang_handler    = Langs_Languages::getInstance();
    }

    public function doAction() {
        $this->result['languages'] = $this->lang_handler->getEnabledLanguages( 'en' );
    }
}
