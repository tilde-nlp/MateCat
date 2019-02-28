import { group } from 'k6'
import { createProject, getProjectInfo, deleteProject, getMemoryId, getAdminJwt } from './utils.js'
import MtTests from './tests/mt.js'
import UserTests from './tests/user.js'
import TmTests from './tests/tm.js'
import SegmentTests from './tests/segment.js'
import CommentTests from './tests/comment.js'
import SettingsTests from './tests/settings.js'
import FileTests from './tests/file.js'

export const options = {
    vus: 20,
    duration: '2m'
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
  const projectInfo = getProjectInfo(data.params, data.projectData.id, data.projectData.password)
  data.projectData['firstSegmentId'] = projectInfo.firstSegmentId
  data['memoryId'] = getMemoryId(data.params)
  
  return data
}

export default function(data) {
  group('User', () => UserTests(data))
  group('MT', () => MtTests(data))
  group('TM', () => TmTests(data))
  group('Segment', () => SegmentTests(data))
  group('Comment', () => CommentTests(data))
  group('Settings', () => SettingsTests(data))
  group('Files', () => FileTests(data))
}

export function teardown(data) {
  deleteProject(data.projectData.id, data.projectData.password)
}
