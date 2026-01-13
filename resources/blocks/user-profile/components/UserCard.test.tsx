// SPDX-FileCopyrightText: 2025-2026 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-2.0-or-later

import {render, screen} from '@testing-library/react'
import {describe, expect, it} from 'vitest'
import {UserCard} from './UserCard'

const createMockUser = (overrides = {}) => ({
  id: 1,
  name: 'Test User',
  avatar_urls: {
    '48': 'https://example.com/avatar-48.jpg',
    '96': 'https://example.com/avatar-96.jpg',
  },
  description: 'A test bio',
  meta: {
    linkedin_url: 'https://linkedin.com/in/testuser',
    twitter_url: 'https://twitter.com/testuser',
  },
  ...overrides,
})

describe('UserCard', () => {
  it('renders user name in heading', () => {
    render(<UserCard user={createMockUser()} />)

    expect(screen.getByRole('heading', {name: /Test User/})).toBeInTheDocument()
  })

  it('renders author name as link when linkToAuthorPage is true', () => {
    render(<UserCard user={createMockUser()} linkToAuthorPage={true} />)

    const heading = screen.getByRole('heading', {name: /Test User/})
    const link = heading.querySelector('a')
    expect(link).toBeInTheDocument()
    expect(link).toHaveAttribute('href', '/?author=1')
  })

  it('renders author name as plain text when linkToAuthorPage is false', () => {
    render(<UserCard user={createMockUser()} linkToAuthorPage={false} />)

    const heading = screen.getByRole('heading', {name: /Test User/})
    const link = heading.querySelector('a')
    expect(link).not.toBeInTheDocument()
  })

  it('defaults to linking author name when linkToAuthorPage is not specified', () => {
    render(<UserCard user={createMockUser()} />)

    const heading = screen.getByRole('heading', {name: /Test User/})
    const link = heading.querySelector('a')
    expect(link).toBeInTheDocument()
  })

  it('renders Connect with text', () => {
    render(<UserCard user={createMockUser()} />)

    expect(screen.getByText(/Connect with/)).toBeInTheDocument()
  })

  it('renders social links navigation', () => {
    render(<UserCard user={createMockUser()} />)

    expect(screen.getByRole('navigation')).toBeInTheDocument()
    expect(screen.getByRole('navigation')).toHaveAttribute(
      'aria-label',
      'Social links for Test User',
    )
  })

  it('renders multiple social links', () => {
    render(<UserCard user={createMockUser()} linkToAuthorPage={false} />)

    const links = screen.getAllByRole('link')
    expect(links).toHaveLength(2)
    expect(links[0]).toHaveAttribute('href', 'https://linkedin.com/in/testuser')
    expect(links[1]).toHaveAttribute('href', 'https://twitter.com/testuser')
  })

  it('returns null when user has no social links', () => {
    const {container} = render(<UserCard user={createMockUser({meta: {}})} />)

    expect(container.firstChild).toBeNull()
  })

  it('renders as an article element', () => {
    render(<UserCard user={createMockUser()} />)

    expect(screen.getByRole('article')).toBeInTheDocument()
  })

  it('renders social icons with SVG', () => {
    const {container} = render(<UserCard user={createMockUser()} />)

    const svgs = container.querySelectorAll('svg')
    expect(svgs.length).toBeGreaterThan(0)
  })

  it('only renders links for platforms with URLs', () => {
    render(
      <UserCard
        user={createMockUser({
          meta: {
            linkedin_url: 'https://linkedin.com/in/testuser',
            twitter_url: '',
            facebook_url: '',
          },
        })}
        linkToAuthorPage={false}
      />,
    )

    const links = screen.getAllByRole('link')
    expect(links).toHaveLength(1)
    expect(links[0]).toHaveAttribute('href', 'https://linkedin.com/in/testuser')
  })
})
