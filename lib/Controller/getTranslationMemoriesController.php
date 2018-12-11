<?php

class getTranslationMemoriesController extends ajaxController {
    public function doAction() {
        $this->writeLog('Called getTranslationMemories controller');
        $TildeTM = new TildeTM(INIT::$TM_BASE_URL, AuthCookie::getToken());
        $tildeMemories = $TildeTM->getMemories();
        $this->writeLog('TM list');
        $this->writeLogData($tildeMemories);

        $user = AuthCookie::getCredentials();
        $JobsDao = new Jobs_JobDao();
        $memorySettings = $JobsDao->getMemorySetting($user['uid']);
        $this->writeLog('Memory settings');
        $this->writeLogData($memorySettings);
        $indexedMemorySettings = [];
        foreach($memorySettings as $setting) {
            $indexedMemorySettings[$setting->memory_id] = $setting;
        }

        $this->writeLog('indexed memories');
        $this->writeLogData($indexedMemorySettings);

        $finalMemories = [];

        foreach ($tildeMemories as $memory) {
            $finalMemories[] = [
                'canUpdate' => $memory->canUpdate,
                'id' => $memory->id,
                'name' => $memory->name,
                'read' => isset($indexedMemorySettings[$memory->id]) ? $indexedMemorySettings[$memory->id]['read_memory'] > 0 : true,
                'concordance' => isset($indexedMemorySettings[$memory->id]) ? $indexedMemorySettings[$memory->id]['concordance_search'] > 0 : false,
                'write' => isset($indexedMemorySettings[$memory->id]) ? $indexedMemorySettings[$memory->id]['write_memory'] > 0 : false
            ];
        }
        $this->writeLog('final Memories');
        $this->writeLogData($finalMemories);

        echo json_encode($finalMemories);
    }

    public function finalize() {}

    private function writeLog($text)
    {
        $oldFileName = \Log::$fileName;
        \Log::$fileName = "tm-controller-debug.log";
        \Log::doLog($text);
        \Log::$fileName = $oldFileName;
    }

    private function writeLogData($data)
    {
        $this->writeLog(var_export($data, true));
    }
}