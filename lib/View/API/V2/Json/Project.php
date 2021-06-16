<?php
/**
 * Created by PhpStorm.
 * @author domenico domenico@translated.net / ostico@gmail.com
 * Date: 20/01/17
 * Time: 16.41
 *
 */

namespace API\V2\Json;

use Chunks_ChunkStruct;
use Constants_JobStatus;
use DataAccess\ShapelessConcreteStruct;
use Projects_ProjectStruct;
use Utils;

class Project {

    /**
     * @var Job
     */
    protected $jRenderer;

    /**
     * @var Projects_ProjectStruct[]
     */
    protected $data = [];

    /**
     * @var bool
     */
    protected $called_from_api = false;

    /**
     * @var \Users_UserStruct
     */
    protected $user;

    /**
     * @param \Users_UserStruct $user
     *
     * @return $this
     */
    public function setUser( $user ) {
        $this->user = $user;
        return $this;
    }

    /**
     * @param bool $called_from_api
     *
     * @return $this
     */
    public function setCalledFromApi( $called_from_api ) {
        $this->called_from_api = (bool)$called_from_api;

        return $this;
    }

    /**
     * Project constructor.
     *
     * @param Projects_ProjectStruct[] $data
     */
    public function __construct( array $data = [] ) {
        $this->data = $data;
        $this->jRenderer = new Job();
    }

    /**
     * @param       $project Projects_ProjectStruct
     *
     * @return array
     * @throws \Exception
     * @throws \Exceptions\NotFoundException
     */
    public function renderItem( Projects_ProjectStruct $project ) {

        $featureSet = $project->getFeatures();
        $jobs = $project->getJobs((int)$project->id);

        $projectOutputFields = [
            'id'                   => (int)$project->id,
            'password'             => $project->password,
            'name'                 => $project->name,
        ];

        if ( !empty( $jobs ) ) {
            $jobJSON = new $this->jRenderer();
            $job = $jobJSON->renderItem( new Chunks_ChunkStruct( $jobs[0]->getArrayCopy() ), $project, $featureSet );
            $projectOutputFields['createTimestamp'] = intval($job['create_timestamp']);
            $projectOutputFields['jobId'] = intval($job['id']);
            $projectOutputFields['jobPassword'] = $job['password'];
            $projectOutputFields['source'] = $job['source'];
            $projectOutputFields['target'] = $job['target'];
            $projectOutputFields['owner'] = $job['owner'];
        }

        return $projectOutputFields;

    }

    /**
     * @return array
     * @throws \Exception
     * @throws \Exceptions\NotFoundException
     */
    public function render() {
        $out = [];
        foreach ( $this->data as $membership ) {
            $out[] = $this->renderItem( $membership );
        }

        return $out;
    }

}