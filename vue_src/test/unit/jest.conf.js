const path = require('path')

module.exports = {
  rootDir: path.resolve(__dirname, '../../'),
  setupFiles: ['<rootDir>/test/unit/setup'],
  moduleFileExtensions: [
    'js',
    'vue'
  ],
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/src/$1',
    '^c/(.*)$': '<rootDir>/src/components/$1',
  },
  transform: {
    '^.+\\.js$': '<rootDir>/node_modules/babel-jest',
    '.*\\.(vue)$': '<rootDir>/node_modules/vue-jest'
  },
  snapshotSerializers: [
    '<rootDir>/node_modules/jest-serializer-vue'
  ],
  collectCoverage: true,
  coverageDirectory: '<rootDir>/test/unit/coverage',
  collectCoverageFrom: [
    'src/**/*.{js,vue}',
    '!src/app.js',
    '!src/router/router.js',
    '!**/assets/**',
    '!**/node_modules/**'
  ]
}
