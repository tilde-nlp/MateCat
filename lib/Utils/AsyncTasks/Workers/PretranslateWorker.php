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

        foreach($emptySegments as $segment) {
            $translation = '';
            $type = '';
            $match = '';
            if ($pretranslateStruct->useTm) {
                try {
                    $tms_match = \TildeTM::getContributionsAsync(
                        $pretranslateStruct->projectId,
                        $pretranslateStruct->jwtToken,
                        $segment->segment,
                        $pretranslateStruct->source,
                        $pretranslateStruct->target);

                    if (!empty($tms_match)) {
                        usort($tms_match, array( "getContributionController", "__compareScore" ));
                        if (intval($tms_match[0]['match']) >= 100) {
                            $translation = $tms_match[0]['translation'];
                            $match = $tms_match[0]['match'];
                            $type = 'TM';
                        }
                    }
                } catch (\Unauthorized $e) {
                    $refreshData = $this->refreshToken($pretranslateStruct->jwtRefreshToken);
                    $pretranslateStruct->jwtToken = $refreshData->access;
                    $pretranslateStruct->jwtRefreshToken = $refreshData->refresh;

                    try {
                        $tms_match = \TildeTM::getContributionsAsync(
                            $pretranslateStruct->projectId,
                            $pretranslateStruct->jwtToken,
                            $segment->segment,
                            $pretranslateStruct->source,
                            $pretranslateStruct->target);
                        if (!empty($tms_match[0])) {
                            usort($tms_match, array( "getContributionController", "__compareScore" ));
                            if (intval($tms_match[0]['match']) >= 100) {
                                $translation = $tms_match[0]['translation'];
                                $match = $tms_match[0]['match'];
                                $type = 'TM';
                            }
                        }
                    } catch (\Unauthorized $e2) {
                        \Log::doLog('Can\'t refresh token for second time.');
                    }
                }
            }
            if (empty($translation) && $pretranslateStruct->useMt) {
                try {
                    $mt_match = \LetsMTLite::getMatch($pretranslateStruct->mtSystem, $segment->segment, $pretranslateStruct->jwtToken, $pretranslateStruct->appId);
                    if (!empty($mt_match[0])) {
                        $translation = $mt_match[0]['translation'];
                        $match = 70;
                        $type = 'MT';
                    }
                } catch (\Unauthorized $e3) {
                    $refreshData = $this->refreshToken($pretranslateStruct->jwtRefreshToken);
                    $pretranslateStruct->jwtToken = $refreshData->access;
                    $pretranslateStruct->jwtRefreshToken = $refreshData->refresh;

                    try {
                        $mt_match = \LetsMTLite::getMatch($pretranslateStruct->mtSystem, $segment->segment, $pretranslateStruct->jwtToken, $pretranslateStruct->appId);
                        if (!empty($mt_match[0])) {
                            $translation = $mt_match[0]['translation'];
                            $match = 70;
                            $type = 'MT';
                        }
                    } catch (\Unauthorized $e4) {
                        \Log::doLog('Can\'t refresh token for second time.');
                    }
                }

            }

            if (empty($translation)) {
                continue;
            }

            $rowCount = \Jobs_JobDao::setTranslation($segment->id, $translation, $match, $type);

        }
        $rowCount = \Jobs_JobDao::removePretranslate($pretranslateStruct->id);
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
