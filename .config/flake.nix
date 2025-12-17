{
  description = "Private inputs for development purposes. These are used by the top level flake in the `dev` partition, but do not appear in consumers' lock files.";
  inputs = {
    nixpkgs-trunk.url = "github:NixOS/nixpkgs/master";
    pre-commit-hooks.url = "github:cachix/pre-commit-hooks.nix";
    # See https://github.com/ursi/get-flake/issues/4
    pre-commit-hooks.inputs.nixpkgs.follows = "";
  };

  # This flake is only used for its inputs.
  outputs = { ... }: { };
}
