// SPDX-FileCopyrightText: 2025 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-2.0-or-later

import {resolve} from 'node:path'
import react from '@vitejs/plugin-react'
import {defineConfig} from 'vitest/config'

export default defineConfig({
  plugins: [react()],

  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./vitest.setup.tsx'],
    include: ['resources/**/*.test.{ts,tsx}'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'html'],
      include: ['resources/**/*.{ts,tsx}'],
      exclude: ['resources/**/*.test.{ts,tsx}', 'resources/types/**'],
    },
  },

  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/js'),
    },
  },
})
