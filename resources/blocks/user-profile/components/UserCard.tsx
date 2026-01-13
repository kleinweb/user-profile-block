// SPDX-FileCopyrightText: 2025-2026 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-2.0-or-later

import {__} from '@wordpress/i18n'
import {SocialIcon} from './SocialIcon'

interface WPUser {
  id: number
  name: string
  avatar_urls: Record<string, string>
  description: string
  meta: Record<string, string>
}

export interface UserCardProps {
  user: WPUser
}

const SOCIAL_META_KEYS = [
  'linkedin_url',
  'instagram_url',
  'twitter_url',
  'facebook_url',
  'tiktok_url',
  'youtube_url',
  'threads_url',
  'bluesky_url',
  'substack_url',
  'medium_url',
] as const

export function UserCard({user}: UserCardProps) {
  const socialLinks = SOCIAL_META_KEYS.map(key => ({
    key,
    url: user.meta?.[key] ?? '',
  })).filter(({url}) => url !== '')

  if (socialLinks.length === 0) {
    return null
  }

  return (
    <article className="wp-block-kleinweb-user-profile__card">
      <h3 className="wp-block-kleinweb-user-profile__name">
        {__('Connect with', 'user-profile-block')} {user.name}
      </h3>

      <nav
        className="wp-block-kleinweb-user-profile__social"
        aria-label={`${__('Social links for', 'user-profile-block')} ${user.name}`}
      >
        {socialLinks.map(({key, url}) => (
          <SocialIcon key={key} platform={key} url={url} />
        ))}
      </nav>
    </article>
  )
}
