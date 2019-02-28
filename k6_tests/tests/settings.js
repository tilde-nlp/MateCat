import { get, post, checkOk, msleep } from '../utils.js'
import { group } from 'k6'

export default data => {
    group('Save mt', () => testSaveMtSystem(data.params, data.projectData.id, data.mtSystemId))
    group('Save mt pretranslate', () => testSaveMtPretranslate(data.params))
    group('Save tm pretranslate', () => testSaveTmPretranslate(data.params))
    group('Save update mt', () => testSaveUpdateMt(data.params))
    group('Save update mt for file', () => testSaveUpdateMtForFile(data.params, data.projectData.id))
    group('Get update mt for file', () => testGetUpdateMtForFile(data.params, data.projectData.id))
}

const testSaveMtSystem = (params, projectId, mtSystemId) => {
    const requestData = {
        projectId: projectId,
        mtSystemId: mtSystemId
    }
    const result = post('settings/save-mt-system', requestData, params)
    checkOk(result)
    msleep(1)
}

const testSaveMtPretranslate = params => {
    const requestData = {
        pretranslate: 0
    }
    const result = post('settings/save-mt-pretranslate', requestData, params)
    checkOk(result)
    msleep(1)
}

const testSaveTmPretranslate = params => {
    const requestData = {
        pretranslate: 0
    }
    const result = post('settings/save-tm-pretranslate', requestData, params)
    checkOk(result)
    msleep(1)
}

const testSaveUpdateMt = params => {
    const requestData = {
        updateMt: 0
    }
    const result = post('settings/save-update-mt', requestData, params)
    checkOk(result)
    msleep(1)
}

const testSaveUpdateMtForFile = (params, projectId) => {
    const requestData = {
        updateMt: 0,
        projectId: projectId
    }
    const result = post('settings/save-update-mt-for-file', requestData, params)
    checkOk(result)
    msleep(1)
}

const testGetUpdateMtForFile = (params, projectId) => {
    const result = get('settings/update-mt-for-file?projectId=' + projectId, params)
    checkOk(result)
    msleep(1)
}