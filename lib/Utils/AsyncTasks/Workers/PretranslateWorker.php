<?php

namespace AsyncTasks\Workers;

use TaskRunner\Commons\AbstractWorker,
        TaskRunner\Commons\QueueElement,
        TaskRunner\Exceptions\EndQueueException,

        TaskRunner\Commons\AbstractElement;
use Pretranslate\PretranslateStruct;

class PretranslateWorker extends AbstractWorker {

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
        $pretranslateStruct->jwtToken .= '3';

        foreach($emptySegments as $segment) {
            $translation = '';
            $type = '';
            $match = '';
            if ($pretranslateStruct->useTm) {
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
                            $translation = $tms_match[0]['translation'];
                            $match = $tms_match[0]['match'];
                            $type = 'TM';
                        }
                    }
                } catch (\Unauthorized $e) {
                    $this->log_text('Caught unauthorized');
                    $refreshData = $this->refreshToken($pretranslateStruct->jwtRefreshToken);
                    $pretranslateStruct->jwtToken = $refreshData['access'];
                    $pretranslateStruct->jwtRefreshToken = $refreshData['refresh'];

                    $tms_match = \TildeTM::getContributionsAsync(
                        $pretranslateStruct->uid,
                        $pretranslateStruct->jwtToken . '2',
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
                }
            }
            if (empty($translation) && $pretranslateStruct->useMt) {
                $mt_match = \LetsMTLite::getMatch($pretranslateStruct->mtSystem, $segment->segment);
                $translation = $mt_match[0]['translation'];
                $match = 70;
                $type = 'MT';
            }

            if (empty($translation)) {
                continue;
            }

            \Jobs_JobDao::setTranslation($segment->id, $translation, $match, $type);
        }

        \Jobs_JobDao::removePretranslate($pretranslateStruct->id);
    }

    private function refreshToken($refreshToken) {
        $refreshData = new \stdClass();
        $refreshData->refresh = $refreshToken;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, "https://hugotest.tilde.lv/ws/auth/jwt");
        curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($refreshData));
        curl_setopt($curl, CURLOPT_HEADER  , true);
        $resp = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $refreshResponse =  json_decode($resp);
        $this->log_text('refresh response');
        $this->log($refreshResponse);
        return $refreshResponse;
    }

}
