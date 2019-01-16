<?php
/**
 * Created by PhpStorm.
 * User: fregini
 * Date: 1/24/16
 * Time: 10:21 AM
 */

namespace Features\ReviewImproved;

use Features\TranslationVersions\SegmentTranslationModel as VersionModel;
use LQA\ChunkReviewStruct;
use LQA\ChunkReviewDao;

class SegmentTranslationModel {
    /**
     * @var \SegmentTranslationModel
     */
    protected $model;

    /**
     * @var \Chunks_ChunkStruct
     */
    protected $chunk;

    /**
     * @var ChunkReviewStruct
     */
    protected $chunk_review;

    /**
     * @var bool
     */
    protected $did_change_reviewed_words_count = false;

    public function __construct( \SegmentTranslationModel $model ) {

        $this->model = $model;
        $this->chunk = \Chunks_ChunkDao::getBySegmentTranslation( $this->model->getTranslation() );

        $reviews = ChunkReviewDao::findChunkReviewsByChunkIds( [
                [
                        $this->chunk->id, $this->chunk->password
                ]
        ] );

        return $this->chunk_review = $reviews[ 0 ];

    }

    public function recountPenaltyPoints() {
        $penaltyPoints                      = ChunkReviewDao::getPenaltyPointsForChunk( $this->chunk );
        $this->chunk_review->penalty_points = $penaltyPoints;

        $chunk_review_model = new ChunkReviewModel( $this->chunk_review );
        $chunk_review_model->updatePassFailResult();
    }

    /**
     * addOrSubtractCachedReviewedWordsCount
     */

    public function addOrSubtractCachedReviewedWordsCount() {

        $version_model = new VersionModel( $this->model );

        /**
         * If this model triggers a new version, then we can jump to
         * the check for reviewed state transition directly, because translation
         * issues are bound to a specific version. So when a new version is created
         * it's useless to check for previous translation issues.
         *
         * When a new version is not triggered instead we must check translation
         * issues exist instead. If they do, then the reviewed word count was already
         * added to the cached sum.
         *
         */

        $this->checkReviewedStateTransition();

        // if ( $version_model->triggersNewVersion() ) {
        //     $this->checkReviewedStateTransition();
        // } else {
        //     $this->checkTranslationIssuesExist();
        // }

    }

    /**
     * @return bool
     */
    public function didChangeReviewedWordsCount() {
        return $this->did_change_reviewed_words_count;
    }


    protected function checkReviewedStateTransition() {
        if ( $this->model->entersReviewedState() ) {
            $this->addCount();
        } elseif ( $this->model->exitsReviewedState() ) {
            $this->subtractCount();
        }

    }

    protected function checkTranslationIssuesExist() {
        $translation = $this->model->getTranslation();

        $entries = \LQA\EntryDao::findAllByTranslationVersion(
                $translation->id_segment,
                $translation->id_job,
                $translation->version_number
        );

        if ( count( $entries ) == 0 ) {
            $this->checkReviewedStateTransition();
        }
    }

    /**
     * @return \LQA\ChunkReviewStruct
     */
    public function getChunkReview() {
        return $this->chunk_review;

    }

    protected function addCount() {
        $segment = $this->model->getSegmentStruct();
        $model   = new ChunkReviewModel( $this->chunk_review );
        $model->addWordsCount( $segment->raw_word_count );

    }

    protected function subtractCount() {
        $segment = $this->model->getSegmentStruct();
        $model   = new ChunkReviewModel( $this->chunk_review );
        $model->subtractWordsCount( $segment->raw_word_count );
    }

}