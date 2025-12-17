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
  it('renders user name when showName is true', () => {
    render(
      <UserCard
        user={createMockUser()}
        showAvatar={false}
        showName={true}
        showBio={false}
        showLabels={false}
        iconSize="medium"
      />,
    )

    expect(screen.getByRole('heading', {name: 'Test User'})).toBeInTheDocument()
  })

  it('hides user name when showName is false', () => {
    render(
      <UserCard
        user={createMockUser()}
        showAvatar={false}
        showName={false}
        showBio={false}
        showLabels={false}
        iconSize="medium"
      />,
    )

    expect(screen.queryByRole('heading')).not.toBeInTheDocument()
  })

  it('renders avatar when showAvatar is true', () => {
    render(
      <UserCard
        user={createMockUser()}
        showAvatar={true}
        showName={false}
        showBio={false}
        showLabels={false}
        iconSize="medium"
      />,
    )

    const img = screen.getByRole('img', {name: 'Test User'})
    expect(img).toHaveAttribute('src', 'https://example.com/avatar-96.jpg')
  })

  it('hides avatar when showAvatar is false', () => {
    render(
      <UserCard
        user={createMockUser()}
        showAvatar={false}
        showName={true}
        showBio={false}
        showLabels={false}
        iconSize="medium"
      />,
    )

    expect(screen.queryByRole('img', {name: 'Test User'})).not.toBeInTheDocument()
  })

  it('renders bio when showBio is true and user has description', () => {
    render(
      <UserCard
        user={createMockUser({description: 'My cool bio'})}
        showAvatar={false}
        showName={false}
        showBio={true}
        showLabels={false}
        iconSize="medium"
      />,
    )

    expect(screen.getByText('My cool bio')).toBeInTheDocument()
  })

  it('hides bio when showBio is false', () => {
    render(
      <UserCard
        user={createMockUser({description: 'Hidden bio'})}
        showAvatar={false}
        showName={false}
        showBio={false}
        showLabels={false}
        iconSize="medium"
      />,
    )

    expect(screen.queryByText('Hidden bio')).not.toBeInTheDocument()
  })

  it('hides bio when user has no description', () => {
    const {container} = render(
      <UserCard
        user={createMockUser({description: ''})}
        showAvatar={false}
        showName={false}
        showBio={true}
        showLabels={false}
        iconSize="medium"
      />,
    )

    expect(
      container.querySelector('.wp-block-kleinweb-user-profile__bio'),
    ).not.toBeInTheDocument()
  })

  it('renders social links navigation', () => {
    render(
      <UserCard
        user={createMockUser()}
        showAvatar={false}
        showName={false}
        showBio={false}
        showLabels={false}
        iconSize="medium"
      />,
    )

    expect(screen.getByRole('navigation')).toBeInTheDocument()
    expect(screen.getByRole('navigation')).toHaveAttribute(
      'aria-label',
      'Social links for Test User',
    )
  })

  it('renders multiple social links', () => {
    render(
      <UserCard
        user={createMockUser()}
        showAvatar={false}
        showName={false}
        showBio={false}
        showLabels={false}
        iconSize="medium"
      />,
    )

    const links = screen.getAllByRole('link')
    expect(links).toHaveLength(2)
    expect(links[0]).toHaveAttribute('href', 'https://linkedin.com/in/testuser')
    expect(links[1]).toHaveAttribute('href', 'https://twitter.com/testuser')
  })

  it('shows "No social links" message when user has no social links', () => {
    render(
      <UserCard
        user={createMockUser({meta: {}})}
        showAvatar={false}
        showName={true}
        showBio={false}
        showLabels={false}
        iconSize="medium"
      />,
    )

    expect(screen.getByText('No social links configured')).toBeInTheDocument()
  })

  it('passes showLabels prop to SocialIcon', () => {
    render(
      <UserCard
        user={createMockUser()}
        showAvatar={false}
        showName={false}
        showBio={false}
        showLabels={true}
        iconSize="medium"
      />,
    )

    // When showLabels is true, labels should be visible
    expect(screen.getByText('LinkedIn')).toBeInTheDocument()
    expect(screen.getByText('X')).toBeInTheDocument()
  })

  it('passes iconSize prop to SocialIcon', () => {
    render(
      <UserCard
        user={createMockUser()}
        showAvatar={false}
        showName={false}
        showBio={false}
        showLabels={false}
        iconSize="large"
      />,
    )

    const links = screen.getAllByRole('link')
    links.forEach((link) => {
      expect(link).toHaveClass('wp-block-kleinweb-user-profile__social-link--large')
    })
  })

  it('renders as an article element', () => {
    render(
      <UserCard
        user={createMockUser()}
        showAvatar={false}
        showName={true}
        showBio={false}
        showLabels={false}
        iconSize="medium"
      />,
    )

    expect(screen.getByRole('article')).toBeInTheDocument()
  })

  it('falls back to 48px avatar when 96px is not available', () => {
    render(
      <UserCard
        user={createMockUser({
          avatar_urls: {
            '48': 'https://example.com/avatar-48.jpg',
          },
        })}
        showAvatar={true}
        showName={false}
        showBio={false}
        showLabels={false}
        iconSize="medium"
      />,
    )

    const img = screen.getByRole('img', {name: 'Test User'})
    expect(img).toHaveAttribute('src', 'https://example.com/avatar-48.jpg')
  })

  it('hides avatar when no avatar URLs available', () => {
    const {container} = render(
      <UserCard
        user={createMockUser({avatar_urls: {}})}
        showAvatar={true}
        showName={true}
        showBio={false}
        showLabels={false}
        iconSize="medium"
      />,
    )

    // Avatar img should not exist (SVGs have role="img" but are not the avatar)
    expect(
      container.querySelector('.wp-block-kleinweb-user-profile__avatar img'),
    ).not.toBeInTheDocument()
  })
})
