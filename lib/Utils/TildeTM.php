<?php

/**
 * Class LetsMTLite
 * @author YourLittleHelper <oskars@shibetec.com>
 * Simple class that provides the most basic interface with https://www.letsmt.eu
 */
class TildeTM {
    public $token;
    private $baseUrl;
    private static $debug = false;

    public static function getContributionsAsync($projectId, $token, $text, $sourceLang, $targetLang) {
        $TildeTM = new TildeTM(INIT::$TM_BASE_URL, AuthCookie::getToken());
        $memories = MemorySettings::getProjectMemorySettings($projectId);
        $tms_match = [];
        foreach($memories as $memory) {
            if ($memory['readMemory'] < 1) {
                continue;
            }
            $tildeMatches = $TildeTM->getMatches(
                $memory['id'],
                $text,
                substr($sourceLang, 0, 2),
                substr($targetLang, 0, 2),
                false
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

    public static function getContributions($projectId, $text, $sourceLang, $targetLang) {
        $TildeTM = new TildeTM(INIT::$TM_BASE_URL, AuthCookie::getToken());
        $memories = MemorySettings::getProjectMemorySettings($projectId);
        $tms_match = [];
        foreach($memories as $memory) {
            if ($memory['readMemory'] < 1) {
                continue;
            }
            $tildeMatches = $TildeTM->getMatches(
                $memory['id'],
                $text,
                substr($sourceLang, 0, 2),
                substr($targetLang, 0, 2),
                false
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

    public static function getConcordanceContributions($projectId, $text, $sourceLang, $targetLang) {
        $TildeTM = new TildeTM(INIT::$TM_BASE_URL, AuthCookie::getToken());
        $memories = MemorySettings::getProjectMemorySettings($projectId);
        $tms_match = [];
        foreach($memories as $memory) {
            if ($memory['read'] < 1) {
                continue;
            }
            $tildeMatches = $TildeTM->getMatches(
                $memory['id'],
                $text,
                substr($sourceLang, 0, 2),
                substr($targetLang, 0, 2),
                true
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
            'targetLang' => $targetLang,
            'match' => 100
        );
        return $this->post('tm/' . urlencode($collection) . '/segments', $data);
    }

    protected function get($request) {
        if (self::$debug) {
            self::log('TILDE TM GET: ' . $this->baseUrl . $request);
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Authorization: Bearer ' . $this->token));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $this->baseUrl . $request);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (self::$debug) {
            self::log('TILDE TM GET RESPONSE: ' . $httpcode);
            if ($httpcode != 200) {
                self::log($response);
            }
        }
        curl_close($curl);
        if ($httpcode == 401) {
            throw new Unauthorized();
        }
        return json_decode($body);
    }


    protected function post($request, $data) {
        if (self::$debug) {
            self::log('TILDE TM POST: ' . $this->baseUrl . $request);
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Authorization: Bearer ' . $this->token));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $this->baseUrl . $request);
        curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HEADER  , true);
        $resp = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (self::$debug) {
            self::log('TILDE TM POST RESPONSE: ' . $httpcode);
            if ($httpcode != 200 && $httpcode != 204) {
                self::log($resp);
            }
        }
        curl_close($curl);
        return json_decode($resp);
    }

    protected function log_text($data) {
        file_put_contents('/var/tmp/TildeTM.log', $data, FILE_APPEND);
        file_put_contents('/var/tmp/TildeTM.log', "\n", FILE_APPEND);
    }

    protected function log($data) {
        file_put_contents('/var/tmp/TildeTM.log', var_export($data, true), FILE_APPEND);
        file_put_contents('/var/tmp/TildeTM.log', "\n", FILE_APPEND);
    }
}
