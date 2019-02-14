<?php

namespace API\V2;

use API\V2\Json\Project;
use API\V2\Json\ProjectAnonymous;
use API\V2\Validators\ProjectPasswordValidator;

/**
 * This controller can be called as Anonymous, but only if you already know the id and the password
 *
 * Class ProjectsController
 * @package API\V2
 */
class ProjectsController extends KleinController {

    /**
     * @var \Projects_ProjectStruct
     */
    private $project;

    /**
     * @var ProjectPasswordValidator
     */
    private $projectValidator;

    public function get() {

        if ( empty( $this->user ) ) {
            $formatted = new ProjectAnonymous();
        } else {
            $formatted = new Project();
            $formatted->setUser( $this->user );
            if ( !empty( $this->api_key ) ) {
                $formatted->setCalledFromApi( true );
            }
        }

        $this->featureSet->loadForProject( $this->project );
        $projectOutputFields = $formatted->renderItem( $this->project );
        $projectOutputFields = $this->featureSet->filter( 'filter_manage_single_project', $projectOutputFields );

        $this->response->json( [ 'project' => $projectOutputFields ] );

    }

    public function setDueDate() {
        $this->updateDueDate();
    }

    public function updateDueDate() {
        if (
                array_key_exists( "due_date", $this->params )
                &&
                is_numeric( $this->params[ 'due_date' ] )
                &&
                $this->params[ 'due_date' ] > time()
        ) {

            $due_date    = \Utils::mysqlTimestamp( $this->params[ 'due_date' ] );
            $project_dao = new \Projects_ProjectDao;
            $project_dao->updateField( $this->project, "due_date", $due_date );
        }
        if ( empty( $this->user ) ) {
            $formatted = new ProjectAnonymous();
        } else {
            $formatted = new Project();
        }

        //$this->response->json( $this->project->toArray() );
        $this->response->json( [ 'project' => $formatted->renderItem( $this->project ) ] );
    }

    public function deleteDueDate() {
        $project_dao = new \Projects_ProjectDao;
        $project_dao->updateField( $this->project, "due_date", null );

        if ( empty( $this->user ) ) {
            $formatted = new ProjectAnonymous();
        } else {
            $formatted = new Project();
        }
        $this->response->json( [ 'project' => $formatted->renderItem( $this->project ) ] );
    }

    public function cancel() {
        return $this->changeStatus(\Constants_JobStatus::STATUS_CANCELLED );
    }

    public function archive() {
        return $this->changeStatus(\Constants_JobStatus::STATUS_ARCHIVED );
    }

    public function active() {
        return $this->changeStatus(\Constants_JobStatus::STATUS_ACTIVE );
    }

    private function changeStatus($status){

        updateJobsStatus( "prj", $this->project->id, $status );
        $this->response->json( [ 'code' => 1, 'data' => "OK", 'status' => $status ] );

    }

    protected function afterConstruct() {

        $projectValidator = ( new ProjectPasswordValidator( $this ) );

        $projectValidator->onSuccess( function () use ( $projectValidator ) {
            $this->project = $projectValidator->getProject();
        } );

        $this->appendValidator( $projectValidator );
    }

}