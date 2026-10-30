#!/usr/bin/env python3
"""
Universal Ctags & Symbol Indexer — Token Optimization Tool
Generates a standard 'tags' file and provides instant symbol lookups for classes,
methods, and functions across PHP and JS files in Project Saavedra.
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

TAGS_FILE = "tags"

def index_symbols(root_dir="."):
    tags = []
    # Header for standard ctags
    tags.append("!_TAG_FILE_FORMAT\t2\t/extended format/")
    tags.append("!_TAG_FILE_SORTED\t1\t/0=unsorted, 1=sorted, 2=foldcase/")
    tags.append("!_TAG_PROGRAM_NAME\tProjectSaavedraCtags\t//")

    php_class_regex = re.compile(r"^\s*(?:abstract\s+|final\s+)?(?:class|trait|interface)\s+([A-Za-z0-9_]+)", re.MULTILINE)
    php_method_regex = re.compile(r"^\s*(?:public|protected|private)?\s*(?:static\s+)?function\s+([A-Za-z0-9_]+)\s*\(", re.MULTILINE)
    js_func_regex = re.compile(r"^\s*(?:async\s+)?function\s+([A-Za-z0-9_]+)\s*\(", re.MULTILINE)
    js_class_regex = re.compile(r"^\s*class\s+([A-Za-z0-9_]+)", re.MULTILINE)

    scanned_files = 0
    symbols_found = []

    target_dirs = ["app", "routes", "resources/js"]

    for tdir in target_dirs:
        full_dir = os.path.join(root_dir, tdir)
        if not os.path.isdir(full_dir):
            continue

        for root, _, files in os.walk(full_dir):
            for file in files:
                ext = os.path.splitext(file)[1].lower()
                if ext not in [".php", ".js"]:
                    continue

                filepath = os.path.join(root, file).replace("\\", "/")
                scanned_files += 1

                try:
                    with open(filepath, "r", encoding="utf-8", errors="ignore") as f:
                        lines = f.readlines()
                except Exception:
                    continue

                for line_idx, line in enumerate(lines):
                    line_num = line_idx + 1
                    clean_line = line.strip()

                    # PHP Classes
                    m = php_class_regex.match(line)
                    if m:
                        name = m.group(1)
                        symbols_found.append((name, filepath, line_num, "c", f"/^{clean_line}$/"))
                        continue

                    # PHP Methods / Functions
                    m = php_method_regex.match(line)
                    if m:
                        name = m.group(1)
                        symbols_found.append((name, filepath, line_num, "m", f"/^{clean_line}$/"))
                        continue

                    # JS Functions
                    m = js_func_regex.match(line)
                    if m:
                        name = m.group(1)
                        symbols_found.append((name, filepath, line_num, "f", f"/^{clean_line}$/"))
                        continue

                    # JS Classes
                    m = js_class_regex.match(line)
                    if m:
                        name = m.group(1)
                        symbols_found.append((name, filepath, line_num, "c", f"/^{clean_line}$/"))
                        continue

    # Sort symbols alphabetically for binary search compatibility
    symbols_found.sort(key=lambda x: x[0])

    for sym, fpath, lnum, kind, ex in symbols_found:
        tags.append(f"{sym}\t{fpath}\t{lnum};\"\t{kind}\tline:{lnum}")

    with open(TAGS_FILE, "w", encoding="utf-8") as f:
        f.write("\n".join(tags) + "\n")

    return scanned_files, len(symbols_found)

def search_symbol(query: str):
    if not os.path.isfile(TAGS_FILE):
        print("⚠️ Archivo 'tags' no encontrado. Generando índice...")
        index_symbols()

    matches = []
    with open(TAGS_FILE, "r", encoding="utf-8") as f:
        for line in f:
            if line.startswith("!"):
                continue
            parts = line.strip().split("\t")
            if len(parts) >= 4:
                sym = parts[0]
                if query.lower() in sym.lower():
                    fpath = parts[1]
                    lnum = parts[2].replace(';"', '')
                    kind = parts[3]
                    kind_name = {"c": "Clase", "m": "Método", "f": "Función"}.get(kind, kind)
                    matches.append((sym, fpath, lnum, kind_name))

    if not matches:
        print(f"❌ No se encontró ningún símbolo coincidente con '{query}'.")
        return

    print(f"🎯 Coincidencias encontradas ({len(matches)}):")
    for sym, fpath, lnum, kind_name in matches:
        print(f"   • [{kind_name}] {sym} -> {fpath}:{lnum}")

def main():
    parser = argparse.ArgumentParser(description="Indexador Universal de Símbolos Ctags")
    parser.add_argument("--build", action="store_true", help="Construir/actualizar el archivo tags")
    parser.add_argument("--search", "-s", help="Buscar la definición de un símbolo (clase, método o función)")
    args = parser.parse_args()

    if args.build or not os.path.isfile(TAGS_FILE):
        files, count = index_symbols()
        print(f"✅ Índice de símbolos generado en '{TAGS_FILE}': {count} símbolos en {files} archivos.")

    if args.search:
        search_symbol(args.search)
    elif not args.build:
        search_symbol("")

if __name__ == "__main__":
    main()

