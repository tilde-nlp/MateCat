import { post, checkOk, msleep } from '../utils.js'
import { group } from 'k6'

export default data => {
    group('Get segment list', () => testGetSegments(data.params, data.projectData))
    group('Set active', () => testSetActive(data.params, data.projectData.firstSegmentId))
    group('Set editing time', () => testSetEditingTime(data.params, data.projectData.id))
    group('Translate', () => testTranslate(data.params, data.projectData.firstSegmentId, data.mtSystemId))
}

const testGetSegments = (params, projectData) => {
    const requestData = {
        projectId: projectData.id,
        projectPassword: projectData.password,
        segment: projectData.firstSegmentId,
        where: 'after',
        step: 10
    }
    const result = post('segments', requestData, params)
    checkOk(result)
    msleep(1)
}

const testSetActive = (params, segmentId) => {
    const requestData = {
        segmentId: segmentId
    }
    const result = post('segments/set-active', requestData, params)
    checkOk(result)
    msleep(1)
}

const testSetEditingTime = (params, projectId) => {
    const requestData = {
        projectId: projectId,
        editingTime: 1000
    }
    const result = post('segments/set-edit-time', requestData, params)
    checkOk(result)
    msleep(1)
}

const testTranslate = (params, segmentId, mtSystemId) => {
    const requestData = {
        segmentId: segmentId,
        status: 'translated',
        translation: 'Šis bloks ir zils',
        segment: 'This block is blue',
        saveType: 'MT',
        saveMatch: '70',
        mtId: mtSystemId
    }
    const result = post('segments/translate', requestData, params)
    checkOk(result)
    msleep(1)
}