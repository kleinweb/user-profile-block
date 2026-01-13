# SPDX-FileCopyrightText: 2024-2026 Temple University <kleinweb@temple.edu>
#
# SPDX-License-Identifier: GPL-2.0-or-later

(builtins.getFlake ("git+file://" + toString ./.)).devShells.${builtins.currentSystem}.default
