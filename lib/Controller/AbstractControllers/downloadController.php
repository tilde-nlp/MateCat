<?php
/**
 * Created by PhpStorm.
 * User: domenico
 * Date: 27/01/14
 * Time: 18.57
 * 
 */

abstract class downloadController extends controller {

    public $id_job ;
    public $password;
    protected $download_type;
    protected $id_file;
    protected $id_project;

    protected $outputContent = "";
    protected $_filename     = "unknown";

    protected $_user_provided_filename ;

    /**
     * @var Jobs_JobStruct
     */
    protected $job;

    public function __construct() {
        $filterArgs = array(
                'jwt'        => array( 'filter' => FILTER_UNSAFE_RAW ),
                'projectId'        => array( 'filter' => FILTER_SANITIZE_NUMBER_INT ),
                'projectPassword'      => array(
                        'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
                )
        );

        $this->__postInput = filter_input_array( INPUT_POST, $filterArgs );
        $crendentials = AuthCookie::getCredentials();
        try {
            $project = Projects_ProjectDao::findByIdAndPassword(
                $this->__postInput[ 'projectId' ],
                $this->__postInput[ 'projectPassword' ]
            );
        } catch(Exceptions\NotFoundException $e) {
            header("HTTP/1.1 404 Not Found");
            die();
        }        

        $this->jwt = $this->__postInput[ 'jwt' ];
        $this->id_job        = $this->getJobIdFromProjectId($this->__postInput[ 'projectId' ]);
        $this->download_type = 'all'; 
        $this->password      = $this->getJobPasswordFromProjectId($this->__postInput[ 'projectId' ]);
    }

    /**
     * @param int $ttl
     *
     * @return Jobs_JobStruct
     */
    public function getJob( $ttl = 0 ) {
        if( empty( $this->job ) ){
            $this->job = Jobs_JobDao::getById( $this->id_job, $ttl )[0];
        }
        return $this->job;
    }

    protected function getJobIdFromProjectId($projectId) {
        $projectData = getProjectJobData($projectId);
        $projectData = array_pop($projectData);

        return $projectData['jid'];
    }

    protected function getJobPasswordFromProjectId($projectId) {
        $projectData = getProjectJobData($projectId);
        $projectData = array_pop($projectData);
        
        return $projectData['jpassword'];
    }

    protected function log($data, $name = 'debug') {
        $oldFile = \Log::$fileName;
        \Log::$fileName = $name . '.log';
        \Log::doLog($data);
        \Log::$fileName = $oldFile;
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setOutputContent( $content ) {
        $this->outputContent = $content;

        return $this;
    }

    /**
     * @param string $filename
     *
     * @return $this
     */
    public function setFilename( $filename ) {
        $this->_filename = $filename;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilename() {
        return $this->_filename;
    }

    /**
     * @var Projects_ProjectStruct
     */
    protected $project;

    /**
     * @return Projects_ProjectStruct
     */
    public function getProject() {
        return $this->project;
    }

    protected function unlockToken( $tokenContent = null ) {

        if ( isset( $this->downloadToken ) && !empty( $this->downloadToken ) ) {
            setcookie(
                    $this->downloadToken,
                    ( empty( $tokenContent ) ? json_encode( array(
                            "code"    => 0,
                            "message" => "Download complete."
                    ) ) : json_encode( $tokenContent ) ),
                    2147483647            // expires January 1, 2038
            );
            $this->downloadToken = null;
        }

    }

    public function finalize() {
        try {
            $this->unlockToken();

            if ( empty( $this->project) ) {
                $this->project = \Projects_ProjectDao::findByJobId($this->id_job);
            }

            if ( empty($this->_filename ) ) {
                $this->_filename = $this->getDefaultFileName( $this->project );
            }

            $isGDriveProject = \Projects_ProjectDao::isGDriveProject($this->project->id);

            $forceXliff = intval( filter_input( INPUT_GET, 'forceXliff' ) );

            if( !$isGDriveProject || $forceXliff === 1 ) {
                $buffer = ob_get_contents();
                ob_get_clean();
                ob_start("ob_gzhandler");  // compress page before sending
                $this->nocache();
                header("Content-Type: application/force-download");
                header("Content-Type: application/octet-stream");
                header("Content-Type: application/download");
                header("Content-Disposition: attachment; filename=\"$this->_filename\""); // enclose file name in double quotes in order to avoid duplicate header error. Reference https://github.com/prior/prawnto/pull/16
                header("Expires: 0");
                header("Connection: close");
                echo $this->outputContent;
                exit;
            }
        } catch (Exception $e) {
            echo "<pre>";
            print_r($e);
            echo "\n\n\n";
            echo "</pre>";
            exit;
        }
    }

    /**
     * If more than one file constitutes the project, then the filename is the project name.
     * If the project is made of just one file, then the filename for download is the file name itself.
     *
     * @param $project Projects_ProjectStruct
     *
     * @return string
     */
    public function getDefaultFileName( Projects_ProjectStruct $project ) {
            $files = Files_FileDao::getByProjectId( $project->id );

            if ( count(  $files ) > 1 ) {
                return $this->project->name . ".zip" ;
            } else {
                return $files[0]->filename ;
            }
        }

    /**
     * @param ZipContentObject[] $output_content
     * @param string $outputFile
     *
     * @return string The zip binary
     */
    protected static function composeZip( Array $output_content, $outputFile=null , $isOriginalFile=false) {
        if(empty($outputFile)){
            $outputFile = tempnam("/tmp", "zipmatecat");
        }

        $zip  = new ZipArchive();
        $zip->open( $outputFile, ZipArchive::OVERWRITE );

        $rev_index_name = array();

        foreach ( $output_content as $f ) {

            //Php Zip bug, utf-8 not supported
            $fName = preg_replace( '/[^0-9a-zA-Z_\.\-=\$\:@§]/u', "_", $f->output_filename );
            $fName = preg_replace( '/[_]{2,}/', "_", $fName );
            $fName = str_replace( '_.', ".", $fName );

            if($isOriginalFile!=true) {
                $fName = self::sanitizeFileExtension( $fName );
            }

            $nFinfo = FilesStorage::pathinfo_fix( $fName );
            $_name  = $nFinfo[ 'filename' ];
            if ( strlen( $_name ) < 3 ) {
                $fName = substr( uniqid(), -5 ) . "_" . $fName;
            }

            if ( array_key_exists( $fName, $rev_index_name ) ) {
                $fName = uniqid() . $fName;
            }

            $rev_index_name[ $fName ] = $fName;

            $content = $f->getContent();
            if( !empty( $content ) ){
                $zip->addFromString( $fName, $content);
            }
        }

        // Close and send to users
        $zip->close();
        $zip_content = file_get_contents( $outputFile );
        unlink( $outputFile );

        return $zip_content;
    }

    public static function sanitizeFileExtension( $filename ) {

        $pathinfo = FilesStorage::pathinfo_fix( $filename );

        switch (strtolower( $pathinfo[ 'extension' ] )) {
            case 'pdf':
            case 'bmp':
            case 'png':
            case 'gif':
            case 'jpg':
            case 'jpeg':
            case 'tiff':
            case 'tif':
                $filename = $pathinfo[ 'basename' ] . ".docx";
                break;
        }

        return $filename;

    }

}
