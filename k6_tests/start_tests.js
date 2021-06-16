import { group } from 'k6'
import getSetupData from './setup.js'
import tearItDown from './teardown.js'
import MtTests from './tests/mt.js'
import UserTests from './tests/user.js'
import TmTests from './tests/tm.js'
import SegmentTests from './tests/segment.js'
import CommentTests from './tests/comment.js'
import SettingsTests from './tests/settings.js'
import FileTests from './tests/file.js'
import CONFIG from './CONFIG.js'

export const options = {
    vus: CONFIG.virtualUserCount,
    duration: CONFIG.testDuration
}

export function setup() {
  return getSetupData(CONFIG.virtualUserCount)
}

export default function(data) {
  const currentVUData = data[__VU - 1]
  group('User', () => UserTests(currentVUData))
  group('MT', () => MtTests(currentVUData))
  group('TM', () => TmTests(currentVUData))
  group('Segment', () => SegmentTests(currentVUData))
  group('Comment', () => CommentTests(currentVUData))
  group('Settings', () => SettingsTests(currentVUData))
  group('Files', () => FileTests(currentVUData))
}

export function teardown(data) {
  tearItDown(data)
}
