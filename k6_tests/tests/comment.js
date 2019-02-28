import { post, checkOk, msleep } from '../utils.js'
import { group } from 'k6'

export default data => {
    group('Add', () => testAddComment(data.params, data.projectData.firstSegmentId))
    group('Resolve', () => testResolveComment(data.params, data.projectData.firstSegmentId))
}

const testAddComment = (params, segmentId) => {
    const requestData = {
        segmentId: segmentId,
        message: 'Some witty comment'
    }
    const result = post('comments/add', requestData, params)
    checkOk(result)
    msleep(1)
}

const testResolveComment = (params, segmentId) => {
    const requestData = {
        segmentId: segmentId
    }
    const result = post('comments/resolve', requestData, params)
    checkOk(result)
    msleep(1)
}