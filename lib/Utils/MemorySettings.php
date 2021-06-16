<?php

class MemorySettings {
    public static function getUserMemorySettings(): array {
        $user = AuthCookie::getCredentials();
        $JobsDao = new Jobs_JobDao();
        return self::mergeSettings($JobsDao->getMemorySetting($user['uid']), AuthCookie::getToken());
    }

    public static function getProjectMemorySettings($projectId): array {
        $JobsDao = new Jobs_JobDao();
        return self::mergeSettings($JobsDao->getMemorySettingsForProject($projectId), AuthCookie::getToken());
    }

    public static function getProjectMemorySettingsAsync($projectId, $token): array {
        $JobsDao = new Jobs_JobDao();
        return self::mergeSettings($JobsDao->getMemorySettingsForProject($projectId), $token);
    }

    private static function mergeSettings($rawMemorySettings, $token) {
        $memories = [];

        $TildeTM = new TildeTM(INIT::$TM_BASE_URL, $token);
        $userMemories = $TildeTM->getMemories();

        $JobsDao = new Jobs_JobDao();
        $memorySettings = self::settingsToArray($rawMemorySettings);

        foreach($userMemories as $userMemory) {
            $memory = [];
            $memory['id'] = $userMemory->id;
            $memory['name'] = $userMemory->name;
            $memory['canUpdate'] = $userMemory->canUpdate;
            $memory['readMemory'] = 1;
            $memory['writeMemory'] = substr($userMemory->id, -strlen(':Private')) === ':Private' ? 1 : 0;
            if (isset($memorySettings[$memory['id']])) {
                $memory['readMemory'] = intval($memorySettings[$memory['id']]['read_memory']);
                $memory['writeMemory'] = intval($memorySettings[$memory['id']]['write_memory']);
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