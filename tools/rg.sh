#!/usr/bin/env bash
if command -v rg &> /dev/null; then
    rg "$@"
else
    # Fallback to grep if ripgrep is not installed yet
    grep -rn "$@"
fi

