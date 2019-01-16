import AppDispatcher from '../dispatcher/AppDispatcher';
import QRApi from "../ajax_utils/quality_report/qrUtils";
import QRConstants from "./../constants/QualityReportConstants"
let QualityReportActions =  {

    loadInitialAjaxData() {
        QRApi.getSegmentsFiles().done(function ( response ) {
            if ( response.files ) {
                AppDispatcher.dispatch({
                    actionType: QRConstants.RENDER_SEGMENTS,
                    files: response.files,
                });
            }
        });
        QRApi.getQRinfo().done(function(response) {
            if (response.job) {
                AppDispatcher.dispatch({
                    actionType: QRConstants.RENDER_REPORT,
                    job: response.job.chunks[0],
                });
            }
        });
    },

    getMoreQRSegments(filter, segmentId) {
        QRApi.getSegmentsFiles(filter, segmentId).done(function ( response ) {
            if ( response.files && _.size(response.files) > 0) {
                AppDispatcher.dispatch({
                    actionType: QRConstants.ADD_SEGMENTS,
                    files: response.files,
                });
            } else {
                AppDispatcher.dispatch({
                    actionType: QRConstants.NO_MORE_SEGMENTS,
                });
            }
        });
    },

    filterSegments(filter, segmentId) {
        QRApi.getSegmentsFiles(filter, segmentId).done(function ( response ) {
            if ( response.files ) {
                AppDispatcher.dispatch({
                    actionType: QRConstants.RENDER_SEGMENTS,
                    files: response.files,
                });
            }
        });
    },

}

export default QualityReportActions ;
