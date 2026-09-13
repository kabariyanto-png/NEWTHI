#!/usr/bin/env bash
# Membuat paket thi-glass.zip yang siap diunggah ke WordPress.
set -euo pipefail

cd "$(dirname "$0")/.."
OUT="thi-glass.zip"

rm -f "$OUT"
zip -r -q "$OUT" thi-glass \
  -x 'thi-glass/build.sh' \
  -x '*/.DS_Store' \
  -x '*/__MACOSX/*' \
  -x '*/.git/*'

echo "Paket dibuat: $(pwd)/$OUT"
unzip -l "$OUT" | tail -3
