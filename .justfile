# SPDX-FileCopyrightText: 2022-2025 Temple University <kleinweb@temple.edu>
# SPDX-License-Identifier: GPL-3.0-or-later

###: <https://just.systems/man/en/>

set dotenv-load

mod licenses '.config/licenses'
mod release '.config/release'

prj-root := env("PRJ_ROOT")

# Display a list of available tasks as the default command
default:
  @just --choose

build:
   fd -t f '^vite\.config\.' -a -x \
     bash -c 'cd {//} && npm exec vite build'

[doc: "Check for any lint or formatting issues on project files"]
check:
  biome check {{prj-root}}
  pnpm check
  markdownlint '**/*.md' --ignore node_modules --ignore vendor --ignore wp --ignore CLAUDE.md
  reuse lint
  nix run 'github:kleinweb/beams#php-lint-project'
  composer php-cs-fixer -- check
  composer phpcs
  composer phpstan

[doc: "Check for (non-stylistic) linting issues on project files"]
lint:
  biome lint {{prj-root}}
  nix run 'github:kleinweb/beams#php-lint-project'
  composer lint

[doc: "Write *all* formatter+fixer changes to project files"]
fix:
  treefmt
  composer fix

[doc: "Write _safe_ formatter changes to project files"]
fmt:
  treefmt
  reuse annotate --skip-existing --skip-unrecognised --merge-copyrights --license GPL-3.0-or-later --copyright 'Temple University <kleinweb@temple.edu>' .
