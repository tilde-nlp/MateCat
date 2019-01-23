<?php
class FileFilter {
    public function convertFile($uploadedFile, $destination, $name) {
        $this->log('Filtering file: ' . $name);
        $tmpFileName = uniqid();
        $tmpDirectory = $destination . DIRECTORY_SEPARATOR . $tmpFileName . DIRECTORY_SEPARATOR;
        mkdir($tmpDirectory, 0775);
        $fullTmpFileName = $tmpDirectory . 'fileToConvert.rtf';
        $this->log('Tmp file path: ' . $fullTmpFileName);
        $moveResult = move_uploaded_file( $uploadedFile, $fullTmpFileName);
        $this->log('Move result: ');
        $this->log($moveResult);
        $targetDestination = $destination .  pathinfo($name, PATHINFO_FILENAME) . '.odt';
        $this->log('Converted file name: ' . $targetDestination);
        $this->post($fullTmpFileName, $targetDestination);
        unlink($fullTmpFileName);
        return $targetDestination;
    }

    private function post($tmpFilePath, $convertedFileName) {
        $convertUrl = 'http://hugodevcode.tilde.lv:5000/converter/rtf/odt';
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