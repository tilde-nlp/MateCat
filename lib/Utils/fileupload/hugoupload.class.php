<?php

define( "DIRSEP", "//" );
use ProjectQueue\Queue;

class UploadHandler {

    protected $options;
    private $project_name;
    private $source_language;
    private $target_language;
    private $job_subject;
    private $mt_system;
    private $mt_engine;
    private $tms_engine = 1;  //1 default MyMemory
    private $private_tm_key;
    private $private_tm_user;
    private $private_tm_pass;
    private $lang_detect_files;
    private $disable_tms_engine_flag;
    private $pretranslate_100;
    private $only_private;
    private $due_date;

    private $metadata;
    private $lang_handler;
    protected $result;
    protected $featureSet;
    protected $file_name;
    protected $source_lang;
    protected $target_lang;
    protected $segmentation_rule;
    protected $cookieDir;
    protected $intDir;
    protected $errDir;
    protected $userIsLogged;
    protected $guid;

    /**
     * @var \Teams\TeamStruct
     */
    private $team;

    /**
     * @var BasicFeatureStruct[]
     */
    private $projectFeatures = [];

    function __construct() {
        $this->setGuid();
        $this->options = [
                'script_url'              => $this->getFullUrl() . '/',
                'upload_dir'              => INIT::$QUEUE_PROJECT_REPOSITORY. DIRECTORY_SEPARATOR . $this->guid . DIRECTORY_SEPARATOR,
                'upload_url'              => $this->getFullUrl() . '/files/',
                'param_name'              => 'files',
            // Set the following option to 'POST', if your server does not support
            // DELETE requests. This is a parameter sent to the client:
                'delete_type'             => "", //'DELETE',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
                'max_file_size'           => INIT::$MAX_UPLOAD_FILE_SIZE,
                'min_file_size'           => 1,
            // The maximum number of files for the upload directory:
                'max_number_of_files'     => INIT::$MAX_NUM_FILES,
            // Set the following option to false to enable resumable uploads:
                'discard_aborted_uploads' => true,
        ];
        $this->userIsLogged = !empty(AuthCookie::getCredentials()['username']);
        // Check for project folder existence and mkdir it if necessary.
        if (!is_dir($this->options['upload_dir'])) {
            mkdir($this->options['upload_dir'], 0775, true);
        }
    }

    public function post() {

        if ( isset( $_REQUEST[ '_method' ] ) && $_REQUEST[ '_method' ] === 'DELETE' ) {
            return $this->delete();
        }



        $upload = isset( $_FILES[ $this->options[ 'param_name' ] ] ) ? $_FILES[ $this->options[ 'param_name' ] ] : null;

        $info = [];
        // param_name is an array identifier like "files[]",
        // $_FILES is a multi-dimensional array:
        foreach ( $upload[ 'tmp_name' ] as $index => $value ) {
            $info[] = $this->handle_file_upload(
                $upload[ 'tmp_name' ][ $index ],
                $upload[ 'name' ][ $index ],
                $upload[ 'size' ][ $index ],
                $upload[ 'type' ][ $index ],
                $upload[ 'error' ][ $index ],
                $index
            );
        }

        //check for server misconfiguration
        $uploadParams = ServerCheck::getInstance()->getUploadParams();

        if ( $_SERVER[ 'CONTENT_LENGTH' ] >= $uploadParams->getPostMaxSize() ) {

            $fp = fopen( "php://input", "r" );

            list( $trash, $boundary ) = explode( 'boundary=', $_SERVER[ 'CONTENT_TYPE' ] );

            $regexp = '/' . $boundary . '.*?filename="(.*)".*?Content-Type:(.*)\x{0D}\x{0A}\x{0D}\x{0A}/sm';

            $readBuff = fread( $fp, 1024 );
            while ( !preg_match( $regexp, $readBuff, $matches ) ) {
                $readBuff .= fread( $fp, 1024 );
            }
            fclose( $fp );

            $file        = new stdClass();
            $file->name  = $this->trim_file_name( $matches[ 1 ] );
            $file->size  = null;
            $file->type  = trim( $matches[ 2 ] );
            $file->error = "The file is too large. " .
                "Please Contact " . INIT::$SUPPORT_MAIL . " and report these details: " .
                "\"The server configuration does not conform with Matecat configuration. " .
                "Check for max header post size value in the virtualhost configuration or php.ini.\"";

            $info = [ $file ];

        } elseif ( $_SERVER[ 'CONTENT_LENGTH' ] >= $uploadParams->getUploadMaxFilesize() ) {
            $info[ 0 ]->error = "The file is too large.  " .
                "Please Contact " . INIT::$SUPPORT_MAIL . " and report these details: " .
                "\"The server configuration does not conform with Matecat configuration. " .
                "Check for max file upload value in the virtualhost configuration or php.ini.\"";
        }
        //check for server misconfiguration

        $this->handle_convert();
        $this->handle_project_create();
        $this->create_project();

        header( 'Vary: Accept' );
        $json     = json_encode( $this->result );
        $redirect = isset( $_REQUEST[ 'redirect' ] ) ?
            stripslashes( $_REQUEST[ 'redirect' ] ) : null;

        if ( $redirect ) {
            header( 'Location: ' . sprintf( $redirect, rawurlencode( $json ) ) );
            return;
        }

        if ( isset( $_SERVER[ 'HTTP_ACCEPT' ] ) && ( strpos( $_SERVER[ 'HTTP_ACCEPT' ], 'application/json' ) !== false ) ) {
            header( 'Content-type: application/json' );
        } else {
            header( 'Content-type: text/plain' );
        }

        echo $json;
    }

    protected function handle_file_upload( $uploaded_file, $name, $size, $type, $error, $index = null ) {

        Log::$fileName = "upload.log";
        Log::doLog( $uploaded_file );

        $file       = new stdClass();
        $file->name = $this->trim_file_name( $name );
        $file->size = intval( $size );
        $file->tmp_name = $uploaded_file;
        //$file->type = $type; // Override and ignore the client type definition
        $file->type = mime_content_type( $file->tmp_name );

        if ( $this->validate( $uploaded_file, $file, $error, $index ) ) {
            $destination = $this->options['upload_dir'];
            $file->full_path   = $destination . $file->name;
            $res = move_uploaded_file( $uploaded_file, $file->full_path );
            Log::doLog( $res );

            $file_size = filesize( $file->full_path );
            if ( $file_size === $file->size ) {
                $file->url = $destination . rawurlencode( $file->name );
            } else {
                unlink( $file->full_path );
                $file->error = 'abort';
            }
            $file->size = $file_size;
            $this->set_file_delete_url( $file );

            Log::doLog( "Size on disk: $file_size - Declared size: $file->size" );

            //As opposed with isset(), property_exists() returns TRUE even if the property has the value NULL.
            if ( property_exists( $file, 'error' ) ) {
                // FORMAT ERROR MESSAGE
                switch ( $file->error ) {
                    case 'abort':
                        $file->error = "File upload failed. Refresh the page using CTRL+R (or CMD+R) and try again.";
                        break;
                    default:
                        null;
                }
            }

        }

        return $file;
    }

    public function handle_convert() {
        $this->featureSet = new FeatureSet();
        $this->featureSet->loadFromUserEmail( AuthCookie::getCredentials()['username'] ) ;

        $filterArgs = array(
            'file_name'         => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags'  => FILTER_FLAG_STRIP_LOW // | FILTER_FLAG_STRIP_HIGH
            ),
            'source_lang'       => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags'  => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
            ),
            'target_lang'       => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags'  => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
            ),
            'segmentation_rule' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags'  => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
            )
        );

        $postInput = filter_input_array( INPUT_POST, $filterArgs );

        $this->file_name         = $postInput[ 'file_name' ];
        $this->source_lang       = $postInput[ "source_lang" ];
        $this->target_lang       = $postInput[ "target_lang" ];
        $this->segmentation_rule = $postInput[ "segmentation_rule" ];

        if ( $this->segmentation_rule == "" ) {
            $this->segmentation_rule = null;
        }

        $this->cookieDir = $this->guid;
        $this->intDir    = $this->options['upload_dir'];
        $this->errDir    = INIT::$STORAGE_DIR . DIRECTORY_SEPARATOR . 'conversion_errors' . DIRECTORY_SEPARATOR . $this->cookieDir;

        $this->result[ 'code' ] = 0; // No Good, Default

        $this->lang_handler = Langs_Languages::getInstance();
        $this->validateSourceLang();
        $this->validateTargetLangs();

        if ( empty( $this->file_name ) ) {
            $this->result[ 'code' ]     = -1; // No Good, Default
            $this->result[ 'errors' ][] = array( "code" => -1, "message" => "Error: missing file name." );
        }

        if( !empty( $this->result[ 'errors' ] ) ){
            return false;
        }

        $conversionHandler = new ConversionHandler();
        $conversionHandler->setFileName( $this->file_name );
        $conversionHandler->setSourceLang( $this->source_lang );
        $conversionHandler->setTargetLang( $this->target_lang );
        $conversionHandler->setSegmentationRule( $this->segmentation_rule );
        $conversionHandler->setCookieDir( $this->cookieDir );
        $conversionHandler->setIntDir( $this->intDir );
        $conversionHandler->setErrDir( $this->errDir );
        $conversionHandler->setFeatures( $this->featureSet );
        $conversionHandler->setUserIsLogged( true );

        $conversionHandler->doAction();

        $this->result = $conversionHandler->getResult();

        ( isset( $this->result[ 'errors' ] ) ) ? null : $this->result[ 'errors' ] = array();

        if ( count( $this->result[ 'errors' ] ) == 0 ) {
            $this->result[ 'code' ] = 1;
        } else {
            $this->result[ 'errors' ] = array_values( $this->result[ 'errors' ] );
        }
    }

    protected function getFullUrl() {
        $https = !empty( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] !== 'off';
        return
                ( $https ? 'https://' : 'http://' ) .
                ( !empty( $_SERVER[ 'REMOTE_USER' ] ) ? $_SERVER[ 'REMOTE_USER' ] . '@' : '' ) .
                ( isset( $_SERVER[ 'HTTP_HOST' ] ) ? $_SERVER[ 'HTTP_HOST' ] : ( $_SERVER[ 'SERVER_NAME' ] .
                        ( $https && $_SERVER[ 'SERVER_PORT' ] === 443 ||
                        $_SERVER[ 'SERVER_PORT' ] === 80 ? '' : ':' . $_SERVER[ 'SERVER_PORT' ] ) ) ) .
                substr( $_SERVER[ 'SCRIPT_NAME' ], 0, strrpos( $_SERVER[ 'SCRIPT_NAME' ], '/' ) );
    }

    protected function set_file_delete_url( $file ) {
        $file->delete_url  = $this->options[ 'script_url' ]
                . '?file=' . rawurlencode( $file->name );
        $file->delete_type = $this->options[ 'delete_type' ];
        if ( $file->delete_type !== 'DELETE' ) {
            $file->delete_url .= '&_method=DELETE';
        }
    }

    protected function get_file_object( $file_name ) {
        $file_path = $this->options[ 'upload_dir' ] . $file_name;
        if ( is_file( $file_path ) && $file_name[ 0 ] !== '.' ) {

            $file       = new stdClass();
            $file->name = $file_name;
            $file->size = filesize( $file_path );
            $file->url  = $this->options[ 'upload_url' ] . rawurlencode( $file->name );
            $this->set_file_delete_url( $file );

            return $file;
        }

        return null;
    }

    protected function get_file_objects() {
        return array_values( array_filter( array_map(
                [ $this, 'get_file_object' ], scandir( $this->options[ 'upload_dir' ] )
        ) ) );
    }

    protected function validate( $uploaded_file, $file, $error, $index ) {
        //TODO: these errors are shown in the UI but are not user friendly.

        if ( $error ) {
            $file->error = $error;

            return false;
        }

        if ( !$this->_isValidFileName( $file ) ) {
            $file->error = "Invalid File Name";

            return false;
        }

        if ( $file->type !== null ) {
            if ( !$this->_isRightMime( $file ) && ( !isset( $file->error ) || empty( $file->error ) ) ) {
                $file->error = "Mime type Not Allowed";
                return false;
            }
        }

        if ( !$this->_isRightExtension( $file ) && ( !isset( $file->error ) || empty( $file->error ) ) ) {
            $file->error = "File Extension Not Allowed";

            return false;
        }


        if ( !$file->name ) {
            $file->error = 'missingFileName';

            return false;
        } else {
            if ( mb_strlen( $file->name ) > INIT::$MAX_FILENAME_LENGTH ) {
                $file->error = "filenameTooLong";

                return false;
            }
        }

        if ( $uploaded_file && is_uploaded_file( $uploaded_file ) ) {
            $file_size = filesize( $uploaded_file );
        } else {
            $file_size = $_SERVER[ 'CONTENT_LENGTH' ];
        }

        if ( $this->options[ 'max_file_size' ] && (
                        $file_size > $this->options[ 'max_file_size' ] ||
                        $file->size > $this->options[ 'max_file_size' ] )
        ) {
            $file->error = 'maxFileSize';

            return false;
        }

        if ( $this->options[ 'min_file_size' ] &&
                $file_size < $this->options[ 'min_file_size' ]
        ) {
            $file->error = 'minFileSize';

            return false;
        }

        if ( is_int( $this->options[ 'max_number_of_files' ] ) && (
                        count( $this->get_file_objects() ) >= $this->options[ 'max_number_of_files' ] )
        ) {
            $file->error = 'maxNumberOfFiles';
            return false;
        }

        return true;
    }

    protected function upcount_name_callback( $matches ) {
        $index = isset( $matches[ 1 ] ) ? intval( $matches[ 1 ] ) + 1 : 1;
        $ext   = isset( $matches[ 2 ] ) ? $matches[ 2 ] : '';

        return '_(' . $index . ')' . $ext;
    }

    protected function upcount_name( $name ) {
        return preg_replace_callback(
                '/(?:(?:_\(([\d]+)\))?(\.[^.]+))?$/', [ $this, 'upcount_name_callback' ], $name, 1
        );
    }

    private function my_basename( $param, $suffix = null ) {
        if ( $suffix ) {
            $tmpstr = ltrim( substr( $param, strrpos( $param, DIRSEP ) ), DIRSEP );
            if ( ( strpos( $param, $suffix ) + strlen( $suffix ) ) == strlen( $param ) ) {
                return str_ireplace( $suffix, '', $tmpstr );
            } else {
                return ltrim( substr( $param, strrpos( $param, DIRSEP ) ), DIRSEP );
            }
        } else {
            return ltrim( substr( $param, strrpos( $param, DIRSEP ) ), DIRSEP );
        }
    }

    /**
     * Remove path information and dots around the filename, to prevent uploading
     * into different directories or replacing hidden system files.
     * Also remove control characters and spaces (\x00..\x20) around the filename:
     */
    protected function trim_file_name( $name ) {
        $name = stripslashes( $name );

        $file_name = trim( $this->my_basename( $name ), ".\x00..\x20" );

        //remove spaces
        $file_name = str_replace( [ " ", " " ], "_", $file_name );

        if ( $this->options[ 'discard_aborted_uploads' ] ) {
            while ( is_file( $this->options[ 'upload_dir' ] . $file_name ) ) {
                $file_name = $this->upcount_name( $file_name );
            }
        }

        //echo "name3 $file_name\n";
        return $file_name;
    }

    public function get() {
        $file_name = isset( $_REQUEST[ 'file' ] ) ?
                basename( stripslashes( $_REQUEST[ 'file' ] ) ) : null;
        if ( $file_name ) {
            $info = $this->get_file_object( $file_name );
        } else {
            $info = $this->get_file_objects();
        }
        header( 'Content-type: application/json' );
        echo json_encode( $info );
    }

    protected function handle_project_create() {
        $filterArgs = [
            'file_name'          => [ 'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW ],
            'project_name'       => [ 'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW ],
            'source_language'    => [ 'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW ],
            'target_language'    => [ 'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW ],
            'job_subject'        => [ 'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW ],
            'mt_system'        => [ 'filter' => FILTER_SANITIZE_STRING ],
            'due_date'           => [ 'filter' => FILTER_VALIDATE_INT ],
            'mt_engine'          => [ 'filter' => FILTER_VALIDATE_INT ],
            'disable_tms_engine' => [ 'filter' => FILTER_VALIDATE_BOOLEAN ],

            'private_tm_user'   => [ 'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW ],
            'private_tm_pass'   => [ 'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW ],
            'lang_detect_files' => [
                'filter'  => FILTER_CALLBACK,
                'options' => "Utils::filterLangDetectArray"
            ],
            'private_tm_key'    => [ 'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW ],
            'pretranslate_100'  => [ 'filter' => FILTER_VALIDATE_INT ],
            'id_team'           => [ 'filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_SCALAR ],

            'project_completion' => [ 'filter' => FILTER_VALIDATE_BOOLEAN ], // features customization
            'get_public_matches' => [ 'filter' => FILTER_VALIDATE_BOOLEAN ], // disable public TM matches

        ];

        $filterArgs = $this->__addFilterForMetadataInput( $filterArgs );

        $__postInput = filter_input_array( INPUT_POST, $filterArgs );

        $this->setProjectFeaturesFromPostValues( $__postInput );

        //first we check the presence of a list from tm management panel
        $array_keys = null;
        if (!empty($_POST[ 'private_keys_list' ])) {
            $array_keys = json_decode( $_POST[ 'private_keys_list' ], true );
            $array_keys = array_merge( $array_keys[ 'ownergroup' ], $array_keys[ 'mine' ], $array_keys[ 'anonymous' ] );
        }

        //if a string is sent by the client, transform it into a valid array
        if ( !empty( $__postInput[ 'private_tm_key' ] ) ) {
            $__postInput[ 'private_tm_key' ] = [
                [
                    'key'  => trim( $__postInput[ 'private_tm_key' ] ),
                    'name' => null,
                    'r'    => true,
                    'w'    => true
                ]
            ];
        } else {
            $__postInput[ 'private_tm_key' ] = [];
        }

        if ( $array_keys ) { // some keys are selected from panel

            //remove duplicates
            foreach ( $array_keys as $pos => $value ) {
                if ( isset( $__postInput[ 'private_tm_key' ][ 0 ][ 'key' ] )
                    && $__postInput[ 'private_tm_key' ][ 0 ][ 'key' ] == $value[ 'key' ]
                ) {
                    //same key was get from keyring, remove
                    $__postInput[ 'private_tm_key' ] = [];
                }
            }

            //merge the arrays
            $private_keyList = array_merge( $__postInput[ 'private_tm_key' ], $array_keys );


        } else {
            $private_keyList = $__postInput[ 'private_tm_key' ];
        }

        $__postPrivateTmKey = array_filter( $private_keyList, [ "self", "sanitizeTmKeyArr" ] );

        // NOTE: This is for debug purpose only,
        // NOTE: Global $_POST Overriding from CLI
        // $__postInput = filter_var_array( $_POST, $filterArgs );

        $this->file_name               = $__postInput[ 'file_name' ];       // da cambiare, FA SCHIFO la serializzazione
        $this->project_name            = $__postInput[ 'project_name' ];
        $this->source_language         = $__postInput[ 'source_language' ];
        $this->target_language         = $__postInput[ 'target_language' ];
        $this->job_subject             = $__postInput[ 'job_subject' ];
        $this->mt_system             = $__postInput[ 'mt_system' ];
        $this->mt_engine               = ( $__postInput[ 'mt_engine' ] != null ? $__postInput[ 'mt_engine' ] : 0 );       // null NON è ammesso
        $this->disable_tms_engine_flag = $__postInput[ 'disable_tms_engine' ]; // se false allora MyMemory
        $this->private_tm_key          = $__postPrivateTmKey;
        $this->private_tm_user         = $__postInput[ 'private_tm_user' ];
        $this->private_tm_pass         = $__postInput[ 'private_tm_pass' ];
        $this->lang_detect_files       = $__postInput[ 'lang_detect_files' ];
        $this->pretranslate_100        = $__postInput[ 'pretranslate_100' ];
        $this->only_private            = ( is_null( $__postInput[ 'get_public_matches' ] ) ? false : !$__postInput[ 'get_public_matches' ] );
        $this->due_date                = ( empty( $__postInput[ 'due_date' ] ) ? null : Utils::mysqlTimestamp( $__postInput[ 'due_date' ] ) );

        $this->__setMetadataFromPostInput( $__postInput );

        if ( $this->disable_tms_engine_flag ) {
            $this->tms_engine = 0; //remove default MyMemory
        }

        if ( empty( $this->file_name ) ) {
            $this->result[ 'errors' ][] = [ "code" => -1, "message" => "Missing file name." ];
        }

        if ( empty( $this->job_subject ) ) {
            $this->result[ 'errors' ][] = [ "code" => -5, "message" => "Missing job subject." ];
        }

        if ( $this->pretranslate_100 !== 1 && $this->pretranslate_100 !== 0 ) {
            $this->result[ 'errors' ][] = [ "code" => -6, "message" => "invalid pretranslate_100 value" ];
        }


        $this->lang_handler = Langs_Languages::getInstance();
        $this->__validateSourceLang();
        $this->__validateTargetLangs();
        $this->__validateUserMTEngine();

        $this->__setTeam( $__postInput[ 'id_team' ] );
    }

    public function create_project() {
        //check for errors. If there are, stop execution and return errors.
        if ( count( @$this->result[ 'errors' ] ) ) {
            return false;
        }

        $arFiles = explode( '@@SEP@@', html_entity_decode( $this->file_name, ENT_QUOTES, 'UTF-8' ) );

        $default_project_name = $arFiles[ 0 ];
        if ( count( $arFiles ) > 1 ) {
            $default_project_name = "MATECAT_PROJ-" . date( "Ymdhi" );
        }

        if ( empty( $this->project_name ) ) {
            $this->project_name = $default_project_name;
        }

        $projectManager = new ProjectManager();

        $projectStructure = $projectManager->getProjectStructure();

        $projectStructure[ 'project_name' ]         = $this->project_name;
        $projectStructure[ 'private_tm_key' ]       = $this->private_tm_key;
        $projectStructure[ 'private_tm_user' ]      = $this->private_tm_user;
        $projectStructure[ 'private_tm_pass' ]      = $this->private_tm_pass;
        $projectStructure[ 'uploadToken' ]          = $this->guid;
        $projectStructure[ 'array_files' ]          = $arFiles; //list of file name
        $projectStructure[ 'source_language' ]      = $this->source_language;
        $projectStructure[ 'target_language' ]      = explode( ',', $this->target_language );
        $projectStructure[ 'job_subject' ]          = $this->job_subject;
        $projectStructure[ 'mt_engine' ]            = $this->mt_engine;
        $projectStructure[ 'tms_engine' ]           = $this->tms_engine;
        $projectStructure[ 'status' ]               = Constants_ProjectStatus::STATUS_NOT_READY_FOR_ANALYSIS;
        $projectStructure[ 'lang_detect_files' ]    = $this->lang_detect_files;
        $projectStructure[ 'skip_lang_validation' ] = true;
        $projectStructure[ 'pretranslate_100' ]     = $this->pretranslate_100;
        $projectStructure[ 'only_private' ]         = $this->only_private;
        $projectStructure[ 'due_date' ]             = $this->due_date;
        $projectStructure[ 'mt_system_id' ]         = $this->mt_system;


        $projectStructure[ 'user_ip' ]   = Utils::getRealIpAddr();
        $projectStructure[ 'HTTP_HOST' ] = INIT::$HTTPHOST;

        //TODO enable from CONFIG
        $projectStructure[ 'metadata' ] = $this->metadata;

        $user = AuthCookie::getCredentials();
        $projectStructure[ 'userIsLogged' ] = true;
        $projectStructure[ 'uid' ]          = $user['uid'];
        $projectStructure[ 'id_customer' ]  = $user['username'];
        $projectStructure[ 'owner' ]        = $user['username'];
        $projectManager->setTeam( $this->team ); // set the team object to avoid useless query

        //set features override
        $projectStructure[ 'project_features' ] = $this->projectFeatures;

        //reserve a project id from the sequence
        $projectStructure[ 'id_project' ] = Database::obtain()->nextSequence( Database::SEQ_ID_PROJECT )[ 0 ];
        $projectStructure[ 'ppassword' ]  = $projectManager->generatePassword();

        Queue::sendProject( $projectStructure );

        $this->__assignLastCreatedPid( $projectStructure[ 'id_project' ] );

        $this->result[ 'data' ] = [
            'id_project' => $projectStructure[ 'id_project' ],
            'password'   => $projectStructure[ 'ppassword' ]
        ];

    }

    private static function sanitizeTmKeyArr( $elem ) {

        $elem = TmKeyManagement_TmKeyManagement::sanitize( new TmKeyManagement_TmKeyStruct( $elem ) );

        return $elem->toArray();

    }

    private function setGuid() {
        $this->guid = Utils::create_guid();
    }

    private function __assignLastCreatedPid( $pid ) {
        $_SESSION[ 'redeem_project' ]   = false;
        $_SESSION[ 'last_created_pid' ] = $pid;
    }

    private function __setTeam() {
        $user = new Users_UserStruct();
        $user->uid   = AuthCookie::getCredentials()['uid'];
        $user->email   = AuthCookie::getCredentials()['username'];
        $this->team = $user->getPersonalTeam();
    }

    private function __validateUserMTEngine() {

        if ( array_search( $this->mt_engine, [ 0, 1, 2 ] ) === false ) {

            if ( !$this->userIsLogged ) {
                $this->result[ 'errors' ][] = [ "code" => -2, "message" => "Invalid MT Engine." ];

                return;
            }

            $engineQuery      = new EnginesModel_EngineStruct();
            $engineQuery->id  = $this->mt_engine;
            $engineQuery->uid = $this->user->uid;
            $enginesDao       = new EnginesModel_EngineDAO();
            $engine           = $enginesDao->setCacheTTL( 60 * 5 )->read( $engineQuery );

            if ( empty( $engine ) ) {
                $this->result[ 'errors' ][] = [ "code" => -2, "message" => "Invalid MT Engine." ];
            }

        }

    }

    private function __validateTargetLangs() {
        $targets = explode( ',', $this->target_language );
        $targets = array_map( 'trim', $targets );
        $targets = array_unique( $targets );

        if ( empty( $targets ) ) {
            $this->result[ 'errors' ][] = [ "code" => -4, "message" => "Missing target language." ];
        }

        try {
            foreach ( $targets as $target ) {
                $this->lang_handler->validateLanguage( $target );
            }
        } catch ( Exception $e ) {
            $this->result[ 'errors' ][] = [ "code" => -4, "message" => $e->getMessage() ];
        }

        $this->target_language = implode( ',', $targets );
    }

    private function __validateSourceLang() {
        try {
            $this->lang_handler->validateLanguage( $this->source_language );
        } catch ( Exception $e ) {
            $this->result[ 'errors' ][] = [ "code" => -3, "message" => $e->getMessage() ];
        }
    }

    private function __addFilterForMetadataInput( $filterArgs ) {
        $filterArgs = array_merge( $filterArgs, [
            'lexiqa'            => [ 'filter' => FILTER_VALIDATE_BOOLEAN ],
            'speech2text'       => [ 'filter' => FILTER_VALIDATE_BOOLEAN ],
            'tag_projection'    => [ 'filter' => FILTER_VALIDATE_BOOLEAN ],
            'segmentation_rule' => [
                'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
            ],
        ] );

        $filterArgs = $this->featureSet->filter( 'filterCreateProjectInputFilters', $filterArgs );

        return $filterArgs;
    }

    private function __setMetadataFromPostInput( $__postInput ) {
        $options = [];

        if ( isset( $__postInput[ 'lexiqa' ] ) ) {
            $options[ 'lexiqa' ] = $__postInput[ 'lexiqa' ];
        }
        if ( isset( $__postInput[ 'speech2text' ] ) ) {
            $options[ 'speech2text' ] = $__postInput[ 'speech2text' ];
        }
        if ( isset( $__postInput[ 'tag_projection' ] ) ) {
            $options[ 'tag_projection' ] = $__postInput[ 'tag_projection' ];
        }
        if ( isset( $__postInput[ 'segmentation_rule' ] ) ) {
            $options[ 'segmentation_rule' ] = $__postInput[ 'segmentation_rule' ];
        }

        $this->metadata = $options;

        $this->metadata = $this->featureSet->filter( 'createProjectAssignInputMetadata', $this->metadata, [
            'input' => $__postInput
        ] );
    }

    private function setProjectFeaturesFromPostValues( $__postInput ) {
        // change project features

        if ( !empty( $__postInput[ 'project_completion' ] ) ) {
            $feature                 = new BasicFeatureStruct();
            $feature->feature_code   = 'project_completion';
            $this->projectFeatures[] = $feature;
        }

        $this->projectFeatures = $this->featureSet->filter(
            'filterCreateProjectFeatures', $this->projectFeatures, $__postInput, $this->userIsLogged
        );
    }

    private function validateSourceLang() {
        try {
            $this->lang_handler->validateLanguage( $this->source_lang );
        } catch ( Exception $e ) {
            $this->result[ 'errors' ][]    = [ "code" => -3, "message" => $e->getMessage() ];
        }
    }

    private function validateTargetLangs() {
        $targets = explode( ',', $this->target_lang );
        $targets = array_map( 'trim', $targets );
        $targets = array_unique( $targets );

        if ( empty( $targets ) ) {
            $this->result[ 'errors' ][]    = [ "code" => -4, "message" => "Missing target language." ];
        }

        try {

            foreach ( $targets as $target ) {
                $this->lang_handler->validateLanguage( $target );
            }

        } catch ( Exception $e ) {
            $this->result[ 'errors' ][]    = [ "code" => -4, "message" => $e->getMessage() ];
        }

        $this->target_lang = implode( ',', $targets );
    }

    public function delete() {

        /*
         * BUG FIXED: UTF16 / UTF8 File name conversion related
         *
         * $file_name = isset($_REQUEST['file']) ? basename(stripslashes($_REQUEST['file'])) : null;
         *
         * ----> basename is NOT UTF8 compliant
         */
        $file_name = null;
        if ( isset( $_REQUEST[ 'file' ] ) ) {
            $raw_file  = explode( DIRECTORY_SEPARATOR, $_REQUEST[ 'file' ] );
            $file_name = array_pop( $raw_file );
        }

        $file_info = FilesStorage::pathinfo_fix( $file_name );

        //if it's a zip file, delete it and all its contained files.
        if ( $file_info[ 'extension' ] == 'zip' ) {
            $success = $this->zipFileDelete( $file_name );
        } //if it's a file in a zipped folder, delete it.
        elseif ( preg_match( '#^[^\.]*\.zip/#', $_REQUEST[ 'file' ] ) ) {
            $file_name = ZipArchiveExtended::getInternalFileName( $_REQUEST[ 'file' ] );

            $success = $this->zipInternalFileDelete( $file_name );
        } else {
            $success = $this->normalFileDelete( $file_name );
        }

        header( 'Content-type: application/json' );
        echo json_encode( $success );

    }

    private function normalFileDelete( $file_name ) {

        $file_path = $this->options[ 'upload_dir' ] . $file_name;

        $this->deleteSha( $file_path );

        $success[ $file_name ] = is_file( $file_path ) && $file_name[ 0 ] !== '.' && unlink( $file_path );

        return $success;
    }

    private function zipFileDelete( $file_name ) {
        $file_path = $this->options[ 'upload_dir' ] . $file_name;

        $out_file_name = ZipArchiveExtended::getFileName( $file_name );

        $success[ $out_file_name ] = is_file( $file_path ) && $file_name[ 0 ] !== '.' && unlink( $file_path );
        if ( $success[ $out_file_name ] ) {

            $containedFiles = glob( $this->options[ 'upload_dir' ] . $file_name . "*" );
            $k              = 0;

            while ( $k < count( $containedFiles ) ) {
                $internalFileName = str_replace( $this->options[ 'upload_dir' ], "", $containedFiles[ $k ] );
                $success          = array_merge( $success, $this->zipInternalFileDelete( $internalFileName ) );
                $k++;
            }

        }

        return $success;
    }

    private function zipInternalFileDelete( $file_name ) {
        $file_path = $this->options[ 'upload_dir' ] . $file_name;
        $this->deleteSha( $file_path );

        $out_file_name = ZipArchiveExtended::getFileName( $file_name );

        $success[ $out_file_name ] = is_file( $file_path ) && $file_name[ 0 ] !== '.' && unlink( $file_path );
        return $success;
    }

    /**
     * Avoid race conditions by javascript multiple calls
     *
     * @param $file_path
     */
    private function deleteSha( $file_path ) {

        $sha1 = sha1_file( $file_path );
        if ( !$sha1 ) {
            return;
        }

        //can be present more than one file with the same sha
        //so in the sha1 file there could be more than one row
        $file_sha = glob( $this->options[ 'upload_dir' ] . $sha1 . "*" ); //delete sha1 also

        $fp = fopen( $file_sha[ 0 ], "r+" );

        // no file found
        if ( !$fp ) {
            return;
        }

        $i = 0;
        while ( !flock( $fp, LOCK_EX | LOCK_NB ) ) {  // acquire an exclusive lock
            $i++;
            if ( $i == 40 ) {
                return;
            } //exit the loop after 2 seconds, can not acquire the lock
            usleep( 50000 );
            continue;
        }

        $file_content       = fread( $fp, filesize( $file_sha[ 0 ] ) );
        $file_content_array = explode( "\n", $file_content );

        //remove the last line ( is an empty string )
        array_pop( $file_content_array );

        $fileName = FilesStorage::basename_fix( $file_path );

        $key = array_search( $fileName, $file_content_array );
        unset( $file_content_array[ $key ] );

        if ( !empty( $file_content_array ) ) {
            fseek( $fp, 0 ); //rewind
            ftruncate( $fp, 0 ); //truncate to zero bytes length
            fwrite( $fp, implode( "\n", $file_content_array ) . "\n" );
            fflush( $fp );
            flock( $fp, LOCK_UN );    // release the lock
            fclose( $fp );
        } else {
            flock( $fp, LOCK_UN );    // release the lock
            fclose( $fp );
            @unlink( @$file_sha[ 0 ] );
        }

    }

    protected function _isRightMime( $fileUp ) {

        //Mime White List, take them from ProjectManager.php
        foreach ( INIT::$MIME_TYPES as $key => $value ) {
            if ( strpos( $key, $fileUp->type ) !== false ) {
                return true;
            }
        }

        return false;

    }

    protected function _isRightExtension( $fileUp ) {

        $acceptedExtensions = [];
        foreach ( INIT::$SUPPORTED_FILE_TYPES as $key2 => $value2 ) {
            $acceptedExtensions = array_unique( array_merge( $acceptedExtensions, array_keys( $value2 ) ) );
        }

        $fileNameChunks = explode( ".", $fileUp->name );

        //first Check the extension
        if ( array_search( strtolower( $fileNameChunks[ count( $fileNameChunks ) - 1 ] ), $acceptedExtensions ) !== false ) {
            return true;
        }

        return false;
    }

    protected function _isValidFileName( $fileUp ) {

        if (
                strpos( $this->options[ 'upload_dir' ] . $fileUp->name, '..' ) !== false ||
                strpos( $this->options[ 'upload_dir' ] . $fileUp->name, '%2E%2E' ) !== false ||
                strpos( $fileUp->name, '.' ) === 0 ||
                strpos( $fileUp->name, '%2E' ) === 0
        ) {
            //Directory Traversal!
            return false;
        }

        return true;

    }

}
