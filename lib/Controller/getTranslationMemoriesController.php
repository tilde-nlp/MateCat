<?php

class getTranslationMemoriesController extends ajaxController {
    public function doAction() {
        $TildeTM = new TildeTM(INIT::$TM_BASE_URL, AuthCookie::getToken());
        $tildeMemories = $TildeTM->getMemories();

        $user = AuthCookie::getCredentials();
        $JobsDao = new Jobs_JobDao();
        $memorySettings = $JobsDao->getMemorySetting($user['uid']);
        $indexedMemorySettings = [];
        foreach($memorySettings as $setting) {
            $indexedMemorySettings[$setting->memory_id] = $setting;
        }

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

        echo json_encode($finalMemories);
    }

    public function finalize() {}
}