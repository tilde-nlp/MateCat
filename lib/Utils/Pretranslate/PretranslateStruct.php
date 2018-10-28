<?php

namespace Pretranslate;

use \DataAccess_AbstractDaoObjectStruct;

use \DataAccess_IDaoStruct;

/**
 * Class ContributionStruct
 * @package Contribution
 */
class PretranslateStruct extends DataAccess_AbstractDaoObjectStruct implements DataAccess_IDaoStruct {

    protected $cached_results = array();

   public $id = "doge";

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function __toString() {
        return json_encode( $this->toArray() );
    }

    public function start(){

        try{
            \WorkerClient::enqueue( 'CONTRIBUTION', '\AsyncTasks\Workers\PretranslateWorker', $this, array( 'persistent' => \WorkerClient::$_HANDLER->persistent ) );
        } catch ( \Exception $e ){

            # Handle the error, logging, ...
            $output  = "**** Pretranslate failed. AMQ Connection Error. ****\n\t";
            $output .= "{$e->getMessage()}";
            $output .= var_export( $this, true );
            \Log::doLog( $output );
            throw $e;

        }

    }

}
