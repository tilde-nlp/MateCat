<?php

use Contribution\Request;

class getContributionController extends ajaxController {

    private $projectId;
    private $password;
    private $text;
    private $sourceLang;
    private $targetLang;
    private $count;
    private $project;
    private $appId;

    private $__postInput = array();

    public function __construct() {

        parent::__construct();

        $filterArgs = [
                'projectId'     => [ 'filter' => FILTER_SANITIZE_NUMBER_INT ],
                'count'    => [ 'filter' => FILTER_SANITIZE_NUMBER_INT ],
                'text'           => [ 'filter' => FILTER_UNSAFE_RAW ],
                'projectPassword'       => [ 'filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW ],
                'sourceLang'       => [ 'filter' => FILTER_SANITIZE_STRING ],
                'targetLang'       => [ 'filter' => FILTER_SANITIZE_STRING ],
                'appId'       => [ 'filter' => FILTER_SANITIZE_STRING ],

        ];

        $this->__postInput = filter_input_array( INPUT_POST, $filterArgs );

        $this->projectId         = $this->__postInput[ 'projectId' ];
        $this->password         = $this->__postInput[ 'projectPassword' ];
        $this->count         = $this->__postInput[ 'count' ];
        $this->sourceLang         = $this->__postInput[ 'sourceLang' ];
        $this->targetLang         = $this->__postInput[ 'targetLang' ];
        $this->appId         = $this->__postInput[ 'appId' ];
        $this->text               = html_entity_decode(trim( $this->__postInput[ 'text' ] ));

        $this->project = Projects_ProjectDao::findByIdAndPassword(
                $this->projectId,
                $this->password
        );
    }

    public function doAction() {

        if ( is_null( $this->text ) || $this->text === '' ) {
            $this->result[ 'errors' ][] = [ "code" => -2, "message" => "missing text" ];
        }

        if ( empty( $this->count ) ) {
            $this->count = 5;
        }

        if ( !empty( $this->result[ 'errors' ] ) ) {
            return -1;
        }

        $this->featureSet->loadForProject( $this->project );

        $this->text = CatUtils::view2rawxliff( $this->text );
        $parsedText = PlaceholderParser::toXliffFromSymbols($this->text);
        $tmp_match = TildeTM::getContributions($this->projectId, $parsedText, $this->sourceLang, $this->targetLang, $this->appId);
        $tms_match = [];

        foreach($tmp_match as $match) {
            $match['segment'] = PlaceholderParser::toPlaceholders($match['segment']);
            $match['translation'] = PlaceholderParser::toPlaceholders($match['translation']);
            $match['createdBy'] = $match['created_by'];
            unset($match['created_by']);
            unset($match['raw_segment']);
            unset($match['raw_translation']);
            $tms_match[] = $match;
        }
        $mt_match = [];

        $matches = array_merge($tms_match, $mt_match);
        usort( $matches, array( "getContributionController", "__compareScore" ) );
        $matches = array_reverse( $matches );
        $matches = array_slice( $matches, 0, $this->count );

        $this->result = [];
        $this->result = $matches;
    }

    private static function __compareScore( $a, $b ) {
        if ( floatval( $a[ 'match' ] ) == floatval( $b[ 'match' ] ) ) {
            return 0;
        }
        return ( floatval( $a[ 'match' ] ) < floatval( $b[ 'match' ] ) ? -1 : 1 );
    }
}

