QaCheckGlossary = {};

QaCheckGlossary.enabled = function() {
    return config.qa_check_glossary_enabled ;
};

if ( QaCheckGlossary.enabled() )
(function($, QaCheckGlossary, undefined) {
    var matchRegExp = '\\b(%s)\\b' ;
    // var cjkRegExp = '(%s)';
    var regExpFlags = 'g';

    var globalWarnings ;

    /**
     * We wait for getWarning local event to notify for Glossary warnings,
     * so to provide a consistent feeling go the user.
     */
    $(document).on('getWarning:local:success', function( e, data ) {
        startLocalUnusedGlossaryHighlight( data.segment );
    });

    $(document).on('getWarning:global:success', function(e, data) {
        updateGlobalWarnings(data.resp.data.glossary) ;
    });

    /**
     * Ensure update is reissued after glossarySourceMarked.
     */
    $(document).on('glossarySourceMarked', function(e, data) {
        startLocalUnusedGlossaryHighlight( data.segment );
    });

    function updateGlobalWarnings(glossary) {

        var mapped = {} ;

        // group by segment id
        _.each( glossary.matches, function ( item ) {
            mapped[ item.id_segment ] ? null : mapped[ item.id_segment ] = []  ;
            mapped[ item.id_segment ].push( item.data );
        });

        globalWarnings = mapped;
    }

    function removeUnusedGlossaryMarks( container ) {
        container.find('.unusedGlossaryTerm').each(function(index)  {
            $(this).replaceWith( this.childNodes );
        });
    }

    function startLocalUnusedGlossaryHighlight( segment ) {
        if (globalWarnings) {
            updateGlossaryUnusedMatches( segment, globalWarnings[segment.id] ) ;
        }
    }
    /*
    * Can be called externaly (by LexiQA) to reload powerip
    */
    function redoBindEvents(container) {
        $('.unusedGlossaryTerm', container).powerTip({
            placement : 's'
        });
        $('.unusedGlossaryTerm', container).data({ 'powertipjq' : $('<div class="unusedGlossaryTip" style="padding: 4px;">Unused glossary term</div>') });
    }
    /*
    * Can be called externaly (by LexiQA) to destroy powtip and prevent
    * memory leak when HTML is replaced
    */
    function destroyPowertip(container) {
        $.powerTip.destroy($('.blacklistItem', container));
    }

    function bindEvents( container, unusedMatches ) {

        container.find('.unusedGlossaryTerm').each(function(index, item) {
            var el = $(item);

            var entry = _.chain(unusedMatches).filter(function findMatch(match, index) {
                return match.id == el.data('id');
            }).first().value();
            el.powerTip({ placement : 's' });
            el.data({ 'powertipjq' : $('<div class="unusedGlossaryTip" style="padding: 4px;">Unused glossary term</div>') });
        });
    }

    function updateGlossaryUnusedMatches( segment, unusedMatches ) {
        // read the segment source, find with a regexp and replace with a span
        var container = segment.el.find('.source').clone();

        removeUnusedGlossaryMarks( container ) ;
        if (_.isUndefined(unusedMatches) || unusedMatches.length === 0) {
            return;
        }
        container.find('.inside-attribute').remove();
        var newHTML = htmlEncode(container.text());

        //Replace ph tags
        var base64Tags=[];
        newHTML = newHTML.replace( /&lt;ph.*?equiv-text="base64:(.*?)".*?\/&gt;/gi, function (match, text) {
            base64Tags.push(match);
            return "###" + text +"###";
        });
        unusedMatches = unusedMatches.sort(function(a, b){
            return b.raw_segment.length - a.raw_segment.length;
        });
        $.each(unusedMatches, function( index ) {
            var value = (this.raw_segment) ? this.raw_segment : this.translation ;
            value = escapeRegExp( value );
            value = value.replace(/ /g, '(?: *<\/*(?:mark)*(?:span *)*(?: (data-id="(.*?)" )*class="(unusedGlossaryTerm)*(inGlossary)*")*> *)* *');
            var re = new RegExp( sprintf( matchRegExp, value ), QaCheckGlossary.qaCheckRegExpFlags);

            var check = re.test( '<span class="unusedGlossaryTerm">$1</span>' );
            if ( !check ){
                newHTML = newHTML.replace(
                    re , '<span class="unusedGlossaryTerm">$1</span>'
                );
            } else  {
                re = new RegExp( sprintf( "\\s\\b(%s)\\s\\b", value ), QaCheckGlossary.qaCheckRegExpFlags);
                newHTML = newHTML.replace(
                    re , ' <span class="unusedGlossaryTerm">$1</span> '
                );
            }
        });
        newHTML = newHTML.replace( /###(.*?)###/gi, function (match, text) {
            var tag = base64Tags.shift();
            return tag;
        });
        if ( newHTML.indexOf('unusedGlossaryTerm') > -1 ) {
            (function (cont, html, matches) {
                setTimeout(function (  ) {
                    SegmentActions.replaceSourceText(UI.getSegmentId(cont), UI.getSegmentFileId(cont), UI.transformTextForLockTags(html));
                    bindEvents( cont, matches );
                }, 200);
            })(segment.el, newHTML, unusedMatches);
        }


    }

    // function findUnusedGlossaryMatches( record ) {
    //     if ( ! ( record.glossary_matches && record.glossary_matches.length ) )  return [] ;
    //
    //     var segment = UI.Segment.find( record.sid ) ;
    //     var currentText = segment.el.find( UI.targetContainerSelector() ).text();
    //
    //     return _.filter( record.glossary_matches, function( item ) {
    //         var translation = (item.raw_translation) ? item.raw_translation : item.translation;
    //         var value = escapeRegExp( translation );
    //         var re = new RegExp( sprintf( matchRegExp, value ), QaCheckGlossary.qaCheckRegExpFlags);
    //
    //         if ( config.targetIsCJK ) {
    //             re = new RegExp( sprintf( cjkRegExp, value ), QaCheckGlossary.qaCheckRegExpFlags);
    //         }
    //
    //         var match = currentText.match( re ) ;
    //         return match == null ;
    //     });
    // }

    $.extend(QaCheckGlossary, {
        removeUnusedGlossaryMarks : removeUnusedGlossaryMarks,
        destroyPowertip: destroyPowertip,
        redoBindEvents: redoBindEvents,
        qaCheckRegExpFlags: regExpFlags
    });

})(jQuery, QaCheckGlossary);
