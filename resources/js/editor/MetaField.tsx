// SPDX-FileCopyrightText: 2025-2026 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-2.0-or-later

import {
  Button,
  DatePicker,
  Dropdown,
  __experimentalNumberControl as NumberControl,
  SelectControl,
  TextareaControl,
  TextControl,
  ToggleControl,
} from '@wordpress/components'
import {useEntityProp} from '@wordpress/core-data'
import {useSelect} from '@wordpress/data'
import {format} from '@wordpress/date'
import type {MetaFieldConfig} from './index'

interface MetaFieldProps {
  metaKey: string
  config: MetaFieldConfig
}

export function MetaField({metaKey, config}: MetaFieldProps) {
  const postType = useSelect(
    select => select('core/editor').getCurrentPostType() as string,
    [],
  )

  const [meta, setMeta] = useEntityProp('postType', postType as string, 'meta')

  const value = meta?.[metaKey] ?? config.default

  const onChange = (newValue: unknown) => {
    setMeta({...meta, [metaKey]: newValue})
  }

  const {label, description, inputType, options} = config

  switch (inputType) {
    case 'checkbox':
    case 'toggle':
      return (
        <ToggleControl
          label={label}
          help={description}
          checked={Boolean(value)}
          onChange={onChange}
        />
      )

    case 'select':
      return (
        <SelectControl
          label={label}
          help={description}
          value={String(value)}
          options={Object.entries(options || {}).map(([val, lab]) => ({
            value: val,
            label: lab,
          }))}
          onChange={onChange}
        />
      )

    case 'number':
      return (
        <NumberControl
          label={label}
          help={description}
          value={value as number}
          onChange={val => onChange(Number(val))}
        />
      )

    case 'textarea':
      return (
        <TextareaControl
          label={label}
          help={description}
          value={String(value)}
          onChange={onChange}
        />
      )

    case 'date':
      return (
        <div style={{marginBottom: '16px'}}>
          {/* biome-ignore lint/a11y/noLabelWithoutControl: False positive */}
          <label>
            <div
              style={{display: 'block', marginBottom: '8px', fontWeight: 500}}
            >
              {label}
            </div>
            <Dropdown
              renderToggle={({isOpen, onToggle}) => (
                <Button
                  variant="secondary"
                  onClick={onToggle}
                  aria-expanded={isOpen}
                >
                  {value ? format('Y-m-d', value as string) : 'Select date'}
                </Button>
              )}
              renderContent={({onClose}) => (
                <DatePicker
                  currentDate={value as string}
                  onChange={date => {
                    onChange(date ? format('Y-m-d', date) : '')
                    onClose()
                  }}
                />
              )}
            />
          </label>

          {description && (
            <p style={{marginTop: '4px', color: '#757575', fontSize: '12px'}}>
              {description}
            </p>
          )}
        </div>
      )

    case 'color':
      return (
        <TextControl
          label={label}
          help={description}
          value={String(value)}
          // @ts-expect-error - color input type not in TextControl types but works
          type="color"
          onChange={onChange}
        />
      )

    case 'url':
      return (
        <TextControl
          label={label}
          help={description}
          value={String(value)}
          type="url"
          onChange={onChange}
        />
      )

    case 'tel':
      return (
        <TextControl
          label={label}
          help={description}
          value={String(value)}
          type="tel"
          onChange={onChange}
        />
      )

    default:
      return (
        <TextControl
          label={label}
          help={description}
          value={String(value)}
          onChange={onChange}
        />
      )
  }
}
