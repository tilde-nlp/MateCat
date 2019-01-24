<?php
class FileFilter {
    private $baseUrl;

    function __construct($baseUrl) {
        $this->baseUrl = $baseUrl;
    }
    public function convertFile($uploadedFile, $destination, $name, $from, $to) {
        $tmpFileName = uniqid();
        $tmpDirectory = $destination . DIRECTORY_SEPARATOR . $tmpFileName . DIRECTORY_SEPARATOR;
        mkdir($tmpDirectory, 0775);
        $fullTmpFileName = $tmpDirectory . 'fileToConvert.' . $from;
        $moveResult = move_uploaded_file( $uploadedFile, $fullTmpFileName);
        $targetDestination = $destination .  pathinfo($name, PATHINFO_FILENAME) . '.' . $to;
        $this->post($fullTmpFileName, $targetDestination, $from, $to);
        unlink($fullTmpFileName);
        return $targetDestination;
    }

    private function post($tmpFilePath, $convertedFileName, $from, $to) {
        $convertUrl = $this->baseUrl . '/converter/'. $from .'/' . $to;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $convertUrl);
        curl_setopt($curl,CURLOPT_POSTFIELDS, file_get_contents($tmpFilePath));
        curl_setopt($curl, CURLOPT_HEADER  , false);
        $resp = curl_exec($curl);
        curl_close($curl);

        file_put_contents($convertedFileName, $resp);
    }

    private function log($data)
    {
        $oldFileName = Log::$fileName;
        Log::$fileName = "file-filter.log";
        Log::doLog(var_export($data, true));
        Log::$fileName = $oldFileName;
    }
}