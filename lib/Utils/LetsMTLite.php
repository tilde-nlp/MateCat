<?php

/**
 * Class LetsMTLite
 * @author YourLittleHelper <oskars@shibetec.com>
 * Simple class that provides the most basic interface with https://www.letsmt.eu
 */
class LetsMTLite {
    private $jwt;
    private $baseUrl;
    private $appId;

    public static function getMatch($mtSystem, $text, $jwt, $appId) {
        $LetsMTLite = new \LetsMTLite(INIT::$MT_BASE_URL, $jwt);
        $letsmtTranslation = $LetsMTLite->translate($mtSystem, $text, $appId);
        $matches = [];
        if ( !empty( $letsmtTranslation ) && $letsmtTranslation->translation != null ) {
            $matches[] = array(
                'created_by' => 'MT',
                'match' => '70',
                'translation' => $letsmtTranslation->translation,
                'raw_segment' => $text,
                'raw_translation' => $letsmtTranslation->translation,

            );
        }

        return $matches;
    }

    public function __construct($baseUrl, $jwt)
    {
        $this->baseUrl = $baseUrl;
        $this->jwt = $jwt;
    }

    public function getSystems($lang = 'en') {
        return $this->get('GetSystemList?appID=' . $this->appId . '&options=public&uiLanguage=' . $lang);
    }

    public function translate($systemId, $text, $appId) {
        $data = array(
            'appID' => $appId,
            'options' => 'widget=text,alignment,markSentences',
            'systemID' => $systemId,
            'text' => html_entity_decode($text)
        );
        return $this->post('TranslateEx', $data);
    }

    public function updateMT($systemId, $text, $translation, $appId)
    {
        $data = array(
            'appID' => $appId,
            'options' => '',
            'systemID' => $systemId,
            'text' => $text,
            'translation' => $translation
        );

        return $this->post('UpdateTranslation', $data);
    }

    protected function get($request) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Authorization: Bearer ' . $this->jwt));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL,$this->baseUrl . $request);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    protected function post($request, $data) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Authorization: Bearer ' . $this->jwt));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL,$this->baseUrl . $request);
        curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HEADER  , true);

        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($httpcode == 401) {
            throw new Unauthorized();
        }
        return json_decode($body);
    }

    protected function log_text($data) {
        file_put_contents('/var/tmp/worker.log', $data, FILE_APPEND);
        file_put_contents('/var/tmp/worker.log', "\n", FILE_APPEND);
    }

    protected function log($data) {
        file_put_contents('/var/tmp/worker.log', var_export($data, true), FILE_APPEND);
        file_put_contents('/var/tmp/worker.log', "\n", FILE_APPEND);
    }
}
