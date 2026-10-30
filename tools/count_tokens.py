#!/usr/bin/env python3
"""
Token Budget Counter — Token Optimization Tool
Uses tiktoken (cl100k_base) to calculate exact token usage before injecting code or prompts into the LLM context.
"""

import sys
import os
import argparse
import io

if sys.platform == "win32":
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding="utf-8", errors="replace")
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding="utf-8", errors="replace")

import tiktoken

def get_token_count(text: str, model: str = "cl100k_base") -> int:
    try:
        enc = tiktoken.get_encoding(model)
    except Exception:
        enc = tiktoken.get_encoding("cl100k_base")
    return len(enc.encode(text))

def analyze_file(path: str):
    if not os.path.isfile(path):
        print(f"Error: No existe el archivo {path}", file=sys.stderr)
        return

    with open(path, "r", encoding="utf-8", errors="replace") as f:
        content = f.read()

    tokens = get_token_count(content)
    chars = len(content)
    lines = len(content.splitlines())

    print(f"📄 Archivo: {path}")
    print(f"   - Tokens: {tokens:,}")
    print(f"   - Líneas: {lines:,}")
    print(f"   - Caracteres: {chars:,}")
    print(f"   - % Ventana (128k): {(tokens / 128000) * 100:.2f}%")

    if tokens > 5000:
        print("   ⚠️ AVISO: Archivo pesado (>5,000 tokens). Considera usar 'python tools/ast_skeleton.py' para extraer solo firmas.")

def analyze_dir(path: str, top_n: int = 15):
    enc = tiktoken.get_encoding("cl100k_base")
    results = []
    total_tokens = 0

    for root, _, files in os.walk(path):
        if any(x in root for x in ["vendor", "node_modules", ".git", "storage"]):
            continue
        for file in files:
            ext = os.path.splitext(file)[1].lower()
            if ext in [".php", ".js", ".css", ".blade.php", ".json", ".md", ".py"]:
                fpath = os.path.join(root, file)
                try:
                    with open(fpath, "r", encoding="utf-8", errors="ignore") as f:
                        t = len(enc.encode(f.read()))
                        results.append((fpath, t))
                        total_tokens += t
                except Exception:
                    pass

    results.sort(key=lambda x: x[1], reverse=True)

    print(f"📊 Directorio analizado: {path}")
    print(f"   - Total archivos: {len(results):,}")
    print(f"   - Total tokens: {total_tokens:,}\n")
    print(f"🏆 Top {top_n} archivos más pesados:")
    for fpath, t in results[:top_n]:
        print(f"   {t:>7,} tokens | {fpath}")

def main():
    parser = argparse.ArgumentParser(description="Calcula el presupuesto de tokens con tiktoken.")
    parser.add_argument("target", nargs="?", default=None, help="Archivo o directorio a analizar")
    parser.add_argument("--top", type=int, default=15, help="Número de archivos top en directorio")
    args = parser.parse_args()

    if not args.target:
        # Check stdin
        if not sys.stdin.isatty():
            content = sys.stdin.read()
            tokens = get_token_count(content)
            print(f"Tokens en entrada estándar (stdin): {tokens:,}")
            return
        else:
            parser.print_help()
            return

    if os.path.isdir(args.target):
        analyze_dir(args.target, top_n=args.top)
    elif os.path.isfile(args.target):
        analyze_file(args.target)
    else:
        print(f"Destino no encontrado: {args.target}", file=sys.stderr)

if __name__ == "__main__":
    main()
