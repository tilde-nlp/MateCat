<?php

class MemorySettings {
    public static function getUserMemorySettings(): array {
        $memories = [];

        $TildeTM = new TildeTM(INIT::$TM_BASE_URL, AuthCookie::getToken());
        $userMemories = $TildeTM->getMemories();

        $JobsDao = new Jobs_JobDao();
        $user = AuthCookie::getCredentials();
        $memorySettings = self::settingsToArray($JobsDao->getMemorySetting($user['uid']));

        foreach($userMemories as $userMemory) {
            $memory = [];
            $memory['id'] = $userMemory->id;
            $memory['readMemory'] = 1;
            $memory['writeMemory'] = $userMemory->canWrite ? 1 : 0;
            if (isset($memorySettings[$memory->id])) {
                $memory['readMemory'] = $memorySettings[$memory->id]['read_memory'];
                $memory['writeMemory'] = $memorySettings[$memory->id]['write_memory'];
            }
            $memories[] = $memory;
        }

        return $memories;
    }

    private static function settingsToArray($settings): array {
        $settingsArray = [];
        foreach($settings as $setting) {
            $settingsArray[$setting['memory_id']] = $setting;
        }
        return $settingsArray;
    }

    private static function log($data)
    {
        $oldFileName = Log::$fileName;
        Log::$fileName = "memory-settings.log";
        Log::doLog(var_export($data, true));
        Log::$fileName = $oldFileName;
    }
}