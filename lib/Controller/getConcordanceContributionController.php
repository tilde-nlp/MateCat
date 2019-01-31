<?php

use Constants\Ices;

class getConcordanceContributionController extends ajaxController {

    private $text;
    private $source;
    private $target;
    private $projectId;

    private $__postInput = array();

    public function __construct() {

        parent::__construct();

        $filterArgs = [
                'source'         => [ 'filter' => FILTER_SANITIZE_STRING ],
                'text'           => [ 'filter' => FILTER_UNSAFE_RAW ],
                'target'       => [ 'filter' => FILTER_SANITIZE_STRING],
                'project_id' => array( 'filter' => FILTER_SANITIZE_NUMBER_INT ),
        ];

        $this->__postInput = filter_input_array( INPUT_POST, $filterArgs );

        $this->text               = html_entity_decode(trim( $this->__postInput[ 'text' ] ));
        $this->source           = $this->__postInput[ 'source' ];
        $this->target           = $this->__postInput[ 'target' ];
        $this->projectId           = $this->__postInput[ 'project_id' ];
    }

    public function doAction() {
        $matches = TildeTM::getConcordanceContributions($this->projectId, $this->text, $this->source, $this->target);

        usort( $matches, array( "getContributionController", "__compareScore" ) );
        $matches = array_reverse( $matches );

        ( isset( $matches[ 0 ][ 'match' ] ) ? $firstMatchVal = floatval( $matches[ 0 ][ 'match' ] ) : null );
        if ( isset( $firstMatchVal ) && $firstMatchVal >= 90 && $firstMatchVal < 100 ) {

            $srcSearch    = strip_tags( $this->text );
            $segmentFound = strip_tags( $matches[ 0 ][ 'raw_segment' ] );
            $srcSearch    = mb_strtolower( preg_replace( '#[\x{20}]{2,}#u', chr( 0x20 ), $srcSearch ) );
            $segmentFound = mb_strtolower( preg_replace( '#[\x{20}]{2,}#u', chr( 0x20 ), $segmentFound ) );

            $fuzzy = levenshtein( $srcSearch, $segmentFound ) / log10( mb_strlen( $srcSearch . $segmentFound ) + 1 );

            if ( $srcSearch == $segmentFound || ( $fuzzy < 2.5 && $fuzzy >= 0 ) ) {

                $qaRealign = new QA( $this->text, html_entity_decode( $matches[ 0 ][ 'raw_translation' ] ) );
                $qaRealign->tryRealignTagID();

                $log_prepend = "CLIENT REALIGN IDS PROCEDURE | ";
                if ( !$qaRealign->thereAreErrors() ) {
                    $matches[ 0 ][ 'segment' ]     = CatUtils::rawxliff2view( $this->text );
                    $matches[ 0 ][ 'translation' ] = CatUtils::rawxliff2view( $qaRealign->getTrgNormalized() );
                    $matches[ 0 ][ 'match' ]       = ( $fuzzy == 0 ? '100%' : '99%' );
                } else {
                    Log::doLog( $log_prepend . 'Realignment Failed. Skip. Segment: ' . $this->__postInput[ 'id_segment' ] );
                }
            }
        }

        foreach ( $matches as &$match ) {

            if ( strpos( $match[ 'created_by' ], 'MT' ) !== false ) {
                $match[ 'match' ] = 'MT';

                $QA = new PostProcess( $match[ 'raw_segment' ], $match[ 'raw_translation' ] );
                $QA->realignMTSpaces();

                //this should every time be ok because MT preserve tags, but we use the check on the errors
                //for logic correctness
                if ( !$QA->thereAreErrors() ) {
                    $match[ 'raw_translation' ] = $QA->getTrgNormalized();
                    $match[ 'translation' ]     = CatUtils::rawxliff2view( $match[ 'raw_translation' ] );
                } else {
                    Log::doLog( $QA->getErrors() );
                }
            }

            if ( $match[ 'created_by' ] == 'MT!' ) {
                $match[ 'created_by' ] = 'MT'; //MyMemory returns MT!
            } elseif ( $match[ 'created_by' ] == 'NeuralMT' ) {
                $match[ 'created_by' ] = 'MT'; //For now do not show differences
            } else {

                $uid = null;
                $this->readLoginInfo();
                if($this->userIsLogged){
                    $uid = $this->user->uid;
                }
                $match[ 'created_by' ] = Utils::changeMemorySuggestionSource(
                        $match,
                        $this->jobData['tm_keys'],
                        $this->jobData['owner'],
                        $uid
                );
            }

            $match = $this->_matchRewrite( $match );

            if ( !empty( $match[ 'sentence_confidence' ] ) ) {
                $match[ 'sentence_confidence' ] = round( $match[ 'sentence_confidence' ], 0 ) . "%";
            }

            $match[ 'segment' ] = strip_tags( html_entity_decode( $match[ 'segment' ] ) );
            $match[ 'segment' ] = preg_replace( '#[\x{20}]{2,}#u', chr( 0x20 ), $match[ 'segment' ] );

            //Do something with &$match, tokenize strings and send to client
            $match[ 'segment' ]     = preg_replace( array_keys( $regularExpressions ), array_values( $regularExpressions ), $match[ 'segment' ] );
            $match[ 'translation' ] = strip_tags( html_entity_decode( $match[ 'translation' ] ) );
        }

        $this->result[ 'data' ][ 'matches' ] = $matches;
    }

    protected function _matchRewrite( $match ){

        //Rewrite ICE matches as 101%
        if( $match[ 'match' ] == '100%' ){
            list( $lang, ) = explode( '-', $this->jobData[ 'target' ] );
            if( isset( $match[ 'ICE' ] ) && $match[ 'ICE' ] && array_search( $lang, ICES::$iceLockDisabledForTargetLangs ) === false ){
                $match[ 'match' ] = '101%';
            }
            //else do not rewrite the match value
        }

        //Allow the plugins to customize matches
        $match = $this->featureSet->filter( 'matchRewriteForContribution', $match );

        return $match;

    }
}

