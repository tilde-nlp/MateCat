<?php

class pretranslateController {

    private $useTm;
    private $useMt;
    private $id;
    private $password;
    private $mtSystem;

    public function __construct()
    {
        $filterArgs = [
            'use_tm'          => [ 'filter' => FILTER_VALIDATE_INT ],
            'use_mt'          => [ 'filter' => FILTER_VALIDATE_INT ],
            'id'          => [ 'filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_SCALAR ],
            'mt_system'  => [ 'filter' => FILTER_SANITIZE_STRING],
            'password'   => array (
                'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
            )
        ];
        $__postInput = filter_input_array( INPUT_POST, $filterArgs );
        $this->id = $__postInput['id'];
        $this->password = $__postInput['password'];
        $this->useTm = intval($__postInput['use_tm']) > 0;
        $this->useMt = intval($__postInput['use_mt']) > 0;
        $this->mtSystem = $__postInput[ 'mt_system' ];
    }

    public function doAction() {
        set_time_limit ( 3600 );
        $jobData = array_pop(Jobs_JobDao::getById($this->id));

        if ($this->useTm || $this->useMt) {
            $this->preTranslate($jobData);
        }

        echo json_encode([]);
    }

    private function preTranslate($jobData) {
        $emptySegments = Jobs_JobDao::getEmptySegments($this->id, $this->password, $jobData->job_first_segment, $jobData->job_last_segment);

        foreach($emptySegments as $segment) {
            $translation = '';
            $type = '';
            $match = '';
            if ($this->useTm) {
                $tms_match = TildeTM::getContributions($segment->segment, $jobData->source, $jobData->target);
                if (!empty($tms_match)) {
                    usort($tms_match, array( "getContributionController", "__compareScore" ));
                    $tms_match = array_reverse($tms_match);
                    if (intval($tms_match[0]['match']) >= 100) {
                        $translation = $tms_match[0]['translation'];
                        $match = $tms_match[0]['match'];
                        $type = 'TM';
                    }
                }
            }
            if (empty($translation) && $this->useMt) {
                $mt_match = \LetsMTLite::getMatch($this->mtSystem, $segment->segment);
                $translation = $mt_match[0]['translation'];
                $match = 70;
                $type = 'MT';
            }

            if (empty($translation)) {
                continue;
            }

            Jobs_JobDao::setTranslation($segment->id, $translation, $match, $type);
        }

        Jobs_JobDao::removePretranslate($this->id);
    }

    public function finalize() {}
}