import { check, sleep } from 'k6'
import http from 'k6/http'
import encoding from 'k6/encoding'

const apiUrl = 'http://local.matecat.com/'
const testDocument = open('./files/blocks.txt')

const getJwtFromXml = xml => {
  const tokenStartPosition = xml.indexOf('<access>') + ('<access>').length
  const tokenEndPosition = xml.indexOf('</access>') - tokenStartPosition
  return xml.substr(tokenStartPosition, tokenEndPosition)
}

export const msleep = (__ENV.KS_SLEEP === 'yes' ? sleep : x => x)

export const getAdminJwt = () => {
  const params = {
    headers: {
      'Authorization': 'Basic ' + encoding.b64encode('oskars.petriks@tilde.lv:HugoDev!')
    }
  }
  const result = http.get('http://hugodevlogic.tilde.lv:4000/Usermanager.asmx/GetJwt', params)
  const jwt = getJwtFromXml(result.body)

  return jwt
}

export const getMemoryId = params => {
  const result = get('tm', params)
  const memoryObject = JSON.parse(result.body)
  return memoryObject[0].id
}

export const createProject = (params, mtSystemId) => {
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
  return {
    id: bodyObject.data.id_project,
    password: bodyObject.data.password
  }
}

export const getProjectInfo = (params, id, password) => {
  const requestData = {
    projectId: id,
    projectPassword: password
  }
  const result = post('files/info', requestData, params)
  const bodyObject = JSON.parse(result.body)
  return {
    firstSegmentId: bodyObject.firstSegment
  }
}

export const deleteProject = (id, password) => {
  const requestData = {
    projectId: id,
    projectPassword: password
  }

  post('files/delete', requestData)
}

export const checkOk = result => {
  check(result, {
    'status was 200': r => r.status == 200,
  })
}

export const get = (endpoint, params) => {
  params = params || {}
  return http.get(apiUrl + endpoint, params)
}

export const post = (endpoint, payload, params) => {
    params = params || {}
    return http.post(apiUrl + endpoint, payload, params)
}
