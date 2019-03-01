import { get, post, servicePost, getValueFromXml, adminParams } from './utils.js'
import CONFIG from './CONFIG.js'
import encoding from 'k6/encoding'
import http from 'k6/http'

export default () => {
    const setupData = {}
    setupData['userData'] = createUser()
    setupData['params'] = {
        headers: {
            'Authorization': 'Bearer ' + setupData.userData.jwt
        }
    }

    setupData['mtSystemId'] = CONFIG.mtSystemId
    setupData['memoryId'] = getMemoryId(setupData.params)
    setupData['projectData'] = createProject(setupData.params, setupData.mtSystemId)

    return setupData
}

const createUser = () => {
    const adminGroup = getAdminGroup()
    const requestData = {
      rememberYOurCreator: 'true',
      displayName: 'Load test user',
      group: adminGroup,
      domain: 'example.com',
      ipRange: '127.0.0.1-244',
      comments: 'Load testing user'
    }
    const result = servicePost('CreateNewAPIUserID', requestData, adminParams)
    const clientId = getValueFromXml(result.body, '<string xmlns="http://www.tilde.com/LetsMT/UserManager">', '</string>')
  
    const params = {
      headers: {
        'Authorization': 'Basic ' + encoding.b64encode(clientId + ':' + CONFIG.clientIdPassword)
      }
    }
  
    const jwtResult = servicePost('GetJwt', {}, params)
    const jwt = getValueFromXml(jwtResult.body, '<access>', '</access>')
  
    return {
      jwt: jwt,
      clientId: clientId
    }
  }

  const getAdminGroup = () => {
    const result = servicePost('WhoAmI', {} , adminParams)
    const group = getValueFromXml(result.body, '<Group>', '</Group>')
  
    return group
  }

  const getMemoryId = params => {
    const result = get('tm', params)
    const memoryObject = JSON.parse(result.body)
    return memoryObject[0].id
  }

  const testDocument = open('./files/blocks.txt')
  const createProject = (params, mtSystemId) => {
    const requestData = {
      source_language: 'en-US',
      target_language: 'lv-LV',
      tm_pretranslate: 0,
      mt_pretranslate: 0,
      mt_system: mtSystemId,
      files: http.file(testDocument, 'blocks.txt', 'text/plain')
    }
    const result = post('files/upload', requestData, params)
    const bodyObject = JSON.parse(result.body)
    const projectData = {
        id: bodyObject.data.id_project,
        password: bodyObject.data.password
    }
    projectData['firstSegmentId'] = getFirstSegmentId(params, projectData.id, projectData.password)

    return projectData
  }

  const getFirstSegmentId = (params, id, password) => {
    const requestData = {
      projectId: id,
      projectPassword: password
    }
    const result = post('files/info', requestData, params)
    const bodyObject = JSON.parse(result.body)
    return bodyObject.firstSegment
  }
