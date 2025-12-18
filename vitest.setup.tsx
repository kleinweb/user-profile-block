// SPDX-FileCopyrightText: 2025 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-2.0-or-later

import '@testing-library/jest-dom/vitest'
import {cleanup} from '@testing-library/react'
import {afterEach, vi} from 'vitest'

// Cleanup after each test
afterEach(() => {
  cleanup()
})

// Mock WordPress packages
vi.mock('@wordpress/i18n', () => ({
  __: (text: string) => text,
  _x: (text: string) => text,
  _n: (single: string, plural: string, number: number) =>
    number === 1 ? single : plural,
  sprintf: (format: string, ...args: unknown[]) => {
    let i = 0
    return format.replace(/%[sd]/g, () => String(args[i++] ?? ''))
  },
}))

vi.mock('@wordpress/element', async () => {
  const actual = await vi.importActual<typeof import('react')>('react')
  return {
    ...actual,
    createInterpolateElement: (text: string) => text,
  }
})

vi.mock('@wordpress/data', () => ({
  useSelect: vi.fn(() => ({})),
  useDispatch: vi.fn(() => ({})),
  select: vi.fn(() => ({})),
  dispatch: vi.fn(() => ({})),
}))

vi.mock('@wordpress/core-data', () => ({
  useEntityProp: vi.fn(() => [{}, vi.fn()]),
}))

vi.mock('@wordpress/block-editor', () => ({
  useBlockProps: vi.fn((props = {}) => ({
    ...props,
    className: `wp-block ${props.className || ''}`.trim(),
  })),
  InspectorControls: ({children}: {children: React.ReactNode}) => children,
  RichText: vi.fn(),
}))

vi.mock('@wordpress/components', () => ({
  Button: ({
    children,
    onClick,
  }: {
    children: React.ReactNode
    onClick?: () => void
  }) => (
    <button type="button" onClick={onClick}>
      {children}
    </button>
  ),
  PanelBody: ({
    children,
    title,
  }: {
    children: React.ReactNode
    title: string
  }) => (
    <div data-testid="panel-body" data-title={title}>
      {children}
    </div>
  ),
  ToggleControl: ({
    label,
    checked,
    onChange,
  }: {
    label: string
    checked: boolean
    onChange: (val: boolean) => void
  }) => (
    <label>
      <input
        type="checkbox"
        checked={checked}
        onChange={e => onChange(e.target.checked)}
      />
      {label}
    </label>
  ),
  SelectControl: ({
    label,
    value,
    options,
    onChange,
  }: {
    label: string
    value: string
    options: {value: string; label: string}[]
    onChange: (val: string) => void
  }) => (
    <label>
      {label}
      <select value={value} onChange={e => onChange(e.target.value)}>
        {options.map(opt => (
          <option key={opt.value} value={opt.value}>
            {opt.label}
          </option>
        ))}
      </select>
    </label>
  ),
  Spinner: () => <div data-testid="spinner">Loading...</div>,
  FormTokenField: vi.fn(() => null),
}))

vi.mock('@wordpress/blocks', () => ({
  registerBlockType: vi.fn(),
}))
