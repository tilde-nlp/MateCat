import { check, sleep } from 'k6'
import http from 'k6/http'
import encoding from 'k6/encoding'
import CONFIG from './CONFIG.js'

export const adminParams = {
  headers: {
    'Authorization': 'Basic ' + encoding.b64encode(CONFIG.adminUsername + ':' + CONFIG.adminPassword)
  }
}

export const servicePost = (endpoint, payload, params) => {
  payload = payload || {}
  return http.post(CONFIG.serviceUrl + endpoint, payload, params)
}

export const get = (endpoint, params) => {
  params = params || {}
  return http.get(CONFIG.apiUrl + endpoint, params)
}

export const post = (endpoint, payload, params) => {
    params = params || {}
    return http.post(CONFIG.apiUrl + endpoint, payload, params)
}

export const getValueFromXml = (xml, keyStart, keyEnd) => {
  const valueStartPosition = xml.indexOf(keyStart) + (keyStart).length
  const valueEndPosition = xml.indexOf(keyEnd) - valueStartPosition
  return xml.substr(valueStartPosition, valueEndPosition)
}

export const msleep = (__ENV.KS_SLEEP === 'yes' ? sleep : x => x)

export const checkOk = result => {
  check(result, {
    'status was 200': r => r.status == 200,
  })
  try {
    const bodyObject = JSON.parse(result.body)
    check(bodyObject, {
      'got valid response': b => !b.hasOwnProperty('code')
    })
  } catch (e) {
    check({}, {
      'got valid response': () => false
    })
  }
}
