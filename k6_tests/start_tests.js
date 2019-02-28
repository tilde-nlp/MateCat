import { group } from 'k6'
import { createProject, getProjectInfo, deleteProject, getMemoryId, getAdminJwt } from './utils.js'
import MtTests from './tests/mt.js'
import UserTests from './tests/user.js'
import TmTests from './tests/tm.js'
import SegmentTests from './tests/segment.js'

export const options = {
    vus: 1,
    duration: '30s'
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
  data.projectData['firstSegmentId'] = getProjectInfo(data.params, data.projectData.id, data.projectData.password)['firstSegmentId']
  data['memoryId'] = getMemoryId(data.params)
  
  return data
}

export default function(data) {
  group('User', () => UserTests(data))
  group('MT', () => MtTests(data))
  group('TM', () => TmTests(data))
  group('Segment', () => SegmentTests(data))
}

export function teardown(data) {
  deleteProject(data.projectData.id, data.projectData.password)
}
