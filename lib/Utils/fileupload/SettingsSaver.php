<?php
class SettingsSaver {
    private $projectId;

    function __construct($projectId) {
        $this->projectId = $projectId;
    }
    public function save() {
        $projectSettings = [];
        $userData = AuthCookie::getCredentials();
        $UserDao = new Users_UserDao();
        $userStruct = $UserDao->getByUid($userData['uid']);
        $projectSettings['update_mt'] = intval($userStruct->update_mt);
        $projectSettings['project_id'] = intval($this->projectId);

        try {
            $projectSettingsId = $this->saveProjectSettings($projectSettings);
            $memories = \MemorySettings::getUserMemorySettings();
            $this->saveProjectMemorySettings($projectSettingsId, $memories);
        } catch(Throwable $e) {
            $this->log($e);
        }
    }

    private function saveProjectSettings($projectSettings) {
        $db = \Database::obtain();
        $query = 'INSERT INTO project_settings (update_mt, project_id) VALUES (:update_mt, :project_id);';
        $connection = $db->getConnection();
        $statement = $connection->prepare($query);
        $statement->execute($projectSettings);
        return $db->last_insert();
    }

    private function saveProjectMemorySettings($projectSettingsId, $memorySettings) {
        foreach($memorySettings as $setting) {
            unset($setting['name']);
            unset($setting['canUpdate']);
            $query = 'INSERT INTO project_memory_settings (read_memory, write_memory, memory_id, project_settings_id) VALUES 
                (:readMemory, :writeMemory, :id, :settingsId)
            ';
            $setting['settingsId'] = $projectSettingsId;
            $db = \Database::obtain();
            $connection = $db->getConnection();
            $statement = $connection->prepare($query);
            $statement->execute($setting);
        }
    }

    private function log($data)
    {
        $oldFileName = Log::$fileName;
        Log::$fileName = "settings-saver.log";
        Log::doLog(var_export($data, true));
        Log::$fileName = $oldFileName;
    }
}