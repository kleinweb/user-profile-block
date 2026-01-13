// SPDX-FileCopyrightText: 2025-2026 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-2.0-or-later

import {PanelBody} from '@wordpress/components'
import {useSelect} from '@wordpress/data'
import {PluginSidebar, PluginSidebarMoreMenuItem} from '@wordpress/edit-post'
import {__} from '@wordpress/i18n'
import type {MetaConfig, MetaFieldConfig} from './index'
import {MetaField} from './MetaField'

interface MetaSidebarProps {
  config: MetaConfig
}

export function MetaSidebar({config}: MetaSidebarProps) {
  const postType = useSelect(
    select => select('core/editor').getCurrentPostType() as string,
    [],
  )

  // Get fields for this post type or '_all' fields
  const postConfig = config?.post || {}
  const fieldsForType = postConfig[postType] || {}
  const fieldsForAll = postConfig['_all'] || {}
  const fields = {...fieldsForAll, ...fieldsForType}

  if (Object.keys(fields).length === 0) {
    return null
  }

  return (
    <>
      <PluginSidebarMoreMenuItem target="plugin-name-sidebar">
        {__('Plugin Name', 'plugin-name')}
      </PluginSidebarMoreMenuItem>

      <PluginSidebar
        name="plugin-name-sidebar"
        title={__('Plugin Name', 'plugin-name')}
      >
        <PanelBody
          title={__('Custom Fields', 'plugin-name')}
          initialOpen={true}
        >
          {Object.entries(fields).map(([key, fieldConfig]) => (
            <MetaField
              key={key}
              metaKey={key}
              config={fieldConfig as MetaFieldConfig}
            />
          ))}
        </PanelBody>
      </PluginSidebar>
    </>
  )
}
