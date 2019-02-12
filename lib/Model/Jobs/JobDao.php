<?php

use DataAccess\LoudArray;
use DataAccess\ShapelessConcreteStruct;

class Jobs_JobDao extends DataAccess_AbstractDao {

    const TABLE       = "jobs";
    const STRUCT_TYPE = "Jobs_JobStruct";

    protected static $auto_increment_field = [ 'id' ];
    protected static $primary_keys         = [ 'id', 'password' ];

    protected static $_sql_update_password = "UPDATE jobs SET password = :new_password WHERE id = :id AND password = :old_password ";

    protected static $_sql_get_jobs_by_project = "SELECT * FROM jobs WHERE id_project = ? ORDER BY id, job_first_segment ASC;";

    /**
     * This method is not static and used to cache at Redis level the values for this Job
     *
     * Use when counters of the job value are not important but only the metadata are needed
     *
     * XXX: Be careful, used by the ContributionSetStruct
     *
     * @see \AsyncTasks\Workers\SetContributionWorker
     * @see \Contribution\ContributionSetStruct
     *
     * @param Jobs_JobStruct $jobQuery
     *
     * @return DataAccess_IDaoStruct[]|Jobs_JobStruct[]
     */
    public function read( Jobs_JobStruct $jobQuery ){

        $stmt = $this->_getStatementForCache();
        return $this->_fetchObject( $stmt,
                $jobQuery,
                array(
                        'id_job' => $jobQuery->id,
                        'password' => $jobQuery->password
                )
        );

    }

    /**
     *
     * @return PDOStatement
     */
    protected function _getStatementForCache() {

        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare(
                "SELECT * FROM jobs WHERE " .
                " id = :id_job AND password = :password "
        );

        return $stmt;
    }

    /**
     * @param array $array_result
     *
     * @return DataAccess_IDaoStruct|DataAccess_IDaoStruct[]|void
     */
    protected function _buildResult( $array_result ){}

    /**
     * Destroy a cached object
     *
     * @param Jobs_JobStruct $jobQuery
     *
     * @return bool
     * @throws Exception
     */
    public function destroyCache( Jobs_JobStruct $jobQuery ){
        /*
        * build the query
        */
        $stmt = $this->_getStatementForCache();
        return $this->_destroyObjectCache( $stmt,
                array(
                        'id_job' => $jobQuery->id,
                        'password' => $jobQuery->password
                )
        );
    }

    /**
     * @param     $id_job
     * @param     $password
     * @param int $ttl
     *
     * @return Jobs_JobStruct
     */
    public static function getByIdAndPassword( $id_job, $password, $ttl = 0 ) {
        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare(
                "SELECT * FROM jobs WHERE " .
                " id = :id_job AND password = :password "
        );

        $thisDao = new self();
        return $thisDao->setCacheTTL( $ttl )->_fetchObject( $stmt, new Jobs_JobStruct(), [
                'id_job' => $id_job,
                'password' => $password
        ] )[ 0 ];

    }

    public function destroyCacheByProjectId( $project_id ) {
        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare( self::$_sql_get_jobs_by_project );

        return $this->_destroyObjectCache( $stmt, [ $project_id ] );
    }

    /**
     * @param     $id_project
     * @param int $ttl
     *
     * @return DataAccess_IDaoStruct[]|Jobs_JobStruct[]
     */
    public static function getByProjectId( $id_project, $ttl = 0 ) {

        $thisDao = new self();
        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare( self::$_sql_get_jobs_by_project );

        return $thisDao->setCacheTTL( $ttl )->_fetchObject( $stmt, new Jobs_JobStruct(), [ $id_project ] );

    }

    /**
     * @param int $id
     * @param string $password
     * @param int $ttl
     *
     * @return DataAccess_IDaoStruct[]|LoudArray[]
     * @internal param Chunks_ChunkStruct $chunk
     * @internal param $requestedWordsPerSplit
     *
     */
    public function getSplitData( $id, $password, $ttl = 0 ) {
        $conn = $this->getConnection()->getConnection();

        /**
         * Select all rows raw_word_count and eq_word_count
         * and their totals ( ROLLUP )
         * reserve also two columns for job_first_segment and job_last_segment
         *
         * +----------------+-------------------+---------+-------------------+------------------+
         * | raw_word_count | eq_word_count     | id      | job_first_segment | job_last_segment |
         * +----------------+-------------------+---------+-------------------+------------------+
         * |          26.00 |             22.10 | 2390662 |           2390418 |          2390665 |
         * |          30.00 |             25.50 | 2390663 |           2390418 |          2390665 |
         * |          48.00 |             40.80 | 2390664 |           2390418 |          2390665 |
         * |          45.00 |             38.25 | 2390665 |           2390418 |          2390665 |
         * |        3196.00 |           2697.25 |    NULL |           2390418 |          2390665 |  -- ROLLUP ROW
         * +----------------+-------------------+---------+-------------------+------------------+
         *
         */
        $stmt = $conn->prepare(
                "SELECT
                    SUM( raw_word_count ) AS raw_word_count,
                    SUM( eq_word_count ) AS eq_word_count,

                    job_first_segment, job_last_segment, s.id, s.show_in_cattool
                        FROM segments s
                        JOIN files_job fj ON fj.id_file = s.id_file
                        JOIN jobs j ON j.id = fj.id_job
                        LEFT  JOIN segment_translations st ON st.id_segment = s.id AND st.id_job = j.id
                        WHERE s.id BETWEEN j.job_first_segment AND j.job_last_segment
                        AND j.id = :id_job
                        AND j.password = :password
                        GROUP BY s.id
                    WITH ROLLUP"
        ) ;

        return $this
                ->setCacheTTL( $ttl )
                ->_fetchObject( $stmt, new LoudArray(), [ 'id_job' => $id, 'password' => $password ] )
                ;

    }

    /**
     *
     * @param int $id_job
     *
     * @param int $ttl
     *
     * @return DataAccess_IDaoStruct[]|Jobs_JobStruct[]
     */
    public static function getById( $id_job, $ttl = 0 ) {

        $thisDao = new self();
        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ? ORDER BY job_first_segment");
        return $thisDao->setCacheTTL( $ttl )->_fetchObject( $stmt, new Jobs_JobStruct(), [ $id_job ] );

    }

    /**
     * For now this is used by tests
     *
     * TODO Upgrade Project manager class with this method
     *
     * @param Jobs_JobStruct $jobStruct
     *
     * @return Jobs_JobStruct
     */
    public static function createFromStruct( Jobs_JobStruct $jobStruct ){

        $conn = Database::obtain()->getConnection();

        $jobStructToArray = $jobStruct->toArray();
        $columns = array_keys( $jobStructToArray );
        $values = array_values( $jobStructToArray );

        //clean null values
        foreach( $values as $k => $val ){
            if( is_null( $val ) ){
                unset( $values[ $k ] );
                unset( $columns[ $k ] );
            }
        }

        //reindex the array
        $columns = array_values( $columns );
        $values  = array_values( $values );

        \Database::obtain()->begin();

        $sql = 'INSERT INTO `jobs` ( ' . implode( ',', $columns ) . ' ) VALUES ( ' . implode( ',' , array_fill( 0, count( $values ), '?' ) ) . ' )';
        $stmt = $conn->prepare($sql);

        foreach( $values as $k => $v ){
            $stmt->bindValue( $k +1, $v ); //Columns/Parameters are 1-based
        }

        $stmt->execute();

        $job = static::getById( $conn->lastInsertId() )[0];

        $conn->commit();

        return $job;

    }

    public function setActiveSegment( $user_id, $segment_id ) {
        $job_id = self::getSegmentJobId($segment_id);
        $sql = " INSERT INTO  user_job_segment (user_id, job_id, segment_id) VALUES(:user_id, :job_id, :segment_id) ON DUPLICATE KEY UPDATE segment_id = :segment_id  ";

        $stmt = $this->con->getConnection()->prepare( $sql ) ;
        $stmt->execute(array('user_id' => $user_id, 'job_id' => $job_id, 'segment_id' => $segment_id ) ) ;

        return $stmt->rowCount();
    }


    public static function getSegmentJobId($segmentId) {

        $thisDao = new self();
        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("SELECT j.id AS jobId
        FROM segments s
        INNER JOIN files f ON f.id = s.id_file
        INNER JOIN projects p ON p.id = f.id_project
        INNER JOIN jobs j ON j.id_project = p.id
        WHERE s.id = ?
        LIMIT 1");
        
        $jobIdRecord = $thisDao->_fetchObjectNoCache( $stmt, new LoudArray(), [ $segmentId ] );
        $jobIdRecord = array_pop($jobIdRecord);
        return intval($jobIdRecord['jobId']);
    }

    public static function getSegmentJobPassword($segmentId) {

        $thisDao = new self();
        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("SELECT j.password AS jobPassword
        FROM segments s
        INNER JOIN files f ON f.id = s.id_file
        INNER JOIN projects p ON p.id = f.id_project
        INNER JOIN jobs j ON j.id_project = p.id
        WHERE s.id = ?
        LIMIT 1");
        
        $jobIdRecord = $thisDao->_fetchObjectNoCache( $stmt, new LoudArray(), [ $segmentId ] );
        $jobIdRecord = array_pop($jobIdRecord);
        return $jobIdRecord['jobPassword'];
    }

    public static function getActiveSegment( $user_id, $job_id, $ttl = 0 ) {

        $thisDao = new self();
        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("SELECT segment_id FROM user_job_segment WHERE user_id = ? AND job_id = ?");
        return $thisDao->setCacheTTL( $ttl )->_fetchObject( $stmt, new LoudArray(), [ $user_id, $job_id ] );

    }

    public static function getMtSystem($project_id) {

        $thisDao = new self();
        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("SELECT mt_system_id FROM projects WHERE id = ? ");
        return $thisDao->_fetchObjectNoCache( $stmt, new LoudArray(), [ $project_id ] );

    }

    public static function getUpdateMtForProject($project_id) {

        $thisDao = new self();
        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("SELECT update_mt FROM project_settings WHERE project_id = ? ");
        return $thisDao->_fetchObjectNoCache( $stmt, new LoudArray(), [ $project_id ] );

    }

    public static function getPretranslateData($job_id) {

        $thisDao = new self();
        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("SELECT tm_pretranslate, mt_pretranslate, start_tm_pretranslate, start_mt_pretranslate FROM jobs WHERE id = ? ");
        return $thisDao->_fetchObjectNoCache( $stmt, new LoudArray(), [ $job_id ] );

    }

    public static function saveMtSystem( $project_id, $system_id, $ttl = 0 ) {

        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("UPDATE projects SET mt_system_id = :system_id WHERE id = :project_id ");
        $stmt->execute(array('system_id' => $system_id, 'project_id' => $project_id));
        return $stmt->rowCount();
    }

    public static function saveTmPretranslate($userId, $pretranslate) {

        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("UPDATE users SET tm_pretranslate = :pretranslate WHERE uid = :user_id ");
        $stmt->execute(array('user_id' => $userId, 'pretranslate' => $pretranslate));
        return $stmt->rowCount();
    }

    public static function saveMtPretranslate($userId, $pretranslate) {

        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("UPDATE users SET mt_pretranslate = :pretranslate WHERE uid = :user_id ");
        $stmt->execute(array('user_id' => $userId, 'pretranslate' => $pretranslate));
        return $stmt->rowCount();
    }

    public static function saveUpdateMt($userId, $update_mt) {

        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("UPDATE users SET update_mt = :update_mt WHERE uid = :user_id ");
        $stmt->execute(array('user_id' => $userId, 'update_mt' => $update_mt));
        return $stmt->rowCount();
    }

    public static function saveUpdateMtForProject($projectId, $update_mt) {

        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("UPDATE project_settings SET update_mt = :update_mt WHERE project_id = :projectId ");
        $stmt->execute(array('projectId' => $projectId, 'update_mt' => $update_mt));
        return $stmt->rowCount();
    }

    public static function saveEditingTime( $job_id, $editingTime, $ttl = 0 ) {

        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("UPDATE jobs SET editing_time = :editing_time WHERE id = :job_id ");
        $stmt->execute(array('editing_time' => $editingTime, 'job_id' => $job_id));
        return $stmt->rowCount();
    }

    public static function removePretranslate($job_id) {
        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("UPDATE jobs SET tm_pretranslate = 0, mt_pretranslate = 0 WHERE id = :job_id ");
        $stmt->execute(array('job_id' => $job_id));
        return $stmt->rowCount();
    }

    public static function setPretranslating($job_id, $tm_pretranslate, $mt_pretranslate) {

        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("UPDATE jobs SET tm_pretranslate = :tm_pretranslate, mt_pretranslate = :mt_pretranslate, start_tm_pretranslate = 0, start_mt_pretranslate = 0 WHERE id = :job_id ");
        $stmt->execute(array('tm_pretranslate' => $tm_pretranslate, 'mt_pretranslate' => $mt_pretranslate, 'job_id' => $job_id));
        return $stmt->rowCount();
    }

    public static function removeStartPretranslate($job_id) {

        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("UPDATE jobs SET tm_pretranslate = start_tm_pretranslate, mt_pretranslate = start_mt_pretranslate, start_tm_pretranslate = 0, start_mt_pretranslate = 0 WHERE id = :job_id ");
        $stmt->execute(array('job_id' => $job_id));
        return $stmt->rowCount();
    }

    public static function getMemorySetting( $userId) {

        $thisDao = new self();
        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("SELECT * FROM memory_settings WHERE user_id = ? ");
        return $thisDao->_fetchObjectNoCache( $stmt, new LoudArray(), [ $userId ] );
    }

    public static function getMemorySettingsForProject( $projectId) {

        $thisDao = new self();
        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("SELECT pms.*
        FROM project_memory_settings pms
        INNER JOIN project_settings ps ON ps.id = pms.project_settings_id
        WHERE ps.project_id = ? ");
        return $thisDao->_fetchObjectNoCache( $stmt, new LoudArray(), [ $projectId ] );

    }

    public static function setTranslation($segmentId, $translation, $match, $source) {

        $conn = Database::obtain()->getConnection();
        $sql = "
        UPDATE segment_translations
        SET `translation` = :translation,
        `status` = 'DRAFT',
        translation_date = NOW(),
        suggestion_match = :match,
        suggestion_source = :source,
        save_match = :match,
        save_type = :source
        WHERE id_segment = :segment_id
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array(
            'translation' => $translation,
            'segment_id' => $segmentId,
            'match' => $match,
            'source' => $source));
        return $stmt->rowCount();

    }

    public static function getEmptySegments( $jobId, $jobPassword, $startSegmentId, $endSegmentId ) {

        $thisDao = new self();
        $conn = Database::obtain()->getConnection();
        $sql = "SELECT s.id AS id, s.segment AS segment
            FROM segment_translations st
            JOIN segments s ON st.id_segment = s.id
            JOIN jobs j ON st.id_job = j.id
            WHERE j.id = ?
            AND j.password = ?
            AND st.id_segment BETWEEN ? AND ?
            AND (st.translation = '' OR st.translation IS NULL OR st.status = 'NEW')";
        $stmt = $conn->prepare($sql);
        return $thisDao->_fetchObject( $stmt, new LoudArray(), [ $jobId, $jobPassword, $startSegmentId, $endSegmentId ] );

    }

    public static function saveMemorySetting( $user_id, $memory_id, $read, $write, $concordance, $ttl = 0 ) {

        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("INSERT INTO memory_settings (memory_id, read_memory, write_memory, concordance_search, user_id) VALUES (:memory_id, :read, :write, :concordance, :user_id)
                        ON DUPLICATE KEY UPDATE
                        read_memory = :read,
                        write_memory = :write,
                        concordance_search = :concordance");
        $stmt->execute(array(
            'memory_id' => $memory_id,
            'read' => $read ? 1 : 0,
            'write' => $write ? 1 : 0,
            'concordance' => $concordance ? 1 : 0,
            'user_id' => $user_id));
        return $stmt->rowCount();
    }

    public static function saveMemorySettingsForProject( $projectId, $memory_id, $read, $write) {

        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("INSERT INTO project_memory_settings (read_memory, write_memory, memory_id, project_settings_id)
        VALUES (:read_memory, :write_memory, :memory_id, (SELECT id FROM project_settings WHERE project_id = :project_id))
        ON DUPLICATE KEY UPDATE
        read_memory = :read_memory,
        write_memory = :write_memory");
        $stmt->execute(array(
            'memory_id' => $memory_id,
            'read_memory' => $read ? 1 : 0,
            'write_memory' => $write ? 1 : 0,
            'project_id' => $projectId));
        return $stmt->rowCount();
    }

    public static function getFileName( $job_id, $ttl = 0 ) {

        $thisDao = new self();
        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare("SELECT f.filename as name
FROM jobs j
INNER JOIN segments s ON s.id = j.job_first_segment
INNER JOIN files f ON f.id = s.id_file
WHERE j.id = ?");
        return $thisDao->setCacheTTL( $ttl )->_fetchObject( $stmt, new LoudArray(), [ $job_id ] );

    }

    /**
     * @param Projects_ProjectStruct $project
     * @param Users_UserStruct $user
     * @return int the number of rows affected by the statement
     */
    public function updateOwner( Projects_ProjectStruct $project, Users_UserStruct $user ) {
        $sql = " UPDATE jobs SET owner = :email WHERE id_project = :id_project ";

        $stmt = $this->con->getConnection()->prepare( $sql ) ;
        $stmt->execute(array('email' => $user->email, 'id_project' => $project->id ) ) ;

        return $stmt->rowCount();
    }

    public static function getTODOWords( Jobs_JobStruct $jStruct ){

        return array_sum([$jStruct->new_words, $jStruct->draft_words]);

    }

    public function changePassword( Jobs_JobStruct $jStruct, $new_password ){

        if( empty( $new_password ) ) throw new PDOException( "Invalid empty value: password." );

        $conn = \Database::obtain()->getConnection();
        $stmt = $conn->prepare( self::$_sql_update_password );
        $stmt->execute( [
                'id'           => $jStruct->id,
                'new_password' => $new_password,
                'old_password' => $jStruct->password
        ] );

        $jStruct->password = $new_password;

        $this->destroyCache( $jStruct );
        $this->destroyCacheByProjectId( $jStruct->id_project );

        return $jStruct;



    }

    /**
     * Job Worker gets segments to recount the Job Total weighted PEE
     *
     * @param Jobs_JobStruct $jStruct
     *
     * @return EditLog_EditLogSegmentStruct[]|DataAccess_IDaoStruct[]
     */
    public function getAllModifiedSegmentsForPee( Jobs_JobStruct $jStruct ){

        $query = "
            SELECT
              s.id,
              suggestion, 
              translation,
              raw_word_count,
              time_to_edit
            FROM segment_translations st
            JOIN segments s ON s.id = st.id_segment
            JOIN jobs j ON j.id = st.id_job
            WHERE id_job = :id_job 
                AND show_in_cattool = 1
                AND  password = :password
                AND st.status NOT IN( :status_new , :status_draft )
                AND time_to_edit/raw_word_count BETWEEN :edit_time_fast_cut AND :edit_time_slow_cut
                AND st.id_segment BETWEEN j.job_first_segment AND j.job_last_segment
        ";

        $stmt = $this->con->getConnection()->prepare( $query );

        return $this->_fetchObject( $stmt, new EditLog_EditLogSegmentStruct(), [
                'id_job'             => $jStruct->id,
                'password'           => $jStruct->password,
                'status_new'         => Constants_TranslationStatus::STATUS_NEW,
                'status_draft'       => Constants_TranslationStatus::STATUS_DRAFT,
                'edit_time_fast_cut' => 1000 * EditLog_EditLogModel::EDIT_TIME_FAST_CUT,
                'edit_time_slow_cut' => 1000 * EditLog_EditLogModel::EDIT_TIME_SLOW_CUT
        ] );

    }

    /**
     * @param Jobs_JobStruct $jStruct
     */
    public function updateJobWeightedPeeAndTTE( Jobs_JobStruct $jStruct ){

        $sql  = " UPDATE jobs 
                    SET avg_post_editing_effort = :avg_post_editing_effort, 
                        total_time_to_edit = :total_time_to_edit 
                    WHERE id = :id 
                    AND password = :password ";

        $stmt = Database::obtain()->getConnection()->prepare( $sql );
        $stmt->execute( [
                'avg_post_editing_effort' => $jStruct->avg_post_editing_effort,
                'total_time_to_edit'      => $jStruct->total_time_to_edit,
                'id'                      => $jStruct->id,
                'password'                => $jStruct->password
        ] );
        $stmt->closeCursor();

    }

    /**
     * @param $id_job
     * @param $password
     *
     * @return ShapelessConcreteStruct
     */
    public function getPeeStats( $id_job, $password ){

        $query = "
            SELECT
                avg_post_editing_effort / SUM( raw_word_count ) AS avg_pee
            FROM segment_translations st
            JOIN segments s ON s.id = st.id_segment
            JOIN jobs j ON j.id = st.id_job
            WHERE id_job = :id_job 
                AND show_in_cattool = 1
                AND  password = :password
                AND st.status NOT IN( :status_new , :status_draft )
                AND time_to_edit/raw_word_count BETWEEN :edit_time_fast_cut AND :edit_time_slow_cut
                AND st.id_segment BETWEEN j.job_first_segment AND j.job_last_segment
        ";

        $stmt = $this->con->getConnection()->prepare( $query );

        return $this->_fetchObject( $stmt, new ShapelessConcreteStruct(), [
                'id_job'             => $id_job,
                'password'           => $password,
                'status_new'         => Constants_TranslationStatus::STATUS_NEW,
                'status_draft'       => Constants_TranslationStatus::STATUS_DRAFT,
                'edit_time_fast_cut' => 1000 * EditLog_EditLogModel::EDIT_TIME_FAST_CUT,
                'edit_time_slow_cut' => 1000 * EditLog_EditLogModel::EDIT_TIME_SLOW_CUT
        ] )[ 0 ];

    }

    /**
     * @param $id_job
     * @param $password
     *
     * @return ShapelessConcreteStruct
     */
    public function getJobRawStats( $id_job, $password ){

        $queryAllSegments = "
          SELECT
            SUM(time_to_edit) AS tot_tte,
            SUM(raw_word_count) AS raw_words,
            SUM(time_to_edit)/SUM(raw_word_count) AS secs_per_word
          FROM segment_translations st
            JOIN segments s ON s.id = st.id_segment
            JOIN jobs j ON j.id = st.id_job
          WHERE id_job = :id_job 
            AND  password = :password
            AND st.id_segment BETWEEN j.job_first_segment AND j.job_last_segment
            ";

        $stmt = $this->con->getConnection()->prepare( $queryAllSegments );

        return $this->_fetchObject( $stmt, new ShapelessConcreteStruct(), [
                'id_job'             => $id_job,
                'password'           => $password
        ] )[ 0 ];

    }

    /**
     * @param Jobs_JobStruct $jobStruct
     *
     * @return PDOStatement
     */
    public function getSplitJobPreparedStatement( Jobs_JobStruct $jobStruct ){

        $jobCopy = $jobStruct->getArrayCopy();

        $columns      = implode( ", ", array_keys( $jobCopy ) );
        $values       = array_values( $jobCopy );
        $placeHolders = implode( ',', array_fill( 0, count( $values ), '?' ) );

        $values[] = $jobStruct->last_opened_segment;
        $values[] = $jobStruct->job_first_segment;
        $values[] = $jobStruct->job_last_segment;
        $values[] = $jobStruct->avg_post_editing_effort;

        $query = "INSERT INTO jobs ( $columns ) VALUES ( $placeHolders )
                        ON DUPLICATE KEY UPDATE
                        last_opened_segment = ?,
                        job_first_segment = ?,
                        job_last_segment = ?,
                        avg_post_editing_effort = ?
                ";

        $conn = Database::obtain()->getConnection();
        $stmt = $conn->prepare( $query );

        foreach( $values as $k => $v ){
            $stmt->bindValue( $k +1, $v ); //Columns/Parameters are 1-based
        }

        return $stmt;

    }

    public static function updateForMerge( Jobs_JobStruct $first_job, $newPass ){

        static::updateStruct( $first_job );

        if( $newPass ){
            $sql = " UPDATE jobs SET password = :new_password WHERE id = :id AND password = :old_password ";
            $stmt = Database::obtain()->getConnection()->prepare( $sql ) ;
            $stmt->execute( [
                    'new_password' => $newPass,
                    'id'           => $first_job->id,
                    'old_password' => $first_job->password
            ] );
            $first_job->password = $newPass;
        }

        return $first_job;

    }

    public static function deleteOnMerge( Jobs_JobStruct $first_job ){

        $conn = Database::obtain()->getConnection();
        $query = "DELETE FROM jobs WHERE id = :id AND password != :first_job_password "; //use new password
        $stmt = $conn->prepare( $query );

        return $stmt->execute( [
                'id' => $first_job->id,
                'first_job_password' => $first_job->password
        ] );

    }

}
