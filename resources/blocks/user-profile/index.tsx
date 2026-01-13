// SPDX-FileCopyrightText: 2025-2026 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-2.0-or-later

import {registerBlockType} from '@wordpress/blocks'
import metadata from './block.json'
import {Edit} from './edit'

// @ts-expect-error - block.json types don't match strict BlockConfiguration
registerBlockType(metadata.name, {
  ...metadata,
  edit: Edit,
  save: () => null,
})
