if (!API) {
    var API = {}
}


API.SEGMENT = {

    setTranslation: function (segment) {
        var contextBefore = UI.getContextBefore(segment.sid);
        var idBefore = UI.getIdBefore(segment.sid);
        var contextAfter = UI.getContextAfter(segment.sid);
        var idAfter = UI.getIdAfter(segment.sid);
        var trans = UI.prepareTextToSend(segment.translation);
        var time_to_edit = new Date() - UI.editStart;
        // var id_translator = config.id_translator;

        var data = {
            id_segment: segment.sid,
            id_job: config.id_job,
            password: config.password,
            status: segment.status,
            translation: trans,
            segment : segment.segment,
            propagate: false,
            context_before: contextBefore,
            id_before: idBefore,
            context_after: contextAfter,
            id_after: idAfter,
            time_to_edit: time_to_edit,
            // id_translator: id_translator,
        };
        return $.ajax({
            data: data,
            type: "POST",
            url : "/?action=setTranslation"
        });
    },

    getSegmentsIssues: function ( idSegment ) {
        var path  = sprintf('/api/v2/jobs/%s/%s/translation-issues',
            config.id_job, config.password, idSegment);
        return $.ajax({
            type: "get",
            url : path
        });
    },

    getSegmentVersionsIssues: function (idSegment) {

        var path  = sprintf("/api/v2/jobs/%s/%s/revise/segments/%s/translation-versions",
            config.id_job, config.password, idSegment);
        return $.ajax({
            type: "get",
            url : path
        });
    },

    sendSegmentVersionIssue: function (idSegment, data) {

        var path = sprintf('/api/v2/jobs/%s/%s/segments/%s/translation-issues',
            config.id_job, config.password, idSegment);
        return $.ajax({
            data: data,
            type: "POST",
            url : path
        });
    },

    sendSegmentVersionIssueComment: function (idSegment, idIssue, data) {

        var replies_path = sprintf(
            '/api/v2/jobs/%s/%s/segments/%s/translation-issues/%s/comments',
            config.id_job, config.password,
            idSegment,
            idIssue
        );

        return $.ajax({
            url: replies_path,
            type: 'POST',
            data : data
        })
    },

    getGlossaryForSegment: function ( source) {
        var data = {
            exec: 'get',
            segment: source,
            automatic: true,
            translation: null,
            id_job: config.id_job,
            password: config.password
        };
        return $.ajax({
            data: data,
            type: "POST",
            url : "/?action=glossary"
        });
    },

    getGlossaryMatch: function ( source ) {
        var data = {
            action: 'glossary',
            exec: 'get',
            segment: source,
            automatic: false,
            translation: null,
            id_job: config.id_job,
            password: config.password
        };
        return $.ajax({
            data: data,
            type: "POST",
            url : "/?action=glossary"
        });
    },

    deleteGlossaryItem: function ( source, target ) {
        var data = {
            exec: 'delete',
            segment: source,
            translation: target,
            id_job: config.id_job,
            password: config.password
        };
        return $.ajax({
            data: data,
            type: "POST",
            url : "/?action=glossary"
        });
    },

    addGlossaryItem: function ( source, target, comment ) {
        var data = {
            exec: 'set',
            segment: source,
            translation: target,
            comment: comment,
            id_job: config.id_job,
            password: config.password
        };
        return $.ajax({
            data: data,
            type: "POST",
            url : "/?action=glossary"
        });
    },

    updateGlossaryItem: function (idItem, source, target, newTranslation, comment) {
        var data = {
            exec: 'update',
            segment: source,
            translation: target,
            newsegment: source,
            newtranslation: newTranslation,
            id_item: idItem,
            comment: comment,
            id_job: config.id_job,
            password: config.password
        };

        return $.ajax({
            data: data,
            type: "POST",
            url : "/?action=glossary"
        });
    },
    approveSegments: function ( segments ) {
        var data = {
            segments_id: segments,
            status: 'approved'
        };
        return $.ajax({
            async: true,
            data: data,
            type: "post",
            url : "/api/v2/jobs/" + config.id_job + "/"+ config.password + "/segments/status"
        });
    },
    translateSegments: function ( segments ) {
        var data = {
            segments_id: segments,
            status: 'translated'
        };
        return $.ajax({
            async: true,
            data: data,
            type: "post",
            url : "/api/v2/jobs/" + config.id_job + "/"+ config.password + "/segments/status"
        });
    },

    getConcordance: function (query, type) {
        var data = {
            action: 'getContribution',
            is_concordance: 1,
            from_target: type,
            id_segment: UI.currentSegmentId,
            text: view2rawxliff(query),
            id_job: config.job_id,
            num_results: UI.numMatchesResults,
            id_translator: config.id_translator,
            password: config.password,
            id_client: config.id_client
        };
        return $.ajax({
            async: true,
            data: data,
            type: "post",
            url : "/?action=getContribution"
        });
    },

    /**
     * Return a list of contribution from a id_segment
     * @param id_segment
     * @param target
     * @return Contributions - Promise
     */
    getContributions: function (id_segment, target) {
        var contextBefore = UI.getContextBefore(id_segment);
        var idBefore = UI.getIdBefore(id_segment);
        var contextAfter = UI.getContextAfter(id_segment);
        var idAfter = UI.getIdAfter(id_segment);
        // check if this function is ok for al cases
        let txt = UI.prepareTextToSend(target);
        let data = {
            action: 'getContribution',
            password: config.password,
            is_concordance: 0,
            id_segment: id_segment,
            text: txt,
            id_job: config.id_job,
            num_results: UI.numContributionMatchesResults,
            id_translator: config.id_translator,
            context_before: contextBefore,
            id_before: idBefore,
            context_after: contextAfter,
            id_after: idAfter,
            id_client: config.id_client
        };

        return $.ajax({
            async: true,
            data: data,
            type: "post",
            url: "/?action=getContribution"
        });
    }

};