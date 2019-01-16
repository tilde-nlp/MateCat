let SegmentActions = require("../../../../actions/SegmentActions");
let CatToolActions = require("../../../../actions/CatToolActions");
let SearchUtils = {

    searchEnabled: true,
    searchParams: {
        search: 0
    },
    searchResultsSegments: false,
    /**
     * ???
     * @param segment
     */
	applySearch: function(segment) {
		if (UI.body.hasClass('searchActive'))
			this.markSearchResults({
				singleSegment: segment,
				where: 'no'
			});
	},
    /**
     * Called by the search component to execute search
     * @returns {boolean}
     */
    execFind: function(params) {
		UI.removeGlossaryMarksFormAllSources();

		this.searchResultsSegments = false;
		$('section.currSearchSegment').removeClass('currSearchSegment');

		let searchSource = params.searchSource;
		if (searchSource !== '' && searchSource !== ' ') {
			this.searchParams.source = searchSource;
		} else {
			delete this.searchParams.source;
		}
		let searchTarget = params.searchTarget;
		if (searchTarget !== '' && searchTarget !== ' ')  {
			this.searchParams.target = searchTarget;
		} else {
			delete this.searchParams.target;
		}

		let selectStatus = params.selectStatus;
		if (selectStatus !== '') {
			this.searchParams.status = selectStatus ;
			this.searchParams.status = this.searchParams.status.toLowerCase();

			UI.body.attr('data-filter-status', this.searchParams.status);
		} else {
			delete this.searchParams.status;
		}

		let replaceTarget = params.replaceTarget;
		if (replaceTarget !== '') {
			this.searchParams.replace = replaceTarget;
		} else {
			delete this.searchParams.replace;
		}
		this.searchParams['match-case'] = params.matchCase;
		this.searchParams['exact-match'] = params.exactMatch;
		this.searchParams.search = 1;
		if (_.isUndefined(this.searchParams.source) && _.isUndefined(this.searchParams.target) && (this.searchParams.status == 'all')) {
			APP.alert({msg: 'Enter text in source or target input boxes<br /> or select a status.'});
			return false;
		}
		UI.disableTagMark();

		let p = this.searchParams;

		this.searchMode = (_.isUndefined(p.source) && _.isUndefined(p.target)) ? 'onlyStatus' :
			(!_.isUndefined(p.source) && !_.isUndefined(p.target)) ? 'source&target' : 'normal';
		this.whereToFind = "";
		if ( this.searchMode === 'normal') {
		    if (!_.isUndefined(p.target)) {
                this.whereToFind = ".targetarea";
            } else if (!_.isUndefined(p.source)) {
                this.whereToFind = ".source";
            }
        }
		let source = (p.source) ? htmlEncode(p.source) : '';
		let target = (p.target) ? htmlEncode(p.target) : '';
		let replace = (p.replace) ? p.replace : '';

		this.clearSearchMarkers();
		this.pendingRender = {
			applySearch: true,
			detectSegmentToScroll: true
		};
		UI.body.addClass('searchActive');
		let makeSearchFn = () => {
            let dd = new Date();
            APP.doRequest({
                data: {
                    action: 'getSearch',
                    function: 'find',
                    job: config.id_job,
                    token: dd.getTime(),
                    password: config.password,
                    source: source,
                    target: target,
                    status: this.searchParams.status,
                    matchcase: this.searchParams['match-case'],
                    exactmatch: this.searchParams['exact-match'],
                    replace: replace
                },
                success: function(d) {
                    SearchUtils.execFind_success(d);
                }
            });
        };
		//Save the current segment to not lose the translation
        var segment;
        try {
            segment = UI.Segment.findAbsolute( UI.currentSegmentId );
            if ( UI.translationIsToSave( segment ) ) {
                UI.saveSegment(UI.currentSegment).then(() => {
                    makeSearchFn()
                });
            } else  {
                makeSearchFn();
            }
        } catch (e) {
            makeSearchFn();
        }


	},
    /**
     * Call in response to request getSearch
     * @param d
     */
    execFind_success: function(d) {
		this.numSearchResultsItem = d.total;
		this.searchResultsSegments = d.segments;
		this.numSearchResultsSegments = (d.segments) ? d.segments.length : 0;
        CatToolActions.setSearchResults(d);
		this.updateSearchDisplay();
		if (this.pendingRender) {
			if (this.pendingRender.detectSegmentToScroll) {
				this.pendingRender.segmentToScroll = this.nextUnloadedResultSegment();
			}

			UI.unmountSegments();

			UI.render(this.pendingRender);
			this.pendingRender = false;
		}
	},
    /**
     * Displays the message under the search container that contains the number of results and segments found
     */
    updateSearchDisplay: function() {
        this.updateSearchItemsCount();
        // if (UI.someSegmentToSave()) {
        //     this.addWarningToSearchDisplay();
        // } else {
        //     this.removeWarningFromSearchDisplay();
        // }
    },
    /**
     * Update the results counter in the search container
     */
    updateSearchDisplayCount: function(segment) {
        let numRes = $('.search-display .numbers .results'),
            currRes = parseInt(numRes.text()),
            newRes = (currRes == 0)? 0 : currRes - 1;
        numRes.text(" " + newRes);
        if (($('.targetarea mark.searchMarker', segment).length - 1) <= 0) {
            let numSeg = $('.search-display .numbers .segments'),
                currSeg = parseInt(numSeg.text()),
                newSeg = (currSeg == 0)? 0 : currSeg - 1;
            numSeg.text(" " + newSeg);
        }
        this.updateSearchItemsCount();
    },
    /**
     * Toggle the Search container
     * @param e
     */
	toggleSearch: function(e) {
		if (!this.searchEnabled) return;
        if (UI.body.hasClass('searchActive')) {
            CatToolActions.closeSearch();
        } else {
            e.preventDefault();
            CatToolActions.toggleSearch();
            // this.fixHeaderHeightChange();
        }
	},
    /**
     * Executes the replace all for segments if all the params are ok
     * @returns {boolean}
     */
    execReplaceAll: function(params) {
        // $('.search-display .numbers').text('No segments found');
        this.applySearch();

        let searchSource = params.searchSource;
        if (searchSource !== '' && searchSource !== ' ' && searchSource !== '\'' && searchSource !== '"' ) {
            this.searchParams.source = searchSource;
        } else {
            delete this.searchParams.source;
        }

        let searchTarget = params.searchTarget;
        if (searchTarget !== '' && searchTarget !== ' '  && searchTarget !== '"')  {
            this.searchParams.target = searchTarget;
        } else {
            APP.alert({msg: 'You must specify the Target value to replace.'});
            delete this.searchParams.target;
            return false;
        }

        let replaceTarget =  params.replaceTarget;
        if ( replaceTarget !== '"')  {
            this.searchParams.replace = replaceTarget;
        } else {
            APP.alert({msg: 'You must specify the replacement value.'});
            delete this.searchParams.replace;
            return false;
        }

        if (params.selectStatus !== '' && params.selectStatus !== 'all') {
            this.searchParams.status = params.selectStatus;
            UI.body.attr('data-filter-status', params.selectStatus);
        } else {
            delete this.searchParams.status;
        }

        this.searchParams['match-case'] = params.matchCase;
        this.searchParams['exact-match'] = params.exactMatch;

        let p = this.searchParams;
        let source = (p.source) ? htmlEncode(p.source) : '';
        let target = (p.target) ? htmlEncode(p.target) : '';
        let replace = (p.replace) ? p.replace : '';
        let dd = new Date();

        APP.doRequest({
            data: {
                action: 'getSearch',
                function: 'replaceAll',
                job: config.id_job,
                token: dd.getTime(),
                password: config.password,
                source: source,
                target: target,
                status: p.status,
                matchcase: p['match-case'],
                exactmatch: p['exact-match'],
                replace: replace
            },
            success: function(d) {
                if(d.errors.length) {
                    APP.alert({msg: d.errors[0].message});
                    return false;
                }
                UI.unmountSegments();
                UI.render({
                    firstLoad: false
                });
            }
        });
    },
    /**
     * Remove all the markers
     */
    clearSearchMarkers: function() {
        $('mark.searchMarker').each(function() {
            $(this).replaceWith($(this).html());
        });
        $('section.currSearchSegment').removeClass('currSearchSegment');
    },
    /**
     * Mark your search results according to the type of search is done
     * @param options
     */
    markSearchResults: function(options) { // if where is specified mark only the range of segment before or after seg (no previous clear)
        options = options || {};
        let where = options.where;
        let seg = options.seg;
        let singleSegment = options.singleSegment || false;
        let status, what, q, hasTags;
        if (typeof where == 'undefined') {
            this.clearSearchMarkers();
        }
        let p = this.searchParams;

        let containsFunc = (p['match-case']) ? 'contains' : 'containsNC';
        let ignoreCase = (p['match-case']) ? '' : 'i';

        if (this.searchMode == 'onlyStatus') { // search mode: onlyStatus
            seg = options.segmentToScroll;
            if ( seg ) {
                SegmentActions.addClassToSegment(seg, 'currSearchSegment');
            }
        } else if (this.searchMode == 'source&target') { // search mode: source&target
            status = (p.status == 'all') ? '' : '.status-' + p.status;
            q = (singleSegment) ? '#' + $(singleSegment).attr('id') : "section" + status + ':not(.status-new)';
            let psource = p.source.replace(/(\W)/gi, "\\$1");
            let ptarget = p.target.replace(/(\W)/gi, "\\$1");

            let regSource = new RegExp('(' + htmlEncode(psource).replace(/\(/g, '\\(').replace(/\)/g, '\\)') + ')', "g" + ignoreCase);
            let regTarget = new RegExp('(' + htmlEncode(ptarget).replace(/\(/g, '\\(').replace(/\)/g, '\\)') + ')', "g" + ignoreCase);
            let txtSrc = p.source;
            let txtTrg = p.target;
            let srcHasTags = (txtSrc.match(/<.*?\>/gi) !== null) ? true : false;
            let trgHasTags = (txtTrg.match(/<.*?\>/gi) !== null) ? true : false;

            if (typeof where == 'undefined') {
                let elemsSource = this.getSegmentsResult('.source');
                let elemsTarget = this.getSegmentsResult('.targetarea');
                this.doMarkSearchResults(srcHasTags, elemsSource, regSource, q, txtSrc, ignoreCase);
                this.doMarkSearchResults(trgHasTags, elemsTarget, regTarget, q, txtTrg, ignoreCase);

                $('section').has('.source mark.searchPreMarker').has('.targetarea mark.searchPreMarker').find('mark.searchPreMarker').addClass('searchMarker').removeClass('searchPreMarker');

                $('mark.searchPreMarker:not(.searchMarker)').each(function() {
                    let a = $(this).html();
                    $(this).replaceWith(a);
                });
            } else {
                if ( seg.length > 0 ) {
                    var sid = parseInt(UI.getSegmentId(seg));
                    if (where == 'before') {
                        $('section').each(function() {
                            if (parseInt(UI.getSegmentId($(this))) < sid) {
                                $(this).addClass('justAdded');
                            }
                        });
                    } else {
                        $('section').each(function() {
                            if (parseInt(UI.getSegmentId($(this))) > sid) {
                                $(this).addClass('justAdded');
                            }
                        });
                    }
                }
                let elemsSource = this.getSegmentsResult('.source');
                let elemsTarget = this.getSegmentsResult('.targetarea');
                this.execSearchResultsMarking(this.filterExactMatch(elemsSource, txtSrc), regSource, false);
                this.execSearchResultsMarking(this.filterExactMatch(elemsTarget, txtTrg), regTarget, false);

                $('section').has('.source mark.searchPreMarker').has('.targetarea mark.searchPreMarker').find('mark.searchPreMarker').addClass('searchMarker');
                $('mark.searchPreMarker').removeClass('searchPreMarker');
                $('section.justAdded').removeClass('justAdded');
            }
        } else { // search mode: normal
            status = (p.status == 'all') ? '' : '.status-' + p.status;
            let txt = (typeof p.source != 'undefined') ? p.source : (typeof p.target != 'undefined') ? p.target : '';
            if (singleSegment) {
                what = (typeof p.source != 'undefined') ? ' .source' : (typeof p.target != 'undefined') ? ' .targetarea' : '';
                q = '#' + $(singleSegment).attr('id') + what;
            } else {
                what = (typeof p.source != 'undefined') ? ' .source' : (typeof p.target != 'undefined') ? ':not(.status-new) .targetarea' : '';
                q = "section" + status + what;
            }
            let matchTags = txt.match(/<.*?\>/gi) ;
            hasTags = ( matchTags ) ? true : false;
            let regTxt = txt.replace(/(\W)/gi, "\\$1");
            regTxt = regTxt.replace(/\(/gi, "\\(").replace(/\)/gi, "\\)");

            let reg = new RegExp('(' + htmlEncode(regTxt).replace(/\(/g, '\\(').replace(/\)/g, '\\)') + ')', "g" + ignoreCase);
            let reg1 = new RegExp('(' + htmlEncode(regTxt).replace(/\(/g, '\\(').replace(/\)/g, '\\)').replace(/\\\\\(/gi, "\(").replace(/\\\\\)/gi, "\)") + ')', "g" + ignoreCase );


            if (p['exact-match']) {
                reg = new RegExp('\\b(' + htmlEncode(regTxt).replace(/\(/g, '\\(').replace(/\)/g, '\\)') + ')\\b', "g" + ignoreCase);
                reg1 = new RegExp('\\b(' + htmlEncode(regTxt).replace(/\(/g, '\\(').replace(/\)/g, '\\)').replace(/\\\\\(/gi, "\(").replace(/\\\\\)/gi, "\)") + ')\\b', "g" + ignoreCase );
            }


            // Finding double spaces
            if (txt === "  ") {
                reg1 = new RegExp(/(&nbsp; )/, 'gi');
                reg = new RegExp(/(&nbsp; )/, 'gi');
            }

            if ((typeof where == 'undefined') || (where == 'no')) {
                let elems = this.getSegmentsResult(this.whereToFind);
                this.doMarkSearchResults(hasTags, elems, reg1, q, txt, ignoreCase);
            } else {
                if ( seg.length > 0 ) {
                    var sid = parseInt(UI.getSegmentId(seg));
                    if (where == 'before') {
                        $('section').each(function() {
                            if (parseInt(UI.getSegmentId($(this))) < sid) {
                                $(this).addClass('justAdded');
                            }
                        });
                    } else {
                        $('section').each(function() {
                            if (parseInt(UI.getSegmentId($(this))) > sid) {
                                $(this).addClass('justAdded');
                            }
                        });
                    }
                }
                let elems = this.getSegmentsResult(this.whereToFind);
                this.doMarkSearchResults(hasTags, elems, reg, q, txt, ignoreCase );
                $('section.justAdded').removeClass('justAdded');
            }
        }
    },
    /**
     * Check if a segment has tags and call the fn to mark the results
     * @param hasTags
     * @param items
     * @param regex
     * @param q
     * @param txt
     * @param ignoreCase
     */
    doMarkSearchResults: function(hasTags, items, regex, q, txt, ignoreCase) {
        if (!hasTags) {
            this.execSearchResultsMarking(this.filterExactMatch(items, txt), regex, false);
        } else {
            let inputReg = new RegExp(txt, "g" + ignoreCase);
            this.execSearchResultsMarking(items, regex, inputReg);
        }
    },
    /**
     * Displays the warning message that appears when there is an unsaved segment.
     */
    // addWarningToSearchDisplay: function() {
    //     if (!$('.search-display .found .warning').length)
    //         $('.search-display .found').append('<span class="warning"></span>');
    //     $('.search-display .found .warning').text(' (maybe some results in segments modified but not saved)');
    // },
    /**
     * Removes the warning message that appears when there is an unsaved segment
     */
    removeWarningFromSearchDisplay: function() {
        $('.search-display .found .warning').remove();
    },
    /**
     * Update the results counter in the header container
     */
    updateSearchItemsCount: function() {
        let c = parseInt($('.search-display .numbers .results').text());
        if (c > 0) {
            $('#filterSwitch .numbererror').text(c).attr('title', $('.search-display .found').text());
        } else {
            $('#filterSwitch .numbererror').text('');
        }
    },

    /**
     * Go to next result
     */
    execNext: function() {
        this.gotoNextResultItem(false);
    },
    execPrev: function() {
        this.gotoNextResultItem(false, "prev");
    },
        /**
     * Put the parker in the search results
     * @param areas
     * @param regex
     * @param testRegex
     */
	execSearchResultsMarking: function(areas, regex, testRegex) {
        let searchMarker = (this.searchMode === 'source&target')? 'searchPreMarker' : 'searchMarker';
		$(areas).each(function() {
		    let segId = UI.getSegmentId( $(this) );
            if ( SearchUtils.searchResultsSegments.indexOf(segId) > -1 ) {
                let isCurrent = $(this).find('.currSearchItem').length > 0;
                let isTagged = $(this).find('.searchMarker').length > 0;
                if ( (!testRegex || ($(this).text().match(testRegex) !== null) ) && !isCurrent && !isTagged) {
                    let tt = $(this).html();
                    if (LXQ.cleanUpHighLighting) {
                        tt = LXQ.cleanUpHighLighting(tt);
                    }
                    let spanArray = [];
                    tt = tt.replace(/(<[/]*span.*?>)/g, function ( match, text ) {
                        spanArray.push(text);
                        return "$&";
                    });
                    tt = tt.replace(regex, '<mark class="' + searchMarker + '">$1</mark>');
                    tt = tt.replace(/(\$&)/g, function ( match, text ) {
                        return spanArray.shift();
                    });
                    $(this).html(tt);
                    //Temp fix for tags with equiv text
                    $(this).find('.tag-html-container-open').each(function (  ) {
                        $(this).text($(this).text());
                    });
                }
            }
		});
	},
    /**
     * Filter items that has an exact match
     * @param items List of jQuery items
     * @param txt The exact match to look for
     * @returns the list of items
     */
    filterExactMatch: function(items, txt) {
        let searchTxt = txt.replace(/[-\/\\^$*+?.()|[\]{}\']/g, '\\$&');
        let searchTxtUppercase = txt.toUpperCase().replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        let self = this;
        return (this.searchParams['exact-match']) ? items.filter(function(item) {
            if (self.searchParams['match-case']) {
                return $(item).text().match(new RegExp("\\b"+searchTxt+"\\b", "i")) != null;
            } else {
                return $(item).text().toUpperCase().match(new RegExp("\\b"+searchTxtUppercase+"\\b", "i")) != null;
            }
        }) : items;
    },
        /**
     * If the text (in the editarea target) inside a marker is modified remove the marker
     * and update the counter
     * @param el the segment modified
     */
	rebuildSearchSegmentMarkers: function(el) {
		let querySearchString = this.searchParams.target,
			segment = $(el),
			markers = $(el).find('mark.searchMarker'),
			self = this;

		//check if there are markers inside the segment
		if(markers){
			//cycle markers
			$(markers).each(function() {
				//if the content of marker is different from querySearch
				//remove tag mark and reposition the cursors

				if($(this).text() != querySearchString){
					pasteHtmlAtCaret('<span class="currentCursorPositionForReplace"></span>'); //set a tag where there is the cursors
					$(this).replaceWith($(this).html()); //remove tag mark
					setCursorPosition($('.currentCursorPositionForReplace')[0]); //reposition of the cursors
					$('.currentCursorPositionForReplace').remove(); //remove tag

					self.updateSearchDisplayCount(segment);
				}
			});
		}
	},
    /**
     * Go to next match inside the segment,
     * @param unmark
     * @param type next or prev
     */
    gotoNextResultItem: function(unmark, type) {
        let p = this.searchParams;

        let jQueryFnForNext = "nextAll";
        if (type === "prev") {
            jQueryFnForNext = "prevAll";// $.extend(UI, {


        }
        if (this.searchMode == 'onlyStatus') {

            let status = (p.status == 'all') ? '' : '.status-' + p.status;
            let el = $('section.currSearchSegment');

            if (p.status == 'all') {
                // TODO: this case should never heppen since onlyStatus and
                // all combination is denied by UI. Consider removing this block.
                UI.scrollSegment( $(el).next() );
            } else {
                if (el[jQueryFnForNext](status).length) {
                    nextToGo = el[jQueryFnForNext](status).first();
                    SegmentActions.removeClassToSegment(UI.getSegmentId(el), 'currSearchSegment');
                    SegmentActions.addClassToSegment(UI.getSegmentId(nextToGo), 'currSearchSegment');
                    UI.scrollSegment(nextToGo);
                } else {
                    // We fit this case if the next batch of segments is to load
                    // from the server.
                    this.gotoSearchResultAfter({
                        el: el.attr('id'),
                    }, type);
                }

            }
        } else {
            let current = "mark.currSearchItem";
            let currentSegmentFind = $(current).closest("div");
            let marksArray = currentSegmentFind.find("mark").toArray();
            let currentMarkIndex = marksArray.indexOf($(current)[0]);
            let nextIndex = (type === "prev") ? currentMarkIndex - 1 : currentMarkIndex + 1;
            if ( marksArray.length > 1 && !_.isUndefined( marksArray[nextIndex] ) ) {
                $(current).removeClass('currSearchItem');
                $(marksArray[nextIndex]).addClass('currSearchItem');
                if (unmark)
                    $(current).replaceWith($(current).text());
                UI.goingToNext = false;
            } else { // jump to results in subsequents segments

                let seg = (current.length) ? $(current).parents('section') : $('mark.searchMarker').first().parents('section');
                if (seg.length) {
                    this.gotoSearchResultAfter({
                        el: $(seg).attr('id').split('-')[1],
                        unmark: unmark
                    }, type);
                } else {
                    setTimeout(function() {
                        SearchUtils.gotoNextResultItem(false, type);
                    }, 500);
                }
            }

        }
    },
    /**
     * Go To next Segment
     * @param options
     */
    gotoSearchResultAfter: function(options, type) {

        let el = options.el;
        let $el = $('#segment-' + el);
        let unmark = (options.unmark || false);

        let p = this.searchParams;
        let jQueryFnForNext = "nextAll";
        if (type === "prev") {
            jQueryFnForNext = "prevAll";
        }

        if (this.searchMode === 'onlyStatus' && p.status !== 'all') { // searchMode: onlyStatus
            let status = '.status-' + p.status;

            if ($el[jQueryFnForNext](status).length) { // there is at least one next result loaded after the currently selected
                let nextToGo = $el[jQueryFnForNext](status).first();
                SegmentActions.addClassToSegment(UI.getSegmentId(nextToGo), 'currSearchSegment');
                UI.scrollSegment(nextToGo);
            } else {
                // load new segments
                if (!this.searchResultsSegments) {
                    this.pendingRender = {
                        applySearch: true,
                        detectSegmentToScroll: true
                    };
                } else {
                    let seg2scroll = this.nextUnloadedResultSegment();
                    UI.unmountSegments();
                    UI.render({
                        firstLoad: false,
                        applySearch: true,
                        segmentToScroll: seg2scroll
                    });
                }
            }

        } else { // searchMode: source&target or normal
            let wh = (this.searchMode === 'source&target')? ' .targetarea' : '';
            let segmentsWithMarkers = $('section' + wh).has("mark.searchMarker");
            let $currentSegment = $el;
            let found = false;
            let self = this;
            segmentsWithMarkers = (type === "prev" ) ? segmentsWithMarkers.toArray().reverse() : segmentsWithMarkers ;
            $.each(segmentsWithMarkers, function() {
                var indexCurrent = self.searchResultsSegments.indexOf( '' + UI.getSegmentId($currentSegment) );
                var index = self.searchResultsSegments.indexOf( '' + UI.getSegmentId($(this)) );
                if ( (type === "prev" &&  ( UI.getSegmentId($(this)) < UI.getSegmentId($currentSegment) || (indexCurrent === 0 &&  index === self.searchResultsSegments.length - 1)) ) ||
                     (type !== "prev" && (UI.getSegmentId($(this)) > UI.getSegmentId($currentSegment) || (indexCurrent === self.searchResultsSegments.length - 1 &&  index === 0)) )
                    ){
                    found = true;
                    UI.scrollSegment($(this));

                    setTimeout(function() {
                        UI.goingToNext = false;
                    }, 500);

                    let mark = $("mark.currSearchItem");
                    $(mark).removeClass('currSearchItem');
                    if (type === "prev") {
                        $(this).find('mark.searchMarker').last().addClass('currSearchItem');
                    } else {
                        $(this).find('mark.searchMarker').first().addClass('currSearchItem');
                    }
                    if (unmark) {
                        $(mark).replaceWith($(mark).text());
                    }
                    return false;
                }
            });
            if (!found) {
                // load new segments
                if (!this.searchResultsSegments) {
                    this.pendingRender = {
                        applySearch: true,
                        detectSegmentToScroll: true
                    };
                } else {
                    let seg2scroll = this.nextUnloadedResultSegment();
                    UI.unmountSegments();
                    UI.render({
                        firstLoad: false,
                        applySearch: true,
                        segmentToScroll: seg2scroll
                    });
                }
            }
        }
    },
    getSegmentsResult: function ( className ) {
        let $objects = [];
        className = (className) ? className : '.source'
        _.each(this.searchResultsSegments, function ( item ) {
            let $obj = UI.getSegmentById(item);
            if ($obj.length) {
                $obj = $obj.find(className);
                $objects.push($obj);
            }
        });
        return $objects;
    },
    nextUnloadedResultSegment: function() {
        let found = '';
        let last = UI.getSegmentId($('section').last());
        $.each(this.searchResultsSegments, function() {
            if ((!$('#segment-' + this).length) && (parseInt(this) > parseInt(last))) {
                found = parseInt(this);
                return false;
            }
        });
        if (found === '') {
            found = this.searchResultsSegments[0];
        }
        return found;
    },
    /**
     * Close search container
     */
    closeSearch : function() {
        CatToolActions.closeSubHeader();
    },
};

module.exports = SearchUtils;

