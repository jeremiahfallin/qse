/** @type {import('ts-jest').JestConfigWithTsJest} */
module.exports = {
  preset: 'ts-jest',
  testEnvironment: 'node', // Or 'jsdom' if testing browser-specific features
  roots: ['<rootDir>/src'], // Look for tests in the src directory
  moduleNameMapper: {
    // Handle module aliases (if you have them in tsconfig.json, like @/*)
    '^@/(.*)$': '<rootDir>/src/$1',
  },
  // Add any other Jest specific configurations here
  // For example, setupFilesAfterEnv for global test setup
  // setupFilesAfterEnv: ['<rootDir>/jest.setup.js'],
};
