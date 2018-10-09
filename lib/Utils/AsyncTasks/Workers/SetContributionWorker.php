<?php
/**
 * Created by PhpStorm.
 * @author domenico domenico@translated.net / ostico@gmail.com
 * Date: 02/05/16
 * Time: 20.36
 *
 */

namespace AsyncTasks\Workers;

use Contribution\ContributionStruct,
        Engine,
        TaskRunner\Commons\AbstractWorker,
        TaskRunner\Commons\QueueElement,
        TaskRunner\Exceptions\EndQueueException,
        TaskRunner\Exceptions\ReQueueException,
        TmKeyManagement_Filter,
        TmKeyManagement_TmKeyManagement,
        TaskRunner\Commons\AbstractElement,
        Jobs_JobStruct;
use INIT;
use TildeTM;
use Jobs_JobDao;

class SetContributionWorker extends AbstractWorker {

    const ERR_SET_FAILED    = 4;
    const ERR_UPDATE_FAILED = 6;
    const ERR_NO_TM_ENGINE  = 5;

    const REDIS_PROPAGATED_ID_KEY = "j:%s:s:%s";

    /**
     * @var \Engines_EngineInterface
     */
    protected $_engine;

    protected function log_text($data) {
        file_put_contents('/var/tmp/worker.log', $data, FILE_APPEND);
        file_put_contents('/var/tmp/worker.log', "\n", FILE_APPEND);
    }

    protected function log($data) {
        file_put_contents('/var/tmp/worker.log', var_export($data, true), FILE_APPEND);
        file_put_contents('/var/tmp/worker.log', "\n", FILE_APPEND);
    }

    /**
     * This method is for testing purpose. Set a dependency injection
     *
     * @param \Engines_EngineInterface $_tms
     */
    public function setEngine( $_tms ){
        $this->_engine = $_tms;
    }

    /**
     * @param AbstractElement $queueElement
     *
     * @return null
     * @throws EndQueueException
     * @throws ReQueueException
     * @throws \Exception
     * @throws \Exceptions\ValidationError
     */
    public function process( AbstractElement $queueElement ) {

        /**
         * @var $queueElement QueueElement
         */
        $this->_checkForReQueueEnd( $queueElement );

        $contributionStruct = new ContributionStruct( $queueElement->params->toArray() );

        $this->_checkDatabaseConnection();

        $this->_execContribution( $contributionStruct );

    }

    /**
     * @param ContributionStruct $contributionStruct
     *
     * @throws ReQueueException
     * @throws \Exception
     * @throws \Exceptions\ValidationError
     */
    protected function _execContribution( ContributionStruct $contributionStruct ){
        $jobStruct = $contributionStruct->getJobStruct();
        $this->_loadEngine( $contributionStruct );

        $config = $this->_engine->getConfigStruct();
        $config[ 'source' ]      = $jobStruct->source;
        $config[ 'target' ]      = $jobStruct->target;
        $config[ 'email' ]       = $contributionStruct->api_key;

        $config = array_merge( $config, $this->_extractAvailableKeysForUser( $contributionStruct, $jobStruct ) );

        $redisSetKey = sprintf( static::REDIS_PROPAGATED_ID_KEY, $contributionStruct->id_job, $contributionStruct->id_segment );
        $isANewSet  = $this->_queueHandler->getRedisClient()->setnx( $redisSetKey, 1 );

        try {

//            if( empty( $isANewSet ) && $contributionStruct->propagationRequest ){
//                $this->_update( $config, $contributionStruct );
//                $this->_doLog( "Key UPDATE: $redisSetKey, " . var_export( $isANewSet, true ) );
//            } else {
//                $this->_set( $config, $contributionStruct );
//                $this->_doLog( "Key SET: $redisSetKey, " . var_export( $isANewSet, true ) );
//            }

            $this->_queueHandler->getRedisClient()->expire(
                    $redisSetKey,
                    60 * 60 * 24 * INIT::JOB_ARCHIVABILITY_THRESHOLD
            ); //TTL 3 months, the time for job archivability

        } catch( ReQueueException $e ){
            $this->_doLog( $e->getMessage() );
            if( $e->getCode() == self::ERR_SET_FAILED || $isANewSet ){
                $this->_queueHandler->getRedisClient()->del( [ $redisSetKey ] );
            }
            throw $e;
        }

    }

    /**
     * !Important Refresh the engine ID for each queueElement received
     * to avoid set contributions on the wrong engine ID
     *
     * @param ContributionStruct $contributionStruct
     *
     * @throws \Exception
     * @throws \Exceptions\ValidationError
     */
    protected function _loadEngine( ContributionStruct $contributionStruct ){

        $jobStruct = $contributionStruct->getJobStruct();
        if( empty( $this->_engine ) ){
            $this->_engine = Engine::getInstance( $jobStruct->id_tms ); //Load MyMemory
        }

    }

    /**
     * @param array              $config
     * @param ContributionStruct $contributionStruct
     *
     * @throws ReQueueException
     * @throws \Exceptions\ValidationError
     */
    protected function _set( Array $config, ContributionStruct $contributionStruct ) {
        $this->log($contributionStruct->jwt_token);
        $this->log(INIT::$TM_BASE_URL);
        $TildeTM = new TildeTM(INIT::$TM_BASE_URL, $contributionStruct->jwt_token);
        $memories = $TildeTM->getMemories();
        $canWrite= $this->settingsToArray(Jobs_JobDao::getMemorySetting($contributionStruct->uid));
        $this->log($canWrite);
        $this->log($memories);
        if (empty($memories)) {
            return;
        }
        foreach($memories as &$mem) {
            $writeValue = empty($canWrite[$mem->id]) ? true : $canWrite[$mem->id];
            $mem->write = true && $mem->canUpdate && $writeValue;
        }
        $this->log($memories[0]);
        $this->log($memories[1]);
        foreach($memories as $memory) {
            $this->log_text('Write');
            $this->log($memory);
            $this->log($memory->write);
            if (!$memory->write) {
                continue;
            }
            $this->log_text('Writing');
            $TildeTM->writeMatch(
                $memory->id,
                $contributionStruct->segment,
                $contributionStruct->translation,
                substr($config['source'], 0, 2),
                substr($config['target'], 0, 2)
            );
        }
    }

    private function settingsToArray($settings) {
        $settingsArray = [];
        foreach($settings as $setting) {
            $settingsArray[$setting['memory_id']] = intval($setting['write_memory']) > 0;
        }
        return $settingsArray;
    }

    protected function _update( Array $config, ContributionStruct $contributionStruct ){
        $this->_set($config, $contributionStruct);
    }

    protected function _extractAvailableKeysForUser( ContributionStruct $contributionStruct, Jobs_JobStruct $jobStruct ){

        if ( $contributionStruct->fromRevision ) {
            $userRole = TmKeyManagement_Filter::ROLE_REVISOR;
        } else {
            $userRole = TmKeyManagement_Filter::ROLE_TRANSLATOR;
        }

        //find all the job's TMs with write grants and make a contribution to them
        $tm_keys = TmKeyManagement_TmKeyManagement::getJobTmKeys( $jobStruct->tm_keys, 'w', 'tm', $contributionStruct->uid, $userRole  );

        $config = [];
        if ( !empty( $tm_keys ) ) {

            $config[ 'keys' ] = array();
            foreach ( $tm_keys as $i => $tm_info ) {
                $config[ 'id_user' ][] = $tm_info->key;
            }

        }

        return $config;

    }

    /**
     * @param       $type
     * @param array $config
     *
     * @throws ReQueueException
     */
    protected function _raiseException( $type, array $config ){
        //reset the engine
        $engineName = get_class( $this->_engine );
        $this->_engine = null;

        $errNum = self::ERR_SET_FAILED;
        switch( strtolower( $type ) ){
            case 'set':
                $errNum = self::ERR_SET_FAILED;
                break;
            case 'update':
                $errNum = self::ERR_UPDATE_FAILED;
                break;
        }

        throw new ReQueueException( "$type failed on " . $engineName . ": Values " . var_export( $config, true ), $errNum );
    }

}