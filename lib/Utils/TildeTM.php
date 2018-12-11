<?php

/**
 * Class LetsMTLite
 * @author YourLittleHelper <oskars@shibetec.com>
 * Simple class that provides the most basic interface with https://www.letsmt.eu
 */
class TildeTM {
    public $token;
    private $baseUrl;

    public static function getContributionsAsync($uid, $token, $text, $sourceLang, $targetLang) {
        $TildeTM = new TildeTM(INIT::$TM_BASE_URL, $token);
        $memorySettings = self::settingsToArray(Jobs_JobDao::getMemorySetting($uid));
        $memories = $TildeTM->getMemories();
        foreach($memories as &$mem) {
            $readValue = empty($memorySettings[$mem->id]['read']) ? true : $memorySettings[$mem->id]['read'];
            $mem->read = true && $readValue;
            $mem->concordance = false;
        }
        $tms_match = [];
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

    public static function getContributions($text, $sourceLang, $targetLang) {
        $TildeTM = new TildeTM(INIT::$TM_BASE_URL, AuthCookie::getToken());
        $user = AuthCookie::getCredentials();
        $memorySettings = self::settingsToArray(Jobs_JobDao::getMemorySetting($user['uid']));
        $memories = $TildeTM->getMemories();
        foreach($memories as &$mem) {
            $readValue = empty($memorySettings[$mem->id]['read']) ? true : $memorySettings[$mem->id]['read'];
            $mem->read = true && $readValue;
            $mem->concordance = false;
        }
        $tms_match = [];
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

    public static function getConcordanceContributions($text, $sourceLang, $targetLang) {
        $TildeTM = new TildeTM(INIT::$TM_BASE_URL, AuthCookie::getToken());
        $user = AuthCookie::getCredentials();
        $memorySettings = self::settingsToArray(Jobs_JobDao::getMemorySetting($user['uid']));
        $memories = $TildeTM->getMemories();
        foreach($memories as &$mem) {
            $readValue = empty($memorySettings[$mem->id]['read']) ? true : $memorySettings[$mem->id]['read'];
            $mem->read = true && $readValue;
            $mem->concordance = true;
        }
        $tms_match = [];
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

    private static function settingsToArray($settings) {
        $settingsArray = [];
        foreach($settings as $setting) {
            $settingsArray[$setting['memory_id']] = [
                'read' => intval($setting['read_memory']) > 0,
                'concordance' => intval($setting['concordance_search']) > 0,
                ];
        }
        return $settingsArray;
    }

    public function __construct($baseUrl, $token)
    {
        $this->baseUrl = $baseUrl;
        $this->token = $token;
    }

    public function getMemories() {
        $this->writeLog('Getting TM list');
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
        $this->writeLog('TM called: ' . $this->baseUrl . $request);
        // Get cURL resource
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
        $this->writeLog('TM response');
        $this->writeLogData($header);
        $this->writeLogData($body);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($httpcode == 401) {
            throw new Unauthorized();
        }
        return json_decode($body);
    }


    protected function post($request, $data) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Authorization: Bearer ' . $this->token));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $this->baseUrl . $request);
        curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HEADER  , true);
        $resp = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return json_decode($resp);
    }

    protected function log_text($data) {
        file_put_contents('/var/tmp/worker.log', $data, FILE_APPEND);
        file_put_contents('/var/tmp/worker.log', "\n", FILE_APPEND);
    }

    protected function log($data) {
        file_put_contents('/var/tmp/worker.log', var_export($data, true), FILE_APPEND);
        file_put_contents('/var/tmp/worker.log', "\n", FILE_APPEND);
    }

    private function writeLog($text)
    {
        $oldFileName = Log::$fileName;
        Log::$fileName = "tm-debug.log";
        Log::doLog($text);
        Log::$fileName = $oldFileName;
    }

    private function writeLogData($data)
    {
        $this->writeLog(var_export($data, true));
    }
}
