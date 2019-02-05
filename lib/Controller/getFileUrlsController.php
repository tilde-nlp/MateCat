<?php

use API\V2\Json\ProjectUrls;

class getFileUrlsController extends ajaxController {

    private $project;

    private $id_project;
    private $password;

    public function __construct() {
        parent::__construct();
        
        $filterArgs = array(
                'projectId' => array(
                        'filter' => FILTER_SANITIZE_NUMBER_INT
                ),
                'projectPassword'   => array(
                        'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
                ),
        );

        $postInput = filter_input_array( INPUT_POST, $filterArgs );

        $this->id_project = $postInput['projectId'];
        $this->password   = $postInput['projectPassword'];

        $this->project = Projects_ProjectDao::findByIdAndPassword(
                $this->id_project,
                $this->password
        );

        if ( !$this->project ) {
            throw new NotFoundException();
        }
    }

    public function doAction() {
        $this->featureSet->loadForProject( $this->project );

        /**
         * @var $projectData ShapelessConcreteStruct[]
         */
        $projectData = ( new \Projects_ProjectDao() )->setCacheTTL(0 )->getProjectData( $this->project->id );

        $formatted = new ProjectUrls( $projectData );

        $formatted = $this->featureSet->filter( 'projectUrls', $formatted );
        
        $urls = $formatted->render();
        $this->result = [];
        $this->result['translation'] = $urls['files'][0]['translation_download_url'];
        $this->result['original'] = $urls['files'][0]['original_download_url'];
    }
}