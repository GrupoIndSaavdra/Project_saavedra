#!/usr/bin/env python3
"""
Clean Git Diff — Token Optimization Tool
Replaces bloated git diff output with an ultra-compact, noise-reduced diff (delta/gitoxide style).
Uses minimal context (-U1) and strips metadata headers, saving 50-70% of tokens when inspecting git changes.
"""

import sys
import os
import subprocess
import io
import argparse

if sys.platform == "win32":
    if hasattr(sys.stdout, 'reconfigure'):
        sys.stdout.reconfigure(encoding='utf-8')
    if hasattr(sys.stderr, 'reconfigure'):
        sys.stderr.reconfigure(encoding='utf-8')

def get_clean_diff(staged=False, commit=None, context_lines=1):
    cmd = ["git", "diff", f"-U{context_lines}", "--minimal"]
    if staged:
        cmd.append("--staged")
    if commit:
        cmd.append(commit)

    try:
        proc = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True, encoding="utf-8", errors="replace")
        raw_diff = proc.stdout
    except Exception as e:
        return f"Error ejecutando git diff: {e}"

    if not raw_diff.strip():
        return "✨ No hay cambios pendientes en git."

    # Noise reduction filter
    lines = raw_diff.splitlines()
    clean_lines = []
    current_file = ""
    added = 0
    deleted = 0

    for line in lines:
        if line.startswith("diff --git"):
            # Extract file path
            parts = line.split(" ")
            if len(parts) >= 4:
                current_file = parts[3].lstrip("b/")
                clean_lines.append(f"\n📁 Modificado: {current_file}")
            continue

        if line.startswith("index ") or line.startswith("--- ") or line.startswith("+++ "):
            # Skip redundant metadata
            continue

        if line.startswith("@@"):
            # Simplify hunk header
            clean_lines.append(f"  {line}")
            continue

        if line.startswith("+"):
            added += 1
            clean_lines.append(f"  {line}")
        elif line.startswith("-"):
            deleted += 1
            clean_lines.append(f"  {line}")
        else:
            # Context line
            clean_lines.append(f"   {line}")

    header = [
        f"📊 Resumen Git Diff Limpio:",
        f"   - Líneas agregadas: +{added} | Líneas eliminadas: -{deleted}",
        f"   - Contexto mínimo unificado (-U{context_lines})\n"
    ]

    return "\n".join(header + clean_lines)

def main():
    parser = argparse.ArgumentParser(description="Git Diff Limpio y Atómico para Consumo de LLMs")
    parser.add_argument("--staged", action="store_true", help="Ver cambios en staging (--staged)")
    parser.add_argument("--commit", "-c", help="Comparar contra un commit específico")
    parser.add_argument("--context", "-u", type=int, default=1, help="Líneas de contexto (por defecto 1)")
    args = parser.parse_args()

    output = get_clean_diff(staged=args.staged, commit=args.commit, context_lines=args.context)
    print(output)

if __name__ == "__main__":
    main()

