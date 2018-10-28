<?php

namespace AsyncTasks\Workers;

use TaskRunner\Commons\AbstractWorker,
        TaskRunner\Commons\QueueElement,
        TaskRunner\Exceptions\EndQueueException,

        TaskRunner\Commons\AbstractElement;
use Pretranslate\PretranslateStruct;

class PretranslateWorker extends AbstractWorker {

    protected function log_text($data) {
        file_put_contents('/var/tmp/worker.log', $data, FILE_APPEND);
        file_put_contents('/var/tmp/worker.log', "\n", FILE_APPEND);
    }

    protected function log($data) {
        file_put_contents('/var/tmp/worker.log', var_export($data, true), FILE_APPEND);
        file_put_contents('/var/tmp/worker.log', "\n", FILE_APPEND);
    }

    /**
     * @param AbstractElement $queueElement
     *
     * @throws EndQueueException
     * @throws \Exception
     */
    public function process( AbstractElement $queueElement ) {

        /**
         * @var $queueElement QueueElement
         */
        $this->_checkForReQueueEnd( $queueElement );

        $contributionStruct = new PretranslateStruct( $queueElement->params->toArray() );

        $this->_checkDatabaseConnection();

        $this->_execContribution( $contributionStruct );

    }

    /**
     * @param PretranslateStruct $pretranslateStruct
     *
     */
    protected function _execContribution( PretranslateStruct $pretranslateStruct ){

        $counter = 0;
        while($counter < 5) {
            $this->log_text("Working on " . $pretranslateStruct->id);
            $counter++;
        }
    }

}
