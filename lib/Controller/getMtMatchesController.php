<?php

class getMtMatchesController extends ajaxController
{
    private $mt_id;
    private $text;

    private $User;
    private $postInput;

    public function __construct()
    {
        parent::__construct();
        $this->User = AuthCookie::getCredentials();
        $this->filterInput();
    }

    private function filterInput() {
        $filterArgs = [
            'mt_id' => [ 'filter' => FILTER_SANITIZE_STRING ],
            'text' => [ 'filter' => FILTER_UNSAFE_RAW ],
        ];

        $this->postInput = filter_input_array( INPUT_POST, $filterArgs );

        $this->text = html_entity_decode(trim( $this->postInput[ 'text' ] ));
        $this->mt_id = $this->postInput[ 'mt_id' ];

    }

    function doAction()
    {
        $parsedText = PlaceholderParser::toXliff($this->text);
        $matches = \LetsMTLite::getMatch($this->mt_id, $parsedText, AuthCookie::getToken());
        if (empty($matches[0])) {
            $this->result[ 'data' ] = [];
            return;
        }
        $match = $matches[0];

        $match[ 'match' ] = 'MT';
        $QA = new PostProcess( $match[ 'raw_segment' ], $match[ 'raw_translation' ] );
        $QA->realignMTSpaces();
        $match[ 'raw_translation' ] = $QA->getTrgNormalized();
        $match[ 'translation' ] = CatUtils::rawxliff2view( $match[ 'raw_translation' ] );
        $match[ 'translation' ] = PlaceholderParser::toPlaceholders($match[ 'translation' ]);
        $match = $this->_matchRewrite( $match );

        $this->result[ 'data' ][ 'match' ] = $match;
    }

    protected function _matchRewrite( $match ){
        $match = $this->featureSet->filter( 'matchRewriteForContribution', $match );
        return $match;
    }
}

