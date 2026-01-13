# SPDX-FileCopyrightText: (C) 2024-2026 Temple University <kleinweb@temple.edu>
# SPDX-License-Identifier: GPL-2.0-or-later

prj-root := env("PRJ_ROOT")

wp-header-version-prefix-regexp :='(\s*)(\*\s)?Version:(\s+)'

# [private]
# [group: "release"]
# [confirm: "Release and deploy to PRODUCTION environment?"]
# [doc: "Push release and deploy to production"]
# deploy-production:
#   cog bump --auto
#   git push --follow-tags origin staging:staging

[group: "release"]
[confirm: "Prepare and tag release?"]
[doc: "Prepare and tag a release based on semantic commits"]
[no-cd]
release *ARGS='--auto':
  cog bump {{ARGS}}

[group: "release"]
[doc: "Bump version numbers across manifest files"]
[no-cd]
bump version:
  @for file in `rg '^{{wp-header-version-prefix-regexp}}([[:alnum:]\.-]+)$' \
    --files-with-matches {{prj-root}}`; do \
    just release::_bump-wp-header {{version}} $file; \
  done
  just release::_bump-php-const {{version}} {{prj-root}}/user-profile-block.php
  just release::_bump-json {{version}} {{prj-root}}/package.json

@_bump-json version file:
  jq --arg ver '{{version}}' '.version = $ver' {{file}} \
    | sponge {{file}}
  echo Bumped {{file}} version to \'{{version}}\'
  biome format --diagnostic-level=warn --write {{file}} >/dev/null

@_bump-wp-header version file:
  sed -E -i 's/^({{wp-header-version-prefix-regexp}})[[:alnum:]\.-]+$/\1{{version}}/' {{file}}
  echo Bumped {{file}} version to \'{{version}}\'

@_bump-php-const version file:
  sed -E -i "s/(const VERSION = ')[^']+(';)/\1{{version}}\2/" {{file}}
  echo Bumped {{file}} PHP const VERSION to \'{{version}}\'
