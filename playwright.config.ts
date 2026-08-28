import {defineConfig, devices} from '@playwright/test'

/**
 * Read environment variables from file.
 * https://github.com/motdotla/dotenv
 */
// import dotenv from 'dotenv';
// import path from 'path';
// dotenv.config({ path: path.resolve(__dirname, '.env') });

/**
 * See https://playwright.dev/docs/test-configuration.
 */
export default defineConfig({
  testDir: './tests/e2e',
  testIgnore: ['**/example.spec.*'],

  timeout: 30 * 1000,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,

  // https://playwright.dev/docs/test-reporters
  reporter: 'list',

  // https://playwright.dev/docs/api/class-testoptions
  use: {
    baseURL: 'https://127.0.0.1:32788', // or: http => :32787
    // FIXME: NS_ERROR_UNKNOWN_HOST ... prob needs dns, not /etc/hosts
    // baseURL: 'https://user-profile-block.ddev.test',

    // ddev/mkcert compatibility
    ignoreHTTPSErrors: true,

    actionTimeout: 0,

    // https://playwright.dev/docs/trace-viewer
    trace: 'on-first-retry',
  },

  // Configure projects for major browsers
  projects: [
    {
      name: 'chromium',
      use: {...devices['Desktop Chromium']},
    },

    {
      name: 'firefox',
      use: {...devices['Desktop Firefox']},
    },
  ],

  /* Run your local dev server before starting the tests */
  // webServer: {
  //   command: 'npm run start',
  //   url: 'http://127.0.0.1:3000',
  //   reuseExistingServer: !process.env.CI,
  // },
})
