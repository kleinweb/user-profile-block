// SPDX-FileCopyrightText: 2025-2026 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-2.0-or-later

import apiFetch from '@wordpress/api-fetch'
import {
  Button,
  Card,
  CardBody,
  CardHeader,
  Notice,
  __experimentalNumberControl as NumberControl,
  SelectControl,
  Spinner,
  TextareaControl,
  TextControl,
  ToggleControl,
} from '@wordpress/components'
import {useCallback, useEffect, useState} from '@wordpress/element'
import {__} from '@wordpress/i18n'

declare const pluginNameSettings: {
  restUrl: string
  restNonce: string
}

interface FieldConfig {
  type: string
  default: unknown
  label: string
  description: string
  inputType: string
  options?: Record<string, string>
  min?: number
  max?: number
  language?: string
}

interface SectionConfig {
  label: string
  fields: Record<string, FieldConfig>
}

interface SettingsAppProps {
  schema: Record<string, SectionConfig>
}

type SettingsValues = Record<string, unknown>

export function SettingsApp({schema}: SettingsAppProps) {
  const [settings, setSettings] = useState<SettingsValues>({})
  const [isLoading, setIsLoading] = useState(true)
  const [isSaving, setIsSaving] = useState(false)
  const [notice, setNotice] = useState<{
    type: 'success' | 'error'
    message: string
  } | null>(null)

  useEffect(() => {
    apiFetch.use(apiFetch.createNonceMiddleware(pluginNameSettings.restNonce))

    apiFetch<SettingsValues>({path: '/plugin-name/v1/settings'})
      .then(data => {
        setSettings(data)
        setIsLoading(false)
      })
      .catch(() => {
        setNotice({
          type: 'error',
          message: __('Failed to load settings.', 'plugin-name'),
        })
        setIsLoading(false)
      })
  }, [])

  const updateSetting = useCallback((key: string, value: unknown) => {
    setSettings(prev => ({...prev, [key]: value}))
  }, [])

  const saveSettings = async () => {
    setIsSaving(true)
    setNotice(null)

    try {
      await apiFetch({
        path: '/plugin-name/v1/settings',
        method: 'POST',
        data: settings,
      })

      setNotice({
        type: 'success',
        message: __('Settings saved.', 'plugin-name'),
      })
    } catch {
      setNotice({
        type: 'error',
        message: __('Failed to save settings.', 'plugin-name'),
      })
    }

    setIsSaving(false)
  }

  if (isLoading) {
    return (
      <div style={{padding: '20px', textAlign: 'center'}}>
        <Spinner />
      </div>
    )
  }

  return (
    <div
      className="plugin-name-settings"
      style={{maxWidth: '800px', margin: '20px auto'}}
    >
      <h1>{__('Plugin Settings', 'plugin-name')}</h1>

      {notice && (
        <Notice
          isDismissible
          onRemove={() => setNotice(null)}
          status={notice.type}
        >
          {notice.message}
        </Notice>
      )}

      {Object.entries(schema).map(([sectionKey, section]) => (
        <Card key={sectionKey} style={{marginBottom: '20px'}}>
          <CardHeader>
            <h2 style={{margin: 0}}>{section.label}</h2>
          </CardHeader>
          <CardBody>
            {Object.entries(section.fields).map(([fieldKey, field]) => (
              <SettingsField
                config={field}
                fieldKey={fieldKey}
                key={fieldKey}
                onChange={value => updateSetting(fieldKey, value)}
                value={settings[fieldKey] ?? field.default}
              />
            ))}
          </CardBody>
        </Card>
      ))}

      <Button
        disabled={isSaving}
        isBusy={isSaving}
        onClick={saveSettings}
        variant="primary"
      >
        {isSaving
          ? __('Saving...', 'plugin-name')
          : __('Save Settings', 'plugin-name')}
      </Button>
    </div>
  )
}

interface SettingsFieldProps {
  fieldKey: string
  config: FieldConfig
  value: unknown
  onChange: (value: unknown) => void
}

function SettingsField({
  fieldKey: _fieldKey,
  config,
  value,
  onChange,
}: SettingsFieldProps) {
  void _fieldKey // unused but required for interface
  const {label, description, inputType, options, min, max} = config

  switch (inputType) {
    case 'toggle':
    case 'checkbox':
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
          max={max}
          min={min}
          onChange={val => onChange(Number(val))}
          value={value as number}
        />
      )

    case 'code':
    case 'textarea':
      return (
        <TextareaControl
          help={description}
          label={label}
          onChange={onChange}
          rows={inputType === 'code' ? 10 : 4}
          style={inputType === 'code' ? {fontFamily: 'monospace'} : undefined}
          value={String(value)}
        />
      )

    case 'password':
      return (
        <TextControl
          help={description}
          label={label}
          onChange={onChange}
          type="password"
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
