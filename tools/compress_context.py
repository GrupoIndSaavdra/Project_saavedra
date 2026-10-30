#!/usr/bin/env python3
"""
Context & Prompt Compressor (LLMLingua Heuristics) — Token Optimization Tool
Compresses verbose logs, stack traces, and code payloads before injecting them into the LLM context.
Reduces token usage by 50% - 75% without losing crucial debugging information.
"""

import sys
import os
import re
import io
import argparse

if sys.platform == "win32":
    if hasattr(sys.stdout, 'reconfigure'):
        sys.stdout.reconfigure(encoding='utf-8')
    if hasattr(sys.stderr, 'reconfigure'):
        sys.stderr.reconfigure(encoding='utf-8')

def compress_laravel_log(text: str) -> str:
    lines = text.splitlines()
    compressed = []
    in_vendor_trace = False
    vendor_count = 0

    for line in lines:
        # Check if line is a stack trace frame
        is_trace_frame = bool(re.match(r"^#\d+\s+", line.strip()))
        is_vendor = ("vendor/" in line) or ("vendor\\" in line)

        if is_trace_frame and is_vendor:
            vendor_count += 1
            in_vendor_trace = True
            continue
        else:
            if in_vendor_trace:
                compressed.append(f"   [... {vendor_count} marcos de vendor/laravel colapsados ...]")
                in_vendor_trace = False
                vendor_count = 0

            # Keep application trace frames or general log messages
            compressed.append(line)

    if in_vendor_trace:
        compressed.append(f"   [... {vendor_count} marcos de vendor/laravel colapsados ...]")

    return "\n".join(compressed)

def compress_code(code: str, ext: str = ".php") -> str:
    # Remove block comments /* ... */
    clean = re.sub(r"/\*.*?\*/", "", code, flags=re.DOTALL)
    # Remove single line comments
    clean = re.sub(r"^\s*//.*$", "", clean, flags=re.MULTILINE)
    clean = re.sub(r"^\s*#.*$", "", clean, flags=re.MULTILINE)
    # Collapse multiple blank lines
    clean = re.sub(r"\n\s*\n+", "\n\n", clean)
    return clean.strip()

def compress_general(text: str) -> str:
    # General whitespace and repetitive line compression
    lines = text.splitlines()
    out = []
    prev_line = None
    rep_count = 0

    for l in lines:
        s = l.strip()
        if s == prev_line and s != "":
            rep_count += 1
            if rep_count == 1:
                out.append(f"   [... línea repetida {s[:40]} ...]")
            continue
        else:
            rep_count = 0
            prev_line = s
            out.append(l)

    return "\n".join(out)

def main():
    parser = argparse.ArgumentParser(description="Compresor Inteligente de Prompts y Logs (Estilo LLMLingua)")
    parser.add_argument("target", nargs="?", help="Archivo a comprimir (o lee de stdin)")
    parser.add_argument("--log", action="store_true", help="Modo optimizado para Laravel logs")
    parser.add_argument("--code", action="store_true", help="Modo optimizado para código (remueve comentarios/espacios)")
    args = parser.parse_args()

    content = ""
    if args.target and os.path.isfile(args.target):
        with open(args.target, "r", encoding="utf-8", errors="replace") as f:
            content = f.read()
    elif not sys.stdin.isatty():
        content = sys.stdin.read()
    else:
        # Default test or help
        print("Uso: python tools/compress_context.py <archivo> [--log | --code]", file=sys.stderr)
        sys.exit(1)

    orig_len = len(content)
    if args.log or "storage/logs" in (args.target or ""):
        res = compress_laravel_log(content)
    elif args.code:
        res = compress_code(content)
    else:
        res = compress_general(content)

    new_len = len(res)
    savings = (1 - (new_len / max(1, orig_len))) * 100

    print(f"// === TEXTO COMPRIMIDO ({savings:.1f}% de ahorro en caracteres/tokens) ===")
    print(res)

if __name__ == "__main__":
    main()

