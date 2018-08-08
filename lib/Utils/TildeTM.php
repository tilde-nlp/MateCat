<?php

/**
 * Class LetsMTLite
 * @author YourLittleHelper <oskars@shibetec.com>
 * Simple class that provides the most basic interface with https://www.letsmt.eu
 */
class TildeTM {
    private $token;
    private $baseUrl;

    public function __construct($baseUrl, $token)
    {
        $this->baseUrl = $baseUrl;
        $this->token = $token;
    }

    public function getMemories() {
        return $this->get('tm');
    }

    public function getMatches($collection, $text, $source, $target) {
        return $this->get('tm/' . $collection . '/segments?' . http_build_query(
            array(
                'q' => $text,
                'sourceLang' => $source,
                'targetLang' => $target
            )
            ));
    }

    public function translate($systemId, $text) {
        $data = array(
            'appID' => $this->appId,
            'options' => 'widget=text,alignment,markSentences',
            'systemID' => $systemId,
            'text' => html_entity_decode($text)
        );
        return $this->post('TranslateEx', $data);
    }

    protected function get($request) {
        // Get cURL resource
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Authorization: Bearer ' . $this->token));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL,$this->baseUrl . $request);
        $resp = curl_exec($curl);
        curl_close($curl);
        return json_decode($resp);
    }


    protected function post($request, $data) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Authorization: Bearer ' . $this->token));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL,$this->baseUrl . $request);
        curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
        $resp = curl_exec($curl);
        curl_close($curl);
        return json_decode($resp);
    }
}
