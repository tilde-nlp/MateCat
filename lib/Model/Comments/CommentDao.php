<?php

use Comments\OpenThreadsStruct;

class Comments_CommentDao extends DataAccess_AbstractDao {

    const TABLE       = "comments";
    const STRUCT_TYPE = "Comments_CommentStruct";

    const TYPE_COMMENT = 1;
    const TYPE_RESOLVE = 2;
    const TYPE_MENTION = 3;

    const SOURCE_PAGE_REVISE    = 2;
    const SOURCE_PAGE_TRANSLATE = 2;


    /**
     * Returns a structure that lists open threads count
     *
     * @return  array(
     *        'id_project' => 1,
     *        'password' => 'xxxx',
     *        'id_job' => 2,
     *        'id_segment' => 3,
     *        'count' => 42
     * );
     *
     * @param $projectIds
     *
     */
    public function getOpenThreadsForProjects( $projectIds ) {

        $ids = implode(',', array_map(function( $id ) {
            return (int) $id ;
        }, $projectIds ) );


        $sql = "
        SELECT id_project, jobs.password, id_job, COUNT( DISTINCT id_segment ) AS count
            FROM projects
            JOIN jobs ON jobs.id_project = projects.id
            JOIN comments ON comments.id_job = jobs.id
              AND comments.id_segment >= jobs.job_first_segment
              AND comments.id_segment <= jobs.job_last_segment
              AND comments.resolve_date IS NULL

        WHERE projects.id IN ( $ids )

        GROUP BY id_project, id_job, jobs.password
 ";

        $con = $this->con->getConnection() ;
        $stmt = $con->prepare( $sql ) ;

        return $this->_fetchObject( $stmt, new OpenThreadsStruct(), [] );

    }

    public function saveComment( $input ) {
        if ( $input->message_type == null ) {
            $input->message_type = self::TYPE_COMMENT;
        }

        $input->timestamp   = time();
        $input->create_date = date( 'Y-m-d H:i:s', $input->timestamp );

        $obj = $this->sanitize( $input );

        $this->validateComment( $obj );

        $query = " INSERT INTO comments " .
                " ( " .
                " id_job, id_segment, create_date, email, full_name, uid, " .
                " source_page, message_type, message ) " .
                " VALUES " .
                " ( " .
                implode( ", ", array(
                        $obj->id_job,
                        $obj->id_segment,
                        $obj->create_date,
                        $obj->email,
                        $obj->full_name,
                        $obj->uid,
                        $obj->source_page,
                        $obj->message_type,
                        $obj->message
                ) ) . " ) ";


        $this->con->query( $query );

        return $input;
    }

    public function resolveThread( $input ) {
        $input->message_type = self::TYPE_RESOLVE;
        $input->resolve_date = date( 'Y-m-d H:i:s' );

        $this->con->begin();

        try {
            $comment = $this->saveComment( $input );

            $obj = $this->sanitize( $input );

            $update = "UPDATE comments SET resolve_date = $obj->resolve_date " .
                    " WHERE id_segment = $obj->id_segment " .
                    " AND id_job = $obj->id_job " .
                    " AND resolve_date IS NULL ";

            Log::doLog( $update );

            $this->con->query( $update );

            $this->con->commit();
        } catch ( Exception $e ) {
            $err = $this->con->get_error();
            Log::doLog( "Error: " . var_export( $err, true ) );
            $this->con->rollback();
        }

        $input->thread_id   = $input->getThreadId();
        $input->create_date = $comment->create_date;
        $input->timestamp   = $comment->timestamp;

        return $input;
    }

    public function getThreadContributorUids( $input ) {
        $obj = $this->sanitize( $input );

        $query = "SELECT DISTINCT(uid) FROM " . self::TABLE .
                " WHERE id_job = $obj->id_job " .
                " AND id_segment = $obj->id_segment " .
                " AND uid IS NOT NULL ";

        if ( $input->uid ) {
            $query .= " AND uid <> $obj->uid ";
        }

        Log::doLog( $query );

        $this->con->query( $query );

        $arr_result = $this->_fetch_array( $query );

        return $arr_result;
    }

    public function getThreadsBySegments( $segments_id, $job_id ) {

        $prepare_str_segments_id = str_repeat( 'UNION SELECT ? ', count( $segments_id ) - 1 );

        $db             = Database::obtain()->getConnection();
        $comments_query = "SELECT * FROM comments 
        JOIN ( 
                SELECT ? as id_segment
                " . $prepare_str_segments_id . "
        ) AS SLIST USING( id_segment )
        WHERE message_type IN (1,2) AND id_job = ? ";

        $stmt = $db->prepare( $comments_query );
        $stmt->setFetchMode( PDO::FETCH_CLASS, "\Comments_BaseCommentStruct" );
        $stmt->execute( array_merge($segments_id, array($job_id)) );

        return $stmt->fetchAll();
    }

    /**
     *
     * @param Chunks_ChunkStruct $chunk
     *
     * @return Comments_BaseCommentStruct[]
     */

    public static function getCommentsForChunk( Chunks_ChunkStruct $chunk, $options = array() ) {

        $sql = "SELECT " .
                " id, uid, resolve_date, id_job, id_segment, create_date, full_name, " .
                " source_page, message_type, message, email, " .
                " IF ( resolve_date IS NULL, NULL,  " .
                " MD5( CONCAT( id_job, '-', id_segment, '-', resolve_date ) ) " .
                " ) AS thread_id FROM comments "  .
                " WHERE id_job = :id_job " .
                "  ";

        $params = array(
            'id_job' => $chunk->id
        ) ;

        if ( array_key_exists( 'from_id', $options ) && $options['from_id'] != null ) {
            $sql = $sql . " AND id >= :from_id " ;
            $params['from_id'] = $options['from_id'] ;
        }

        $conn = \Database::obtain()->getConnection();
        $stmt = $conn->prepare( $sql );
        $stmt->execute( $params );

        $stmt->setFetchMode( \PDO::FETCH_CLASS, '\Comments_BaseCommentStruct' );
        $stmt->execute();

        return $stmt->fetchAll();
    }


    /**
     *
     * Returns the list of comments in job.
     *
     * @deprecated this does not follow latest conventions, please don't use
     *             use getCommentsForChunk.
     *
     * TODO: refactor this, shoudl return an array of structs
     * @param $input
     *
     * @return array
     */
    public function getCommentsInJob( $input ) {
        $obj = $this->sanitize( $input );

        $query = $this->finderQuery() .
                " WHERE id_job = $obj->id_job " .
                " ORDER BY id_segment ASC, create_date ASC ";

        $this->con->query( $query );

        $arr_result = $this->_fetch_array( $query );

        return $this->_buildResult( $arr_result );
    }

    private function finderQuery() {
        return "SELECT " .
        " id_job, id_segment, create_date, full_name, resolve_date, " .
        " source_page, message_type, message, email, " .
        " UNIX_TIMESTAMP( create_date ) AS timestamp, " .
        " IF ( resolve_date IS NULL, NULL,  " .
        " MD5( CONCAT( id_job, '-', id_segment, '-', resolve_date ) ) " .
        " ) AS thread_id " .
        " FROM " . self::TABLE;
    }


    private function validateComment( $obj ) {
        if ( empty( $obj->message ) && $obj->message_type == self::TYPE_COMMENT ) {
            throw new Exception( "Comment message can't be blank." );
        }
        if ( empty( $obj->full_name ) ) {
            throw new Exception( "Full name can't be blank." );
        }
    }

    protected function _buildResult( $array_result ) {
        $result = array();

        foreach ( $array_result as $item ) {
            $build_arr = array(
                    'id_job'         => $item[ 'id_job' ],
                    'id_segment'     => $item[ 'id_segment' ],
                    'create_date'    => $item[ 'create_date' ],
                    'full_name'      => $item[ 'full_name' ],
                    'thread_id'      => $item[ 'thread_id' ],
                    'email'          => $item[ 'email' ],
                    'message_type'   => $item[ 'message_type' ],
                    'message'        => $item[ 'message' ],
                    'formatted_date' => self::formattedDate( $item[ 'create_date' ] ),
                    'timestamp'      => (int)$item[ 'timestamp' ]
            );

            $result[] = $build_arr;
        }

        return $result;
    }

    static function formattedDate( $time ) {
        return strftime( '%l:%M %p %e %b %Y UTC', strtotime( $time ) );
    }

    public function sanitize( $input ) {
        $cloned = clone $input;
        parent::_sanitizeInput( $input, self::STRUCT_TYPE );

        $cloned->id_job       = self::intWithNull( $input->id_job );
        $cloned->id_segment   = self::intWithNull( $input->id_segment );
        $cloned->uid          = self::intWithNull( $input->uid );
        $cloned->source_page  = self::intWithNull( $input->source_page );
        $cloned->message_type = self::intWithNull( $input->message_type );

        $cloned->first_segment = self::intWithNull( $input->first_segment );
        $cloned->last_segment  = self::intWithNull( $input->last_segment );

        $cloned->message      = self::escapeWithNull( trim( $input->message ) );
        $cloned->email        = self::escapeWithNull( $input->email );
        $cloned->full_name    = self::escapeWithNull( $input->full_name );
        $cloned->create_date  = self::escapeWithNull( $input->create_date );
        $cloned->resolve_date = self::escapeWithNull( $input->resolve_date );

        return $cloned;
    }

    public static function placeholdContent($content){
        $users_ids = self::getUsersIdFromContent($content);
        $userDao = new Users_UserDao( Database::obtain() );
        $users = $userDao->getByUids( $users_ids );
        foreach($users as $user){
            $content = str_replace("{@".$user->uid."@}", "@".$user->first_name, $content);
        }

        $content = str_replace("{@team@}", "@team", $content);
        return $content;
    }

    public static function getUsersIdFromContent($content){

        $users = [];

        preg_match_all( "/\{\@([\d]+)\@\}/", $content, $find_users );
        if ( isset( $find_users[ 1 ] ) ) {
            $users = $find_users[1];
        }

        return $users;

    }

    private static function escapeWithNull( $value ) {
        $conn = \Database::obtain();
        if ( $value !== null ) {
            return " '{$conn->escape( $value )}' ";
        } else {
            return "NULL";
        }
    }

    private static function intWithNull( $value ) {
        if ( $value === null ) {
            return "NULL";
        } else {
            return (int)$value;
        }
    }

}
