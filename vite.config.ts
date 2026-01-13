// SPDX-FileCopyrightText: 2025-2026 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-2.0-or-later

import fs from 'node:fs'
import {resolve} from 'node:path'
import react from '@vitejs/plugin-react'
import externalGlobals from 'rollup-plugin-external-globals'
import {defineConfig, type Plugin} from 'vite'

// WordPress dependencies - externalized in production to use wp.* globals
// In dev mode, these are resolved from node_modules
const wpExternals = [
  '@wordpress/blocks',
  '@wordpress/block-editor',
  '@wordpress/components',
  '@wordpress/compose',
  '@wordpress/core-data',
  '@wordpress/data',
  '@wordpress/date',
  '@wordpress/element',
  '@wordpress/i18n',
  '@wordpress/api-fetch',
  '@wordpress/plugins',
  '@wordpress/edit-post',
]

// Map @wordpress/* packages to wp.* globals for production builds
const wpGlobals = Object.fromEntries(
  wpExternals.map(pkg => {
    const name = pkg
      .replace('@wordpress/', '')
      .replace(/-([a-z])/g, (_, c: string) => c.toUpperCase())
    return [pkg, `wp.${name}`]
  }),
)

// Write hot file for PHP to detect dev server
const hotFile = (): Plugin => ({
  name: 'hot-file',
  configureServer(server) {
    const hotPath = resolve(__dirname, 'public/build/hot')

    server.httpServer?.once('listening', () => {
      const address = server.httpServer?.address()
      const protocol = server.config.server.https ? 'https' : 'http'
      const host = typeof address === 'object' ? address?.address : 'localhost'
      const port = typeof address === 'object' ? address?.port : 5173

      fs.mkdirSync(resolve(__dirname, 'public/build'), {recursive: true})
      fs.writeFileSync(hotPath, `${protocol}://${host}:${port}`)
    })

    // Clean up on exit
    const cleanup = () => {
      if (fs.existsSync(hotPath)) {
        fs.unlinkSync(hotPath)
      }
    }

    process.on('exit', cleanup)
    process.on('SIGINT', () => {
      cleanup()
      process.exit()
    })
    process.on('SIGTERM', () => {
      cleanup()
      process.exit()
    })
  },
})

export default defineConfig({
  plugins: [
    react({
      jsxRuntime: 'classic',
    }),
    hotFile(),
  ],

  base: './',

  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        settings: resolve(__dirname, 'resources/js/settings/index.tsx'),
        editor: resolve(__dirname, 'resources/js/editor/index.tsx'),
        frontend: resolve(__dirname, 'resources/js/frontend/index.ts'),
        'block-user-profile': resolve(
          __dirname,
          'resources/blocks/user-profile/index.tsx',
        ),
      },
      external: [...wpExternals, 'react', 'react-dom'],
      plugins: [
        externalGlobals({
          ...wpGlobals,
          react: 'React',
          'react-dom': 'ReactDOM',
        }),
      ],
      output: {
        entryFileNames: 'assets/[name]-[hash].js',
        chunkFileNames: 'assets/[name]-[hash].js',
        assetFileNames: 'assets/[name]-[hash][extname]',
        globals: {
          ...wpGlobals,
          react: 'React',
          'react-dom': 'ReactDOM',
        },
      },
    },
  },

  server: {
    host: 'localhost',
    port: 5173,
    strictPort: true,
    cors: true,
    hmr: {
      host: 'localhost',
    },
  },

  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/js'),
    },
  },
})
