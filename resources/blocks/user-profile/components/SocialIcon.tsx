import {
  siInstagram,
  siX,
  siFacebook,
  siTiktok,
  siYoutube,
  siThreads,
  siBluesky,
  siSubstack,
  siMedium,
} from 'simple-icons'

type Platform =
  | 'linkedin_url'
  | 'instagram_url'
  | 'twitter_url'
  | 'facebook_url'
  | 'tiktok_url'
  | 'youtube_url'
  | 'threads_url'
  | 'bluesky_url'
  | 'substack_url'
  | 'medium_url'

interface SocialIconProps {
  platform: Platform
  url: string
  showLabel: boolean
  size: 'small' | 'medium' | 'large'
}

// LinkedIn SVG path (not in simple-icons due to trademark)
const LINKEDIN_PATH =
  'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z'

interface IconConfig {
  path: string
  label: string
}

const PLATFORM_CONFIG: Record<Platform, IconConfig> = {
  linkedin_url: {path: LINKEDIN_PATH, label: 'LinkedIn'},
  instagram_url: {path: siInstagram.path, label: 'Instagram'},
  twitter_url: {path: siX.path, label: 'X'},
  facebook_url: {path: siFacebook.path, label: 'Facebook'},
  tiktok_url: {path: siTiktok.path, label: 'TikTok'},
  youtube_url: {path: siYoutube.path, label: 'YouTube'},
  threads_url: {path: siThreads.path, label: 'Threads'},
  bluesky_url: {path: siBluesky.path, label: 'Bluesky'},
  substack_url: {path: siSubstack.path, label: 'Substack'},
  medium_url: {path: siMedium.path, label: 'Medium'},
}

export function SocialIcon({platform, url, showLabel, size}: SocialIconProps) {
  const config = PLATFORM_CONFIG[platform]
  if (!config) return null

  const {path, label} = config

  return (
    <a
      href={url}
      className={`wp-block-kleinweb-user-profile__social-link wp-block-kleinweb-user-profile__social-link--${size}`}
      target="_blank"
      rel="noopener noreferrer"
      aria-label={label}
    >
      <svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d={path} />
      </svg>
      {showLabel && (
        <span className="wp-block-kleinweb-user-profile__social-label">
          {label}
        </span>
      )}
    </a>
  )
}
