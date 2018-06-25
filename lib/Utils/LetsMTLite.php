<?php

/**
 * Class LetsMTLite
 * @author YourLittleHelper <oskars@shibetec.com>
 * Simple class that provides the most basic interface with https://www.letsmt.eu
 */
class LetsMTLite {
    private $clientId;
    private $baseUrl;

    public function __construct($baseUrl, $clientId)
    {
        $this->clientId = $clientId;
        $this->baseUrl = $baseUrl;
    }

    public function getSystems() {
        return $this->makeRequest('GetSystemList');
    }

    public function translate($systemId, $text) {
        return $this->makeRequest('Translate?systemID='. $systemId .'&text=' . urlencode($text));
    }

    protected function makeRequest($request) {
        // Get cURL resource
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'client-id: ' . $this->clientId));
        // Will return the response, if false it print the response
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // Set the url
        curl_setopt($curl, CURLOPT_URL,$this->baseUrl . $request);
        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);
        return json_decode($resp);
    }
}
