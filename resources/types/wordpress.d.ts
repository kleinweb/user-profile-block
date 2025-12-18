// SPDX-FileCopyrightText: 2025 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-3.0-or-later

// Type declarations for WordPress packages without bundled types

declare module '@wordpress/date' {
  export function format(
    formatString: string,
    date: Date | string | number,
  ): string
  export function dateI18n(
    formatString: string,
    date?: Date | string | number,
  ): string
  export function getDate(date?: Date | string | number): Date
}

// Augment @wordpress/data exports
declare module '@wordpress/data' {
  export function useSelect<T>(
    selector: (
      select: <S extends string>(
        store: S,
      ) => Record<string, (...args: unknown[]) => unknown>,
    ) => T,
    deps?: unknown[],
  ): T

  export function select<S extends string>(
    store: S,
  ): Record<string, (...args: unknown[]) => unknown>
}
