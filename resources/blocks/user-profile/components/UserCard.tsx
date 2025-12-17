import {__} from '@wordpress/i18n'
import {SocialIcon} from './SocialIcon'

interface WPUser {
  id: number
  name: string
  avatar_urls: Record<string, string>
  description: string
  meta: Record<string, string>
}

interface UserCardProps {
  user: WPUser
  showAvatar: boolean
  showName: boolean
  showBio: boolean
  showLabels: boolean
  iconSize: 'small' | 'medium' | 'large'
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

export function UserCard({
  user,
  showAvatar,
  showName,
  showBio,
  showLabels,
  iconSize,
}: UserCardProps) {
  // Get social links from user meta
  const socialLinks = SOCIAL_META_KEYS
    .map((key) => ({
      key,
      url: user.meta?.[key] ?? '',
    }))
    .filter(({url}) => url !== '')

  // Get the largest available avatar
  const avatarUrl = user.avatar_urls?.['96'] ?? user.avatar_urls?.['48'] ?? ''

  return (
    <article className="wp-block-kleinweb-user-profile__card">
      {showAvatar && avatarUrl && (
        <div className="wp-block-kleinweb-user-profile__avatar">
          <img src={avatarUrl} alt={user.name} width={96} height={96} />
        </div>
      )}

      {showName && (
        <h3 className="wp-block-kleinweb-user-profile__name">{user.name}</h3>
      )}

      {showBio && user.description && (
        <p className="wp-block-kleinweb-user-profile__bio">{user.description}</p>
      )}

      {socialLinks.length > 0 && (
        <nav
          className="wp-block-kleinweb-user-profile__social"
          aria-label={__('Social links for', 'user-profile-block') + ' ' + user.name}
        >
          {socialLinks.map(({key, url}) => (
            <SocialIcon
              key={key}
              platform={key}
              url={url}
              showLabel={showLabels}
              size={iconSize}
            />
          ))}
        </nav>
      )}

      {socialLinks.length === 0 && (
        <p className="wp-block-kleinweb-user-profile__no-social">
          {__('No social links configured', 'user-profile-block')}
        </p>
      )}
    </article>
  )
}
