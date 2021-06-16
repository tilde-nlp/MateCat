<?php

class getFileStatsController extends ajaxController {

    private $chunk;

    private $job_id;
    private $password;
    private $project_id;

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
        $this->project_id = $postInput['projectId'];

        $project = Projects_ProjectDao::findByIdAndPassword(
                $this->project_id,
                $postInput['projectPassword']
        );

        if ( !$project ) {
            throw new NotFoundException();
        }

        $projectData = getProjectJobData( $this->project_id );
        $projectData = array_pop($projectData);

        $this->job_id = $projectData['jid'];
        $this->password   = $projectData['jpassword'];

        $this->chunk = Chunks_ChunkDao::getByIdAndPassword(
                $this->job_id,
                $this->password
        );
    }

    public function doAction() {
        $wStruct = new \WordCount_Struct();

        $wStruct->setIdJob( $this->chunk->id );
        $wStruct->setJobPassword( $this->chunk->password );
        $wStruct->setNewWords( $this->chunk->new_words );
        $wStruct->setDraftWords( $this->chunk->draft_words );
        $wStruct->setTranslatedWords( $this->chunk->translated_words );
        $wStruct->setApprovedWords( $this->chunk->approved_words );
        $wStruct->setRejectedWords( $this->chunk->rejected_words );

        $job_stats = \CatUtils::getFastStatsForJob( $wStruct );
        $ChunkDao = new Chunks_ChunkDao();
        $translatedWords = $ChunkDao->getTranslatedWordCount($this->project_id);
        $totalWords = $this->chunk->total_raw_wc;
        $job_stats['TRANSLATED_PERC'] = $translatedWords / $totalWords * 100;

        $job_stats['ANALYSIS_COMPLETE'] = $this->chunk->getProject()->analysisComplete() ;


        $response = array( 'stats' => $job_stats );

        $this->featureSet = new \FeatureSet();
        $this->featureSet->loadForProject( $this->chunk->getProject( 0 ) )  ;
        $response = $this->featureSet->filter('filterStatsControllerResponse', $response, [ 'chunk' => $this->chunk ] );
        
        $this->result = [];
        $this->result['analysisComplete'] = $response['stats']['ANALYSIS_COMPLETE'];
        $this->result['translatedPercent'] = $response['stats']['TRANSLATED_PERC'];
        $this->result['projectId'] = intval($this->project_id);
    }
}