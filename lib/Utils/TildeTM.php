<?php

/**
 * Class LetsMTLite
 * @author YourLittleHelper <oskars@shibetec.com>
 * Simple class that provides the most basic interface with https://www.letsmt.eu
 */
class TildeTM {
    private $token;
    private $baseUrl;

    public static function getContributions($text, $sourceLang, $targetLang) {
        $TildeTM = new TildeTM(INIT::$TM_BASE_URL, AuthCookie::getCookie());
        $memories = $TildeTM->getMemories();
        foreach($memories as &$memory) {
            $memory->read = true;
            $memory->concordance = false;
        }
        $tms_match = [];
        $user = AuthCookie::getCredentials();
        $memorySettings = Jobs_JobDao::getMemorySetting($user['uid']);
        foreach($memorySettings as $setting) {
            foreach($memories as $k => $memory) {
                if (strcmp($memory->id, $setting['memory_id']) !== 0) {
                    continue;
                }
                $memories[$k]->read = $setting['read_memory'] > 0;
                $memories[$k]->concordance = $setting['concordance_search'] > 0;
            }
        }
        foreach($memories as $memory) {
            if (!$memory->read) {
                continue;
            }
            $tildeMatches = $TildeTM->getMatches(
                $memory->id,
                $text,
                substr($sourceLang, 0, 2),
                substr($targetLang, 0, 2),
                $memory->concordance
            );

            foreach($tildeMatches as $match) {
                $tms_match[ ] = array(
                    'created_by' => $memory->name,
                    'match' => $match->match,
                    'segment' => $match->source,
                    'translation' => $match->target,
                    'raw_segment' => $text,
                    'raw_translation' => $match->target,
                );
            }
        }

        return $tms_match;
    }

    public function __construct($baseUrl, $token)
    {
        $this->baseUrl = $baseUrl;
        $this->token = $token;
    }

    public function getMemories() {
        return $this->get('tm');
    }

    public function getMatches($collection, $text, $source, $target, $concordance) {
        $queryString = http_build_query(
            array(
                'q' => $text,
                'sourceLang' => $source,
                'targetLang' => $target)
        );
        if ($concordance) {
            $queryString .= '&concordance=true';
        } else {
            $queryString .= '&concordance=false';
        }
        return $this->get('tm/' . urlencode($collection) . '/segments?' . $queryString);
    }

    public function writeMatch($collection, $source, $target, $sourceLang, $targetLang) {
        $data = array(
            'source' => $source,
            'target' => $target,
            'sourceLang' => $sourceLang,
            'targetLang' => $targetLang
        );
        return $this->post('tm/' . urlencode($collection) . '/segments', $data);
    }

    protected function get($request) {
        // Get cURL resource
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Authorization: Bearer ' . $this->token));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $this->baseUrl . $request);
        $resp = curl_exec($curl);
        curl_close($curl);
        return json_decode($resp);
    }


    protected function post($request, $data) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Authorization: Bearer ' . $this->token));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $this->baseUrl . $request);
        curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
        $resp = curl_exec($curl);
        curl_close($curl);
        return json_decode($resp);
    }
}
