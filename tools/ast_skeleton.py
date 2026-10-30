#!/usr/bin/env python3
"""
AST Skeleton Extractor — Token Optimization Tool
Uses Tree-sitter (PHP, JS, Python) to extract clean class & function signatures
without method bodies, saving up to 85% of tokens.
"""

import sys
import os
import argparse

def extract_php_skeleton(code_bytes):
    try:
        import tree_sitter_php as tsphp
        from tree_sitter import Language, Parser

        php_lang = Language(tsphp.language_php())
        parser = Parser(php_lang)
        tree = parser.parse(code_bytes)

        lines = code_bytes.splitlines()
        output = []

        def get_text(node):
            if not node:
                return ""
            return code_bytes[node.start_byte:node.end_byte].decode('utf-8', errors='replace')

        def walk(node, indent=0):
            prefix = "  " * indent
            ntype = node.type

            if ntype == "namespace_definition":
                output.append(f"{get_text(node).strip()};")
                return

            if ntype == "namespace_use_declaration":
                output.append(f"{get_text(node).strip()};")
                return

            if ntype in ("class_declaration", "interface_declaration", "trait_declaration"):
                name = get_text(node.child_by_field_name("name"))
                base_clause = node.child_by_field_name("base_clause")
                interfaces_clause = node.child_by_field_name("interfaces_clause")
                extends_txt = f" {get_text(base_clause)}" if base_clause else ""
                implements_txt = f" {get_text(interfaces_clause)}" if interfaces_clause else ""
                start_l = node.start_point[0] + 1
                end_l = node.end_point[0] + 1

                kind = ntype.replace("_declaration", "")
                output.append(f"\n{prefix}{kind} {name}{extends_txt}{implements_txt} {{ // L{start_l}-L{end_l}")

                body = node.child_by_field_name("body")
                if body:
                    for member in body.children:
                        walk(member, indent + 1)
                output.append(f"{prefix}}}")
                return

            if ntype == "use_declaration": # trait use
                output.append(f"{prefix}{get_text(node).strip()}")
                return

            if ntype == "property_declaration":
                txt = get_text(node).strip()
                # If too long, truncate
                if len(txt) > 80:
                    txt = txt[:77] + "..."
                output.append(f"{prefix}{txt}")
                return

            if ntype == "method_declaration":
                visibility = "public"
                for m in node.children:
                    if m.type == "visibility_modifier":
                        visibility = get_text(m)
                    if m.type == "static_modifier":
                        visibility += " static"

                name_node = node.child_by_field_name("name")
                params_node = node.child_by_field_name("parameters")
                return_type = node.child_by_field_name("return_type")

                name = get_text(name_node)
                params = get_text(params_node).replace("\n", " ").replace("  ", " ")
                ret = f": {get_text(return_type)}" if return_type else ""
                start_l = node.start_point[0] + 1
                end_l = node.end_point[0] + 1

                output.append(f"{prefix}{visibility} function {name}{params}{ret} {{ ... }} // L{start_l}-L{end_l}")
                return

            if ntype == "function_definition":
                name = get_text(node.child_by_field_name("name"))
                params = get_text(node.child_by_field_name("parameters")).replace("\n", " ")
                start_l = node.start_point[0] + 1
                end_l = node.end_point[0] + 1
                output.append(f"{prefix}function {name}{params} {{ ... }} // L{start_l}-L{end_l}")
                return

            for child in node.children:
                walk(child, indent)

        walk(tree.root_node)
        return "\n".join(output)
    except Exception as e:
        return f"// Error parsing PHP AST: {e}"


def extract_js_skeleton(code_bytes):
    try:
        import tree_sitter_javascript as tsjs
        from tree_sitter import Language, Parser

        js_lang = Language(tsjs.language_javascript())
        parser = Parser(js_lang)
        tree = parser.parse(code_bytes)

        lines = code_bytes.splitlines()
        output = []

        def get_text(node):
            if not node:
                return ""
            return code_bytes[node.start_byte:node.end_byte].decode('utf-8', errors='replace')

        def walk(node, indent=0):
            prefix = "  " * indent
            ntype = node.type

            if ntype in ("import_statement", "export_statement"):
                txt = get_text(node).strip().splitlines()[0]
                output.append(f"{txt}")
                return

            if ntype == "class_declaration":
                name = get_text(node.child_by_field_name("name"))
                start_l = node.start_point[0] + 1
                end_l = node.end_point[0] + 1
                output.append(f"\n{prefix}class {name} {{ // L{start_l}-L{end_l}")
                body = node.child_by_field_name("body")
                if body:
                    for m in body.children:
                        walk(m, indent + 1)
                output.append(f"{prefix}}}")
                return

            if ntype in ("method_definition", "function_declaration"):
                name = get_text(node.child_by_field_name("name"))
                params = get_text(node.child_by_field_name("parameters")).replace("\n", " ")
                start_l = node.start_point[0] + 1
                end_l = node.end_point[0] + 1
                async_kw = "async " if "async" in get_text(node)[:15] else ""
                output.append(f"{prefix}{async_kw}function {name}{params} {{ ... }} // L{start_l}-L{end_l}")
                return

            for child in node.children:
                walk(child, indent)

        walk(tree.root_node)
        return "\n".join(output)
    except Exception as e:
        return f"// Error parsing JS AST: {e}"


def main():
    parser = argparse.ArgumentParser(description="Extrae esqueletos estructurales (AST) de archivos de código.")
    parser.add_argument("file", help="Ruta al archivo PHP, JS o Python")
    args = parser.parse_args()

    if not os.path.isfile(args.file):
        print(f"Error: Archivo no encontrado: {args.file}", file=sys.stderr)
        sys.exit(1)

    with open(args.file, "rb") as f:
        content = f.read()

    orig_lines = len(content.splitlines())
    ext = os.path.splitext(args.file)[1].lower()

    if ext in (".php", ".inc"):
        res = extract_php_skeleton(content)
    elif ext in (".js", ".mjs", ".ts"):
        res = extract_js_skeleton(content)
    else:
        print(f"Extensión no soportada aún para AST: {ext}", file=sys.stderr)
        sys.exit(1)

    res_lines = len(res.splitlines())
    reduction = 100 - (res_lines / max(1, orig_lines) * 100)

    print(f"// === ESQUELETO ESTRUCTURAL AST: {args.file} ===")
    print(f"// Líneas originales: {orig_lines} -> Líneas esqueleto: {res_lines} ({reduction:.1f}% de ahorro en tokens)")
    print(res)

if __name__ == "__main__":
    main()

