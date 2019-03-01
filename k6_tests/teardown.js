import { post, servicePost, adminParams } from './utils.js'

export default setupData => {
    deleteProject(setupData.projectData.id, setupData.projectData.password, setupData.params)
    deleteUser(setupData.userData.clientId)
}

const deleteUser = clientId => {
    const requestData = {
        clientId: clientId
    }
    servicePost('DeleteAPIUser', requestData , adminParams)
}

const deleteProject = (id, password, params) => {
    const requestData = {
        projectId: id,
        projectPassword: password
    }

    post('files/delete', requestData, params)
}