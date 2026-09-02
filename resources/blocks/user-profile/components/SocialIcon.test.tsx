// SPDX-FileCopyrightText: 2025-2026 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-2.0-or-later

import {render, screen} from '@testing-library/react'
import {describe, expect, it} from 'vitest'
import {SocialIcon} from './SocialIcon'

describe('SocialIcon', () => {
  it('renders a link with the correct URL', () => {
    render(
      <SocialIcon
        platform="linkedin_url"
        showLabel={false}
        size="medium"
        url="https://linkedin.com/in/testuser"
      />,
    )

    const link = screen.getByRole('link')
    expect(link).toHaveAttribute('href', 'https://linkedin.com/in/testuser')
  })

  it('renders with target="_blank" for security', () => {
    render(
      <SocialIcon
        platform="instagram_url"
        showLabel={false}
        size="medium"
        url="https://instagram.com/testuser"
      />,
    )

    const link = screen.getByRole('link')
    expect(link).toHaveAttribute('target', '_blank')
    expect(link).toHaveAttribute('rel', 'noopener noreferrer')
  })

  it('renders aria-label for accessibility', () => {
    render(
      <SocialIcon
        platform="twitter_url"
        showLabel={false}
        size="medium"
        url="https://x.com/testuser"
      />,
    )

    const link = screen.getByRole('link')
    expect(link).toHaveAttribute('aria-label', 'X')
  })

  it('renders SVG icon', () => {
    render(
      <SocialIcon
        platform="facebook_url"
        showLabel={false}
        size="medium"
        url="https://facebook.com/testuser"
      />,
    )

    const svg = document.querySelector('svg')
    expect(svg).toBeInTheDocument()
    expect(svg).toHaveAttribute('role', 'img')
    expect(svg).toHaveAttribute('viewBox', '0 0 24 24')
  })

  it('shows label when showLabel is true', () => {
    render(
      <SocialIcon
        platform="youtube_url"
        showLabel={true}
        size="medium"
        url="https://youtube.com/testuser"
      />,
    )

    expect(screen.getByText('YouTube')).toBeInTheDocument()
  })

  it('hides label when showLabel is false', () => {
    render(
      <SocialIcon
        platform="youtube_url"
        showLabel={false}
        size="medium"
        url="https://youtube.com/testuser"
      />,
    )

    expect(screen.queryByText('YouTube')).not.toBeInTheDocument()
  })

  it('applies size class correctly', () => {
    const {rerender} = render(
      <SocialIcon
        platform="tiktok_url"
        showLabel={false}
        size="small"
        url="https://tiktok.com/@testuser"
      />,
    )

    expect(screen.getByRole('link')).toHaveClass(
      'wp-block-kleinweb-user-profile__social-link--small',
    )

    rerender(
      <SocialIcon
        platform="tiktok_url"
        showLabel={false}
        size="large"
        url="https://tiktok.com/@testuser"
      />,
    )

    expect(screen.getByRole('link')).toHaveClass(
      'wp-block-kleinweb-user-profile__social-link--large',
    )
  })

  it('returns null for unsupported platform', () => {
    const {container} = render(
      <SocialIcon
        // @ts-expect-error - testing unsupported platform
        platform="unsupported_url"
        showLabel={false}
        size="medium"
        url="https://example.com"
      />,
    )

    expect(container.firstChild).toBeNull()
  })

  it.each([
    ['linkedin_url', 'LinkedIn'],
    ['instagram_url', 'Instagram'],
    ['twitter_url', 'X'],
    ['facebook_url', 'Facebook'],
    ['tiktok_url', 'TikTok'],
    ['youtube_url', 'YouTube'],
    ['threads_url', 'Threads'],
    ['bluesky_url', 'Bluesky'],
    ['substack_url', 'Substack'],
    ['medium_url', 'Medium'],
  ] as const)('renders correct label for %s', (platform, expectedLabel) => {
    render(
      <SocialIcon
        platform={platform}
        showLabel={true}
        size="medium"
        url="https://example.com"
      />,
    )

    expect(screen.getByText(expectedLabel)).toBeInTheDocument()
  })
})
