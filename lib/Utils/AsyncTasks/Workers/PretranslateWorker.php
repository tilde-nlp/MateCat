<?php

namespace AsyncTasks\Workers;

use TaskRunner\Commons\AbstractWorker,
        TaskRunner\Commons\QueueElement,
        TaskRunner\Exceptions\EndQueueException,

        TaskRunner\Commons\AbstractElement;
use Pretranslate\PretranslateStruct;
use INIT;

class PretranslateWorker extends AbstractWorker {
    private $uid;

    /**
     * @param AbstractElement $queueElement
     *
     * @throws EndQueueException
     * @throws \Exception
     */
    public function process( AbstractElement $queueElement ) {
        $this->uid = uniqid();
        $this->log_text($this->uid . ' worker: Starting up');
        /**
         * @var $queueElement QueueElement
         */
        $this->_checkForReQueueEnd( $queueElement );

        $pretranslateStruct = new PretranslateStruct( $queueElement->params->toArray() );

        $this->_checkDatabaseConnection();

        $this->_execContribution( $pretranslateStruct );

    }

    /**
     * @param PretranslateStruct $pretranslateStruct
     *
     */
    protected function _execContribution( PretranslateStruct $pretranslateStruct ){
        $emptySegments = \Jobs_JobDao::getEmptySegments(
            $pretranslateStruct->id,
            $pretranslateStruct->password,
            $pretranslateStruct->job_first_segment,
            $pretranslateStruct->job_last_segment);
        $this->log_text($this->uid . ' worker: Empty segments found: ' . count($emptySegments));
        $refreshData = $this->refreshToken($pretranslateStruct->jwtRefreshToken);

        foreach($emptySegments as $segment) {
            $translation = '';
            $type = '';
            $match = '';
            $this->log_text($this->uid . ' worker: Processing segment: ');
            $this->log($segment);
            if ($pretranslateStruct->useTm) {
                $this->log_text($this->uid . ' worker: Using TM');
                try {
                    $tms_match = \TildeTM::getContributionsAsync(
                        $pretranslateStruct->uid,
                        $pretranslateStruct->jwtToken,
                        $segment->segment,
                        $pretranslateStruct->source,
                        $pretranslateStruct->target);

                    if (!empty($tms_match)) {
                        usort($tms_match, array( "getContributionController", "__compareScore" ));
                        if (intval($tms_match[0]['match']) >= 100) {
                            $this->log_text($this->uid . ' worker: Setting TM match: ');
                            $this->log($tms_match[0]);
                            $translation = $tms_match[0]['translation'];
                            $match = $tms_match[0]['match'];
                            $type = 'TM';
                        }
                    }
                } catch (\Unauthorized $e) {
                    $this->log_text($this->uid . ' worker: Caught unauthorized.');
                    $refreshData = $this->refreshToken($pretranslateStruct->jwtRefreshToken);
                    $this->log_text($this->uid . ' worker: Token refresh data: ');
                    $this->log($refreshData);
                    $pretranslateStruct->jwtToken = $refreshData->access;
                    $pretranslateStruct->jwtRefreshToken = $refreshData->refresh;

                    try {
                        $tms_match = \TildeTM::getContributionsAsync(
                            $pretranslateStruct->uid,
                            $pretranslateStruct->jwtToken,
                            $segment->segment,
                            $pretranslateStruct->source,
                            $pretranslateStruct->target);
                        $this->log_text($this->uid . ' worker: Matches after refreshing token: ');
                        $this->log($tms_match);
                        if (!empty($tms_match[0])) {
                            usort($tms_match, array( "getContributionController", "__compareScore" ));
                            if (intval($tms_match[0]['match']) >= 100) {
                                $this->log_text($this->uid . ' worker: Setting TM match: ');
                                $this->log($tms_match[0]);
                                $translation = $tms_match[0]['translation'];
                                $match = $tms_match[0]['match'];
                                $type = 'TM';
                            }
                        }
                    } catch (\Unauthorized $e2) {
                        $this->log_text($this->uid . ' worker: Caught second Unauthorized in a row.');
                        \Log::doLog('Can\'t refresh token for second time.');
                    }
                }
            }
            if (empty($translation) && $pretranslateStruct->useMt) {
                $this->log_text($this->uid . ' worker: Using MT');
                $mt_match = \LetsMTLite::getMatch($pretranslateStruct->mtSystem, $segment->segment);
                if (!empty($mt_match[0])) {
                    $translation = $mt_match[0]['translation'];
                    $this->log_text($this->uid . ' worker: Setting MT match: ');
                    $this->log($mt_match[0]);
                    $match = 70;
                    $type = 'MT';
                }
            }

            if (empty($translation)) {
                $this->log_text($this->uid . ' worker: Didn\'t found match.');
                continue;
            }

            $rowCount = \Jobs_JobDao::setTranslation($segment->id, $translation, $match, $type);
            $this->log_text($this->uid . ' worker: Row count of setting translation: ' . $rowCount);

        }
        $rowCount = \Jobs_JobDao::removePretranslate($pretranslateStruct->id);
        $this->log_text($this->uid . ' worker: Row count of clearing tm and mt flags: ' . $rowCount);
    }

    private function refreshToken($refreshToken) {
        $refreshData = new \stdClass();
        $refreshData->refresh = $refreshToken;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, \INIT::$TOKEN_REFRESH_URL);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($refreshData));
        curl_setopt($curl, CURLOPT_HEADER  , true);
        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $refreshResponse =  json_decode($body);
        return $refreshResponse;
    }

}
