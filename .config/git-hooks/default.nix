# SPDX-FileCopyrightText: 2024-2025 Temple University <kleinweb@temple.edu>
# SPDX-License-Identifier: GPL-3.0-or-later
{
  perSystem =
    { inputs', ... }:
    {
      pre-commit.settings = {
        hooks = {
          check-xml.enable = true;
          composer-lint = {
            enable = true;
            entry = "composer lint --";
            types = [
              "file"
              "php"
            ];
            stages = [ "pre-commit" ];
          };
          markdownlint.enable = true;
          markdownlint.excludes = [
            # Auto-generated
            "CHANGELOG.md"

            # Auto-exported from private/migrate/migration.org
            "private/migrate/migrate.md"
            "private/migrate/README.md"
          ];
          php-lint = {
            enable = true;
            description = "Check PHP files for syntax errors";
            package = inputs'.beams.packages.php-lint;
            entry = "php-lint";
            types = [
              "file"
              "php"
            ];
            # Other PHP linters will likely fail when there are syntax errors.
            fail_fast = true;
          };
          treefmt.enable = true;
          yamllint.enable = true;
          yamllint.excludes = [
            "^\.copier-answers\.yml$"
            "^\.ddev/.+$"
          ];
        };
        default_stages = [
          "pre-commit"
          "pre-push"
        ];

        excludes = [ ];
      };
    };
}
