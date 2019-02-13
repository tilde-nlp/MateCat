<?php

class getConcordanceContributionController extends ajaxController {

    private $text;
    private $source;
    private $target;
    private $projectId;

    private $__postInput = array();

    public function __construct() {

        parent::__construct();

        $filterArgs = [
                'sourceLang'         => [ 'filter' => FILTER_SANITIZE_STRING ],
                'text'           => [ 'filter' => FILTER_UNSAFE_RAW ],
                'targetLang'       => [ 'filter' => FILTER_SANITIZE_STRING],
                'projectId' => array( 'filter' => FILTER_SANITIZE_NUMBER_INT ),
        ];

        $this->__postInput = filter_input_array( INPUT_POST, $filterArgs );

        $this->text               = html_entity_decode(trim( $this->__postInput[ 'text' ] ));
        $this->source           = $this->__postInput[ 'sourceLang' ];
        $this->target           = $this->__postInput[ 'targetLang' ];
        $this->projectId           = $this->__postInput[ 'projectId' ];
    }

    public function doAction() {
        $matches = TildeTM::getConcordanceContributions($this->projectId, $this->text, $this->source, $this->target);
        usort( $matches, array( "getContributionController", "__compareScore" ) );
        $matches = array_reverse( $matches );

        foreach ( $matches as &$match ) {
            $match[ 'segment' ] = strip_tags( html_entity_decode( $match[ 'segment' ] ) );
            $match[ 'segment' ] = preg_replace( '#[\x{20}]{2,}#u', chr( 0x20 ), $match[ 'segment' ] );

            $match[ 'segment' ]     = preg_replace( array_keys( $regularExpressions ), array_values( $regularExpressions ), $match[ 'segment' ] );
            $match[ 'translation' ] = strip_tags( html_entity_decode( $match[ 'translation' ] ) );
        }

        $this->result = $matches;
    }
}

