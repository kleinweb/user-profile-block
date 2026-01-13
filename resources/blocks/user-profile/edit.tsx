// SPDX-FileCopyrightText: 2025-2026 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-2.0-or-later

import {InspectorControls, useBlockProps} from '@wordpress/block-editor'
import type {BlockEditProps} from '@wordpress/blocks'
import {
  FormTokenField,
  PanelBody,
  Spinner,
  ToggleControl,
} from '@wordpress/components'
import {useSelect} from '@wordpress/data'
import {__} from '@wordpress/i18n'
import {UserCard} from './components/UserCard'

interface Attributes {
  selectedUserIds: number[]
  usePostAuthor: boolean
}

interface WPUser {
  id: number
  name: string
  avatar_urls: Record<string, string>
  description: string
  meta: Record<string, string>
}

export function Edit({
  attributes,
  setAttributes,
  context,
}: BlockEditProps<Attributes>) {
  const {selectedUserIds, usePostAuthor} = attributes

  const blockProps = useBlockProps({
    className: 'wp-block-kleinweb-user-profile',
  })

  const postId = context.postId as number | undefined

  // Fetch author-capable users for selection
  const {users, isLoadingUsers} = useSelect(select => {
    const {getUsers, isResolving} = select('core') as {
      getUsers: (query: Record<string, unknown>) => WPUser[] | null
      isResolving: (selector: string, args: unknown[]) => boolean
    }

    return {
      users: getUsers({
        who: 'authors',
        per_page: 100,
      }),
      isLoadingUsers: isResolving('getUsers', [
        {who: 'authors', per_page: 100},
      ]),
    }
  }, [])

  // Fetch post author(s)
  const {postAuthors, isLoadingAuthors} = useSelect(
    select => {
      if (!usePostAuthor || !postId) {
        return {postAuthors: [], isLoadingAuthors: false}
      }

      const {getEntityRecord, isResolving} = select('core') as {
        getEntityRecord: (
          kind: string,
          name: string,
          id: number,
        ) => {author?: number} | null
        isResolving: (selector: string, args: unknown[]) => boolean
      }
      const {getUsers} = select('core') as {
        getUsers: (query: Record<string, unknown>) => WPUser[] | null
      }

      const post = getEntityRecord('postType', 'post', postId)
      if (!post?.author) {
        return {postAuthors: [], isLoadingAuthors: false}
      }

      const authorUsers = getUsers({include: [post.author]})

      return {
        postAuthors: authorUsers ?? [],
        isLoadingAuthors: isResolving('getEntityRecord', [
          'postType',
          'post',
          postId,
        ]),
      }
    },
    [usePostAuthor, postId],
  )

  // Fetch specifically selected users
  const {selectedUsers, isLoadingSelected} = useSelect(
    select => {
      if (selectedUserIds.length === 0) {
        return {selectedUsers: [], isLoadingSelected: false}
      }

      const {getUsers, isResolving} = select('core') as {
        getUsers: (query: Record<string, unknown>) => WPUser[] | null
        isResolving: (selector: string, args: unknown[]) => boolean
      }

      return {
        selectedUsers: getUsers({include: selectedUserIds}) ?? [],
        isLoadingSelected: isResolving('getUsers', [
          {include: selectedUserIds},
        ]),
      }
    },
    [selectedUserIds],
  )

  // Combine all users to display (deduped)
  const displayUsers = (() => {
    const seen = new Set<number>()
    const result: WPUser[] = []

    if (usePostAuthor && postAuthors) {
      for (const user of postAuthors) {
        if (!seen.has(user.id)) {
          seen.add(user.id)
          result.push(user)
        }
      }
    }

    if (selectedUsers) {
      for (const user of selectedUsers) {
        if (!seen.has(user.id)) {
          seen.add(user.id)
          result.push(user)
        }
      }
    }

    return result
  })()

  // User selection helpers
  const userOptions = users?.map(user => user.name) ?? []
  const userNameToId = new Map(users?.map(user => [user.name, user.id]) ?? [])
  const userIdToName = new Map(users?.map(user => [user.id, user.name]) ?? [])

  const selectedUserNames = selectedUserIds
    .map(id => userIdToName.get(id))
    .filter((name): name is string => name !== undefined)

  const handleUserSelectionChange = (
    tokens: Array<string | {value: string}>,
  ) => {
    const names = tokens.map(t => (typeof t === 'string' ? t : t.value))
    const ids = names
      .map(name => userNameToId.get(name))
      .filter((id): id is number => id !== undefined)
    setAttributes({selectedUserIds: ids})
  }

  const isLoading = isLoadingUsers || isLoadingAuthors || isLoadingSelected

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('User Selection', 'user-profile-block')}>
          <ToggleControl
            label={__('Show post author', 'user-profile-block')}
            help={__(
              'Display the current post author(s) automatically.',
              'user-profile-block',
            )}
            checked={usePostAuthor}
            onChange={value => setAttributes({usePostAuthor: value})}
          />

          <FormTokenField
            label={__('Additional users', 'user-profile-block')}
            value={selectedUserNames}
            suggestions={userOptions}
            onChange={handleUserSelectionChange}
            __experimentalExpandOnFocus
            __experimentalShowHowTo={false}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        {isLoading ? (
          <div className="wp-block-kleinweb-user-profile__loading">
            <Spinner />
          </div>
        ) : displayUsers.length === 0 ? (
          <div className="wp-block-kleinweb-user-profile__empty">
            <p>
              {__(
                'This is a placeholder for the User Profile block.',
                'user-profile-block',
              )}
            </p>
          </div>
        ) : (
          displayUsers.map(user => <UserCard key={user.id} user={user} />)
        )}
      </div>
    </>
  )
}
