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
          checked={Boolean(value)}
          help={description}
          label={label}
          onChange={onChange}
        />
      )

    case 'select':
      return (
        <SelectControl
          help={description}
          label={label}
          onChange={onChange}
          options={Object.entries(options || {}).map(([val, lab]) => ({
            value: val,
            label: lab,
          }))}
          value={String(value)}
        />
      )

    case 'number':
      return (
        <NumberControl
          help={description}
          label={label}
          onChange={val => onChange(Number(val))}
          value={value as number}
        />
      )

    case 'textarea':
      return (
        <TextareaControl
          help={description}
          label={label}
          onChange={onChange}
          value={String(value)}
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
              renderContent={({onClose}) => (
                <DatePicker
                  currentDate={value as string}
                  onChange={date => {
                    onChange(date ? format('Y-m-d', date) : '')
                    onClose()
                  }}
                />
              )}
              renderToggle={({isOpen, onToggle}) => (
                <Button
                  aria-expanded={isOpen}
                  onClick={onToggle}
                  variant="secondary"
                >
                  {value ? format('Y-m-d', value as string) : 'Select date'}
                </Button>
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
          help={description}
          label={label}
          onChange={onChange}
          // @ts-expect-error - color input type not in TextControl types but works
          type="color"
          value={String(value)}
        />
      )

    case 'url':
      return (
        <TextControl
          help={description}
          label={label}
          onChange={onChange}
          type="url"
          value={String(value)}
        />
      )

    case 'tel':
      return (
        <TextControl
          help={description}
          label={label}
          onChange={onChange}
          type="tel"
          value={String(value)}
        />
      )

    default:
      return (
        <TextControl
          help={description}
          label={label}
          onChange={onChange}
          value={String(value)}
        />
      )
  }
}
