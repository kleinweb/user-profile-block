# SPDX-FileCopyrightText: (C) 2025 Temple University <kleinweb@temple.edu>
# SPDX-License-Identifier: GPL-2.0-or-later

{
  perSystem =
    {
      config,
      inputs',
      pkgs,
      ...
    }:
    let
      checksPkgs = [
        config.pre-commit.settings.hooks.markdownlint.package
        config.pre-commit.settings.hooks.yamllint.package
        inputs'.nixpkgs-trunk.legacyPackages.biome
        inputs'.beams.packages.php-lint
        pkgs.dotenv-linter
        pkgs.reuse
      ];

      buildsPkgs = [
        pkgs.turbo
      ];

      deployPkgs = [
        pkgs.rsync
      ];

      formatterPkgs = [
        pkgs.dos2unix
        pkgs.nixfmt # pkgs.nixfmt-rfc-style via overlay
        pkgs.nodePackages.prettier
        pkgs.taplo
        pkgs.treefmt # pkgs.treefmt2 via overlay
      ];

      releasePkgs = [
        pkgs.cocogitto
      ];

      php = pkgs.php83;

      commonPkgs = [
        pkgs.curl
        pkgs.fd
        pkgs.gnused
        pkgs.jq
        inputs'.nixpkgs-trunk.legacyPackages.biome
        pkgs.moreutils
        pkgs.ripgrep
        pkgs.nodejs
        php
        php.packages.composer
        pkgs.pnpm
        pkgs.xq-xml
        pkgs.wp-cli
      ];

      developmentPkgs =
        commonPkgs ++ checksPkgs ++ formatterPkgs ++ buildsPkgs ++ deployPkgs ++ releasePkgs;

      playwrightShellHook = ''
        export PLAYWRIGHT_BROWSERS_PATH=${pkgs.playwright-driver.browsers}
      '';
    in
    {
      devShells.default = pkgs.mkShellNoCC {
        shellHook = ''
          : "''${PRJ_BIN_HOME:=''${PRJ_PATH:-''${PRJ_ROOT}/.bin}}"

          export PRJ_BIN_HOME

          ${config.pre-commit.installationScript}

          ${playwrightShellHook}
        '';
        nativeBuildInputs = developmentPkgs ++ [
          # TODO: Remove when available upstream: https://github.com/NixOS/nixpkgs/pull/344503
          inputs'.beams.packages.ddev

          pkgs.playwright-driver.browsers
        ];
      };

      devShells.ci = pkgs.mkShellNoCC {
        nativeBuildInputs = commonPkgs ++ checksPkgs ++ buildsPkgs ++ deployPkgs;
      };
    };
}
