import { GET } from './route'; // Adjust path as necessary
import assert from 'assert';

// Mock PrismaClient
jest.mock('@prisma/client', () => {
  const mockPrismaClient = {
    user: {
      findMany: jest.fn(),
    },
    $disconnect: jest.fn(),
  };
  return { PrismaClient: jest.fn(() => mockPrismaClient) };
});

// Mock NextResponse
const mockNextResponseJson = jest.fn();
jest.mock('next/server', () => ({
  NextResponse: {
    json: (...args: any[]) => {
      mockNextResponseJson(...args);
      // Return a dummy response structure for type compatibility if needed by the calling code
      // For this test, we'll mostly inspect mockNextResponseJson.mock.calls
      return { status: args[1]?.status || 200, json: async () => args[0] } as any;
    },
  },
}));

describe('API Route /api/users', () => {
  // Get the mocked Prisma client instance
  // We need to import it *after* the jest.mock calls have been configured
  let prismaMock: any;

  beforeEach(()_ => {
    // Clear all mocks before each test
    jest.clearAllMocks();
    // Dynamically import to get the mocked instance
    const { PrismaClient } = require('@prisma/client');
    prismaMock = new PrismaClient();
  });

  test('should fetch users and return them as JSON', async () => {
    const predefinedUsers = [
      { id: 1, login_name: 'Alice', email_address: 'alice@example.com' },
      { id: 2, login_name: 'Bob', email_address: 'bob@example.com' },
    ];

    // Setup the mock for findMany
    prismaMock.user.findMany.mockResolvedValue(predefinedUsers);

    // Call the GET handler
    const response = await GET();

    // Assertions
    // Check if findMany was called
    assert.ok(prismaMock.user.findMany.mock.calls.length > 0, 'prisma.user.findMany should be called');

    // Check the arguments of NextResponse.json
    assert.strictEqual(mockNextResponseJson.mock.calls.length, 1, 'NextResponse.json should be called once');
    const responseBody = mockNextResponseJson.mock.calls[0][0];
    const responseOptions = mockNextResponseJson.mock.calls[0][1];

    assert.deepStrictEqual(responseBody, predefinedUsers, 'Response body should match predefined users');
    assert.strictEqual(responseOptions, undefined, 'Response status should be 200 (default, so options should be undefined)');

    // Check if $disconnect was called
    assert.ok(prismaMock.$disconnect.mock.calls.length > 0, 'prisma.$disconnect should be called');

    console.log('Test passed: Fetched users successfully.');
  });

  test('should return 500 on error', async () => {
    const errorMessage = 'Database connection error';
    prismaMock.user.findMany.mockRejectedValue(new Error(errorMessage));

    // Call the GET handler
    const response = await GET();

    // Assertions
    assert.ok(prismaMock.user.findMany.mock.calls.length > 0, 'prisma.user.findMany should be called');

    assert.strictEqual(mockNextResponseJson.mock.calls.length, 1, 'NextResponse.json should be called once for error');
    const errorResponseBody = mockNextResponseJson.mock.calls[0][0];
    const errorResponseOptions = mockNextResponseJson.mock.calls[0][1];

    assert.deepStrictEqual(errorResponseBody, { error: 'Failed to fetch users. Please try again later.' });
    assert.strictEqual(errorResponseOptions?.status, 500, 'Response status should be 500 on error');

    assert.ok(prismaMock.$disconnect.mock.calls.length > 0, 'prisma.$disconnect should be called even on error');

    console.log('Test passed: Handled error correctly.');
  });
});

// Rudimentary way to run tests if not using a test runner
async function runTests() {
  // Mock console.error to avoid polluting output during expected error test
  const originalConsoleError = console.error;
  console.error = jest.fn();

  try {
    // This is a bit of a hack since we don't have a real test runner environment
    // We'll manually call the describe and test functions.
    // In a real Jest/Vitest setup, the CLI would do this.
    const tests: Record<string, Function> = {};
    global.describe = (name: string, fn: Function) => { fn(); };
    global.test = (name: string, fn: Function) => { tests[name] = fn; };
    global.beforeEach = (fn: Function) => { fn(); }; // Simplified beforeEach

    // Load the test file again to register tests
    require('./route.test.ts');

    for (const testName in tests) {
      console.log(`\nRunning test: ${testName}`);
      // Reset mocks for each test manually since beforeEach might not work as expected here
      jest.clearAllMocks();
      const { PrismaClient } = require('@prisma/client');
      prismaMock = new PrismaClient();
      await tests[testName]();
    }
    console.log("\nAll route.test.ts tests simulated.");
  } catch (e: any) {
    console.error("A test assertion failed:", e.message);
    console.error(e.stack);
  } finally {
    // Restore console.error
    console.error = originalConsoleError;
  }
}

// Uncomment to run if this file is executed directly (e.g. `ts-node route.test.ts`)
// runTests();
// Note: Running this directly with ts-node might be complex due to Jest's globals (jest, describe, test, etc.)
// This setup is more for demonstrating the test structure for a Jest/Vitest environment.
// For pure Node.js assert, the structure would be simpler without Jest mocks.

// To properly run this, you'd typically:
// 1. Install Jest: `npm install --save-dev jest @types/jest ts-jest`
// 2. Configure Jest (e.g., jest.config.js): `module.exports = { preset: 'ts-jest', testEnvironment: 'node' };`
// 3. Add a test script to package.json: `"test": "jest"`
// 4. Run `npm test`
// The `jest.mock` calls are Jest specific. Without Jest, manual mocking would be needed.
// For now, this file structure demonstrates the intended test logic.
// Actual execution in this environment is not feasible without a test runner.
// The goal is to create the file with the test logic.
declare global {
  namespace NodeJS {
    interface Global {
      describe: (name: string, fn: () => void) => void;
      test: (name: string, fn: () => Promise<void> | void) => void;
      beforeEach: (fn: () => void) => void;
      // Add other Jest globals if needed, e.g., afterEach, beforeAll, afterAll
    }
  }
}
