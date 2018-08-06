<?php

/**
 * Class LetsMTLite
 * @author YourLittleHelper <oskars@shibetec.com>
 * Simple class that provides the most basic interface with https://www.letsmt.eu
 */
class LetsMTLite {
    private $clientId;
    private $baseUrl;
    private $appId;

    public function __construct($baseUrl, $clientId, $appId)
    {
        $this->clientId = $clientId;
        $this->baseUrl = $baseUrl;
        $this->appId = $appId;
    }

    public function getSystems() {
        return $this->get('GetSystemList?appID=' . $this->appId . '&options=public');
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
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'client-id: ' . $this->clientId));
        // Will return the response, if false it print the response
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, 1);
        // Set the url
        curl_setopt($curl, CURLOPT_URL,$this->baseUrl . $request);
        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);
        return json_decode($resp);
    }


    protected function post($request, $data) {
        // Get cURL resource
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'client-id: ' . $this->clientId));
        // Will return the response, if false it print the response
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // Set the url
        curl_setopt($curl, CURLOPT_URL,$this->baseUrl . $request);
        curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));

        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);
        return json_decode($resp);
    }
}
