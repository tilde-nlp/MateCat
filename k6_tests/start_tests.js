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

export const options = {
    vus: 5,
    duration: '90s'
}

export function setup() {
  return getSetupData()
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
  tearItDown(data)
}
