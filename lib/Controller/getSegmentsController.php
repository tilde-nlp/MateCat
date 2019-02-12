<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

use Segments\ContextGroupDao;

class getSegmentsController extends ajaxController {

    private $data = array();
    private $cid = "";
    private $jid = "";
    private $tid = "";
    private $password = "";
    private $source = "";
    private $pname = "";
    private $err = '';
    private $create_date = "";
    private $filetype_handler = null;
    private $start_from = 0;
    private $page = 0;
    private $searchInSource = '';
    private $searchInTarget = '';
    private $searchInComments = '';

    /**
     * @var Chunks_ChunkStruct
     */
    private $job;

    /**
     * @var Projects_ProjectStruct
     */
    private $project ;

    private $segment_notes ;


    public function __construct() {

        parent::__construct();

        $filterArgs = array(
            'projectId'         => array( 'filter' => FILTER_SANITIZE_NUMBER_INT ),
            'step'        => array( 'filter' => FILTER_SANITIZE_NUMBER_INT ),
            'segment' => array( 'filter' => FILTER_SANITIZE_NUMBER_INT ),
            'projectPassword'    => array( 'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH ),
            'where'       => array( 'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH ),
            'searchInSource'       => array( 'filter' => FILTER_UNSAFE_RAW ),
            'searchInTarget'       => array( 'filter' => FILTER_UNSAFE_RAW ),
            'searchInComments'       => array( 'filter' => FILTER_UNSAFE_RAW ),
        );

        $__postInput = filter_input_array( INPUT_POST, $filterArgs );

        $project = Projects_ProjectDao::findByIdAndPassword(
            $__postInput['projectId'],
            $__postInput['projectPassword']
        );

        if ( !$project ) {
            throw new NotFoundException();
        }

        $projectData = getProjectJobData( $__postInput['projectId'] );
        $projectData = array_pop($projectData);

        $this->jid = $projectData['jid'];
        $this->password   = $projectData['jpassword'];
        $this->step        = $__postInput[ 'step' ];
        $this->ref_segment = $__postInput[ 'segment' ];
        $this->where       = $__postInput[ 'where' ];
        $this->searchInSource       = htmlentities($__postInput[ 'searchInSource' ]);
        $this->searchInTarget       = htmlentities($__postInput[ 'searchInTarget' ]);
        $this->searchInComments       = htmlentities($__postInput[ 'searchInComments' ]);

    }

    public function doAction() {

        //get Job Infos
        $job_data = getJobData( $this->jid );

        $pCheck = new AjaxPasswordCheck();
        //check for Password correctness
        if( !$pCheck->grantJobAccessByJobData( $job_data, $this->password ) ){
            $this->result['errors'][] = array("code" => -10, "message" => "wrong password");
            return;
        }

        $this->job        = Chunks_ChunkDao::getByIdAndPassword( $this->jid, $this->password );
        $this->project    = $this->job->getProject();

        $this->featureSet->loadForProject( $this->project ) ;

		$lang_handler = Langs_Languages::getInstance();

		if ($this->ref_segment == '') {
			$this->ref_segment = 0;
        }
        
        $data = $this->searchSegments();

        $this->prepareNotes( $data );
        $data = $this->prepareComments( $data );
        $contexts = $this->getContextGroups( $data );

		foreach ($data as $i => $seg) {

            if ($this->where == 'before') {
                if (((float) $seg['sid']) >= ((float) $this->ref_segment)) {
                    break;
                }
            }

			if (empty($this->pname)) {
				$this->pname = $seg['pname'];
			}

			if (empty($this->last_opened_segment)) {
				$this->last_opened_segment = $seg['last_opened_segment'];
			}

			if (empty($this->cid)) {
				$this->cid = $seg['cid'];
			}

			if (empty($this->pid)) {
				$this->pid = $seg['pid'];
			}

			if (empty($this->tid)) {
				$this->tid = $seg['tid'];
			}

			if (empty($this->create_date)) {
				$this->create_date = $seg['create_date'];
			}

			if (empty($this->source_code)) {
				$this->source_code = $seg['source'];
			}

			if (empty($this->target_code)) {
				$this->target_code = $seg['target'];
			}

            if (empty($this->source)) {
                $s = explode("-", $seg['source']);
                $source = strtoupper($s[0]);
                $this->source = $source;
            }

            if (empty($this->target)) {
                $t = explode("-", $seg['target']);
                $target = strtoupper($t[0]);
                $this->target = $target;
            }

            if (empty($this->err)) {
                $this->err = $seg['serialized_errors_list'];
            }

			$id_file = $seg['id_file'];

			if ( !isset($this->data["$id_file"]) ) {
                $this->data["$id_file"]['jid'] = $seg['jid'];
                $this->data["$id_file"]["filename"] = ZipArchiveExtended::getFileName($seg['filename']);
                $this->data["$id_file"]["mime_type"] = $seg['mime_type'];
                $this->data["$id_file"]['source'] = $lang_handler->getLocalizedName($seg['source']);
                $this->data["$id_file"]['target'] = $lang_handler->getLocalizedName($seg['target']);
                $this->data["$id_file"]['source_code'] = $seg['source'];
                $this->data["$id_file"]['target_code'] = $seg['target'];
                $this->data["$id_file"]['segments'] = array();
            }

            $seg = $this->featureSet->filter('filter_get_segments_segment_data', $seg) ;

            unset($seg['id_file']);
            unset($seg['source']);
            unset($seg['target']);
            unset($seg['source_code']);
            unset($seg['target_code']);
            unset($seg['mime_type']);
            unset($seg['filename']);
            unset($seg['jid']);
            unset($seg['pid']);
            unset($seg['cid']);
            unset($seg['tid']);
            unset($seg['pname']);
            unset($seg['create_date']);
            unset($seg['id_segment_end']);
            unset($seg['id_segment_start']);
            unset($seg['serialized_errors_list']);

            $seg['parsed_time_to_edit'] = CatUtils::parse_time_to_edit($seg['time_to_edit']);

            ( $seg['source_chunk_lengths'] === null ? $seg['source_chunk_lengths'] = '[]' : null );
            ( $seg['target_chunk_lengths'] === null ? $seg['target_chunk_lengths'] = '{"len":[0],"statuses":["DRAFT"]}' : null );
            $seg['source_chunk_lengths'] = json_decode( $seg['source_chunk_lengths'], true );
            $seg['target_chunk_lengths'] = json_decode( $seg['target_chunk_lengths'], true );

            $seg['segment'] = CatUtils::rawxliff2view( CatUtils::reApplySegmentSplit(
                $seg['segment'] , $seg['source_chunk_lengths'] )
            );

            $seg['translation'] = CatUtils::rawxliff2view( CatUtils::reApplySegmentSplit(
                $seg['translation'] , $seg['target_chunk_lengths'][ 'len' ] )
            );

            $this->attachNotes( $seg );
            $this->attachContexts( $seg, $contexts );

            $this->data["$id_file"]['segments'][] = $seg;
        }

        $this->result = [];
        if (!empty($this->data)) {
            $this->result['fileId'] = array_keys($this->data)[0];
            $rawSegments = [];
            $rawSegments = $this->data[$this->result['fileId']]['segments'];
            $cleanSegments = [];
            foreach ($rawSegments as $rawSegment) {
                $cleanSegments[] = [
                    'id' => $rawSegment['sid'],
                    'original' => $rawSegment['segment'],
                    'translation' => $rawSegment['translation'],
                    'status' => $rawSegment['status'],
                    'saveType' => $rawSegment['save_type'],
                    'saveMatch' => $rawSegment['save_match'],
                    'comments' => $rawSegment['comments']
                ];
            }
            $this->result['segments'] = $cleanSegments;
        }
    }

    private function searchSegments() {
        $foundSegments = [];
        $lastSegmentId = $this->ref_segment - 1;
        $where = $this->where;
        while(count($foundSegments) < $this->step) {
            $data = getMoreSegments(
                $this->jid, $this->password, $this->step,
                $lastSegmentId + 1, $where,
                $this->getOptionalQueryFields(),
                $this->searchInSource, $this->searchInTarget,
                $this->searchInComments
            );
            $where = 'after';
            if (empty($data)) {
                break;
            }
            $lastSegmentId = $data[count($data) -1]['sid'];

            $data = $this->stripTagMatches($data);
            if (empty($data)) {
                continue;
            }

            foreach($data as $row) {
                $foundSegments[] = $row;
                if (count($foundSegments) == 2 * $this->step) {
                    break;
                }
            }
        }

        return $foundSegments;
    }

    private function stripTagMatches($data) {
        if ($this->searchInSource == "" && $this->searchInTarget == "") {
            return $data;
        }

        $filteredData = [];
        foreach($data as $row) {
            $sourceIsTagged = $this->isSegmentTagged($row['segment'], $this->searchInSource);
            $targetIsTagged = $this->isSegmentTagged($row['translation'], $this->searchInTarget);

            if ($this->searchInSource != "" && !$sourceIsTagged) {
                $filteredData[] = $row;
                continue;
            }

            if ($this->searchInTarget != "" && !$targetIsTagged) {
                $filteredData[] = $row;
            }
        }

        return $filteredData;
    }

    private function isSegmentTagged($segment, $searchTerm) {
        if ($searchTerm == "") {
            return false;
        }

        $taggedMatches = array_merge(
            $this->findAllMatches('<' . $searchTerm, $segment),
            $this->findAllMatches('<e' . $searchTerm, $segment),
            $this->findAllMatches('<b' . $searchTerm, $segment)
        );
        $allMatches = $this->findAllMatches($searchTerm, $segment);

        return (count($allMatches) == count($taggedMatches));
    }

    private function findAllMatches($needle, $haystack) {
        $positions = [];
        $lastPosition = 0;

        while(($lastPosition = strpos($haystack, $needle, $lastPosition)) !== false) {
            $positions[] = $lastPosition;
            $lastPosition += strlen($needle);
        }

        return $positions;
    }

    private function getOptionalQueryFields() {
        $feature = $this->job->getProject()->isFeatureEnabled('translation_versions');
        $options = array();

        if ( $feature ) {
            $options['optional_fields'] = array('st.version_number');
        }

        $options = $this->featureSet->filter('filter_get_segments_optional_fields', $options);

        return $options;
    }

    private function attachNotes( &$segment ) {
        $segment['notes'] = @$this->segment_notes[ (int) $segment['sid'] ] ;
    }

    private function prepareNotes( $segments ) {
        if ( ! empty( $segments[0] ) ) {
            $start = $segments[0]['sid'];
            $last = end($segments);
            $stop = $last['sid'];
            if( $this->featureSet->filter( 'prepareAllNotes', false ) ){
                $this->segment_notes = Segments_SegmentNoteDao::getAllAggregatedBySegmentIdInInterval($start, $stop);
                foreach ( $this->segment_notes as $k => $noteObj ){
                    $this->segment_notes[ $k ][ 0 ][ 'json' ] = json_decode( $noteObj[ 0 ][ 'json' ], true );
                }
                $this->segment_notes = $this->featureSet->filter( 'processExtractedJsonNotes', $this->segment_notes );
            } else {
                $this->segment_notes = Segments_SegmentNoteDao::getAggregatedBySegmentIdInInterval($start, $stop);
            }

        }

    }

    private function prepareComments( $segments ) {
        if (empty($segments[0])) return $segments;
        $mappedSegments = array();
        foreach($segments as $segment) {
            $mappedSegments[$segment['sid']] = $segment;
            $mappedSegments[$segment['sid']]['comments'] = array();
        }
        $struct = new Comments_CommentStruct() ;

        $struct->id_job = $segments[0]['jid'];
        $struct->first_segment = $segments[0]['sid'];
        $last = end($segments);
        $struct->last_segment  = $last['sid'];

        $commentDao = new Comments_CommentDao( Database::obtain() );
        $comments = $commentDao->getCommentsInJob( $struct );
        if (empty($comments)) {
            return array_values($mappedSegments);
        }

        foreach($comments as $comment) {
            if (empty($mappedSegments[$comment['id_segment']])) {
                continue;
            }
            $mappedSegments[$comment['id_segment']]['comments'][] = $comment;
        }

        return array_values($mappedSegments);
    }

    private function getContextGroups( $segments ){
        if ( ! empty( $segments[0] ) ) {
            $start = $segments[0]['sid'];
            $last = end($segments);
            $stop = $last['sid'];
            return ( new ContextGroupDao() )->getBySIDRange( $start, $stop );
        }
    }

    private function attachContexts( &$segment, $contexts ){
        $segment['context_groups'] = @$contexts[ (int) $segment['sid'] ] ;
    }
}
