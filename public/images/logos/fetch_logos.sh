#!/usr/bin/env bash
set -euo pipefail

INPUT_FILE="${1:-pasted.txt}"
OUTDIR="${2:-./logos}"
BASE_URL="https://images.scrydex.com/pokemon"

if [[ ! -f "$INPUT_FILE" ]]; then
  echo "File not found: $INPUT_FILE"
  exit 1
fi

mkdir -p "$OUTDIR"

# Estrae codici tipo "me1", "sv8pt5", ecc. dal pattern .../pokemon/<code>-logo/logo
codes=$(grep -oE 'https://images\.scrydex\.com/pokemon/[^"]+-logo/logo' "$INPUT_FILE" \
  | sed -E 's#https://images\.scrydex\.com/pokemon/([^/]+)-logo/logo#\1#g' \
  | sort -u)

if [[ -z "${codes// }" ]]; then
  echo "No logo URLs found in $INPUT_FILE"
  exit 1
fi

ext_from_content_type () {
  local ct="$1"
  case "$ct" in
    image/png*) echo "png" ;;
    image/webp*) echo "webp" ;;
    image/svg+xml*) echo "svg" ;;
    image/jpeg*) echo "jpg" ;;
    image/jpg*) echo "jpg" ;;
    *) echo "" ;;
  esac
}

echo "$codes" | while IFS= read -r code; do
  [[ -z "$code" ]] && continue

  url="${BASE_URL}/${code}-logo/logo"

  # Headers (seguo redirect se presenti) e prendo l’ultimo content-type
  ct="$(curl -sIL "$url" | awk -F': ' 'tolower($1)=="content-type"{print tolower($2)}' | tail -n 1 | tr -d '\r')"
  ext="$(ext_from_content_type "$ct")"

  if [[ -z "$ext" ]]; then
    echo "⚠️  Skip $code (unknown Content-Type: ${ct:-none})"
    continue
  fi

  outfile="${OUTDIR}/${code}-logo.${ext}"

  # Se esiste già, non riscarico
  if [[ -f "$outfile" ]]; then
    echo "✔️  Exists: $outfile"
    continue
  fi

  echo "⬇️  $code -> $outfile"
  curl -fsSL "$url" -o "$outfile"
done

echo "✅ Done. Saved logos in: $OUTDIR"
