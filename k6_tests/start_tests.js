import { group } from 'k6'
import { createProject, deleteProject, getMemoryId, getAdminJwt } from './utils.js'
import MtTests from './tests/mt.js'
import UserTests from './tests/user.js'
import TmTests from './tests/tm.js'

export const options = {
    vus: 1,
    duration: '15s'
}

export function setup() {
  const adminJwt = getAdminJwt()

  const data = {
    mtSystemId: 'smt-31896be2-4f05-428e-a3c1-1221ab78141a'
  }
  data['params'] = {
    headers: {
      'Authorization': 'Bearer ' + adminJwt
    }
  }

  data['projectData'] = createProject(data.params, data.mtSystemId)
  data['memoryId'] = getMemoryId(data.params)
  
  return data
}

export default function(data) {
  group('User', () => UserTests(data))
  group('MT', () => MtTests(data))
  group('TM', () => TmTests(data))
}

export function teardown(data) {
  deleteProject(data.projectData.projectId, data.projectData.password)
}
