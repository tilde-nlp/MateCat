import { get, post, checkOk, msleep} from '../utils.js'
import { group } from 'k6'

export default data => {
    group('Get list', () => testTm(data.params))
    group('Get matches', () => testMatches(data.params, data.projectData))
    group('Concordance', () => testConcordance(data.params, data.projectData))
    group('Get list for file', () => testForFile(data.params, data.projectData))
    group('Save settings', () => testSaveSettings(data.params, data.memoryId))
    group('Save settings for file', () => testSaveSettingsForFile(data.params, data.memoryId))
}

const testTm = params => {
    const result = get('tm', params)
    checkOk(result)
    msleep(1)
}

const testMatches = (params, projectData) => {
    const requestData = {
        projectId: projectData.id,
        projectPassword: projectData.password,
        text: 'This block is blue.',
        sourceLang: 'en-US',
        targetLang: 'lv-LV',
        count: 5
    }
    const result = post('tm/matches', requestData, params)
    checkOk(result)
    msleep(1)
}

const testConcordance = (params, projectData) => {
    const requestData = {
        projectId: projectData.id,
        text: 'This block is blue.',
        sourceLang: 'en-US',
        targetLang: 'lv-LV'
    }
    const result = post('tm/concordance-search', requestData, params)
    checkOk(result);
    msleep(1);
}

const testForFile = (params, projectData) => {
    const result = get('tm/for-file?projectId=' + projectData.id, params)
    checkOk(result);
    msleep(1);
}

const testSaveSettings = (params, memoryId) => {
    const requestData = {
        id: memoryId,
        readMemory: 0,
        writeMemory: 0
    }
    const result = post('tm/save-settings', requestData, params)
    checkOk(result);
    msleep(1);
}

const testSaveSettingsForFile = (params, memoryId) => {
    const requestData = {
        id: memoryId,
        readMemory: 1,
        writeMemory: 1
    }
    const result = post('tm/save-settings-for-file', requestData, params)
    checkOk(result);
    msleep(1);
}
