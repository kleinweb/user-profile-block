import {registerBlockType} from '@wordpress/blocks'
import {Edit} from './edit'
import metadata from './block.json'

// @ts-expect-error - block.json types don't match strict BlockConfiguration
registerBlockType(metadata.name, {
  ...metadata,
  edit: Edit,
  save: () => null,
})
