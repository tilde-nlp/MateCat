<?php

namespace API\V1;


use API\V2\KleinController;
use API\V2\Validators\ChunkPasswordValidator;
use Chunks_ChunkStruct;

class StatsController extends KleinController {

    /**
     * @var Chunks_ChunkStruct
     */
    protected $chunk ;

    public function setChunk( Chunks_ChunkStruct $chunk ){
        $this->chunk = $chunk;
    }

    protected function log($data, $name = 'debug') {
        $oldFile = \Log::$fileName;
        \Log::$fileName = $name . '.log';
        \Log::doLog($data);
        \Log::$fileName = $oldFile;
    }

    /**
     * @throws \API\V2\Exceptions\AuthenticationError
     * @throws \Exceptions\NotFoundException
     * @throws \Exceptions\ValidationError
     * @throws \TaskRunner\Exceptions\EndQueueException
     * @throws \TaskRunner\Exceptions\ReQueueException
     */
    public function stats() {

        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $wStruct = new \WordCount_Struct();

        $wStruct->setIdJob( $this->chunk->id );
        $wStruct->setJobPassword( $this->chunk->password );
        $wStruct->setNewWords( $this->chunk->new_words );
        $wStruct->setDraftWords( $this->chunk->draft_words );
        $wStruct->setTranslatedWords( $this->chunk->translated_words );
        $wStruct->setApprovedWords( $this->chunk->approved_words );
        $wStruct->setRejectedWords( $this->chunk->rejected_words );

        $job_stats = \CatUtils::getFastStatsForJob( $wStruct );
        $this->log($this->chunk);
        $translatedWords = $this->chunk->translated_words < 0 ? 0 : $this->chunk->translated_words;
        $totalWords = $this->chunk->total_raw_wc;
        $job_stats['TRANSLATED_PERC'] = $translatedWords / $totalWords * 100;

        $job_stats['ANALYSIS_COMPLETE'] = $this->chunk->getProject()->analysisComplete() ;


        $response = array( 'stats' => $job_stats );

        $this->featureSet = new \FeatureSet();
        $this->featureSet->loadForProject( $this->chunk->getProject( 60 * 60 ) )  ;
        $response = $this->featureSet->filter('filterStatsControllerResponse', $response, [ 'chunk' => $this->chunk ] );

        $this->response->json( $response ) ;
    }

    protected function afterConstruct() {

        $Validator = ( new ChunkPasswordValidator( $this ) );
        $Controller = $this;
        $Validator->onSuccess( function () use ( $Validator, $Controller ) {
            $Controller->setChunk( $Validator->getChunk() );
        } );

        $this->appendValidator( $Validator );

    }

}