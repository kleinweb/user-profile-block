// SPDX-FileCopyrightText: 2025-2026 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-2.0-or-later

import {registerPlugin} from '@wordpress/plugins'
import {MetaSidebar} from './MetaSidebar'

declare const pluginNameEditor: {
  metaConfig: MetaConfig
  restNonce: string
}

export interface MetaFieldConfig {
  key: string
  label: string
  description: string
  type: string
  inputType: string
  options?: Record<string, string>
  default: unknown
}

export interface MetaConfig {
  post?: Record<string, Record<string, MetaFieldConfig>>
  term?: Record<string, Record<string, MetaFieldConfig>>
  user?: Record<string, Record<string, MetaFieldConfig>>
}

// Register the plugin for the post editor sidebar
registerPlugin('plugin-name-meta-sidebar', {
  render: () => <MetaSidebar config={pluginNameEditor.metaConfig} />,
  icon: 'admin-generic',
})
