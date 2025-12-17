import {registerBlockType} from '@wordpress/blocks'
import metadata from './block.json'
import {Edit} from './edit'

// @ts-expect-error - block.json types don't match strict BlockConfiguration
registerBlockType(metadata.name, {
  ...metadata,
  edit: Edit,
  save: () => null,
})
