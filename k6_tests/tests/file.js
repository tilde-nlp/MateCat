import { post, checkOk, msleep } from '../utils.js'
import { group } from 'k6'

export default data => {
    group('Get files', () => testGetFiles(data.params))
    group('Get file info', () => testGetFileInfo(data.params, data.projectData.id, data.projectData.password))
    group('Get file analysis', () => testGetFileAnalysis(data.params, data.projectData.id, data.projectData.password))
    group('Get file stats', () => testGetFileStats(data.params, data.projectData.id, data.projectData.password))
    group('Get file creation status', () => testGetFileCreationStatus(data.params, data.projectData.id, data.projectData.password))
}

const testGetFiles = params => {
    const requestData = {
        page: 0,
        step: 10
    }
    const result = post('files', requestData, params)
    checkOk(result)
    msleep(1)
}

const testGetFileInfo = (params, projectId, projectPassword) => {
    const requestData = {
        projectId: projectId,
        projectPassword: projectPassword
    }
    const result = post('files/info', requestData, params)
    checkOk(result)
    msleep(1)
}

const testGetFileAnalysis = (params, projectId, projectPassword) => {
    const requestData = {
        projectId: projectId,
        projectPassword: projectPassword
    }
    const result = post('files/analysis', requestData, params)
    checkOk(result)
    msleep(1)
}

const testGetFileStats = (params, projectId, projectPassword) => {
    const requestData = {
        projectId: projectId,
        projectPassword: projectPassword
    }
    const result = post('files/stats', requestData, params)
    checkOk(result)
    msleep(1)
}

const testGetFileCreationStatus = (params, projectId, projectPassword) => {
    const requestData = {
        projectId: projectId,
        projectPassword: projectPassword
    }
    const result = post('files/creation-status', requestData, params)
    checkOk(result)
    msleep(1)
}