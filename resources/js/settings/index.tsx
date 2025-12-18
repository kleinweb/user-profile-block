// SPDX-FileCopyrightText: 2025 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-3.0-or-later

import {createRoot} from '@wordpress/element'
import {SettingsApp} from './SettingsApp'

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('plugin-name-settings')

  if (container) {
    const initialSettings = container.dataset.settings
      ? JSON.parse(container.dataset.settings)
      : {}

    createRoot(container).render(<SettingsApp schema={initialSettings} />)
  }
})
