#!/usr/bin/env python3
"""
Repomap Generator — Token Optimization Tool
Generates a compact, PageRank-prioritized repository map (Aider style).
Provides the LLM agent with an executive architectural blueprint in ~600 tokens.
"""

import sys
import os
import io
import json
import argparse

import networkx as nx
from dependency_graph import load_or_build_graph

def generate_repomap(top_n=40) -> str:
    G = load_or_build_graph()

    # Calculate PageRank on undirected version to see hub importance
    G_undir = G.to_undirected()
    try:
        pr = nx.pagerank(G_undir)
    except Exception:
        pr = {node: 1.0 for node in G.nodes()}

    # Group nodes by module or type
    controllers = [n for n in G.nodes if n.startswith("Controller:")]
    models = [n for n in G.nodes if n.startswith("Model:")]
    routes = [n for n in G.nodes if n.startswith("Route:")]
    views = [n for n in G.nodes if n.startswith("View:")]

    controllers.sort(key=lambda x: pr.get(x, 0), reverse=True)
    models.sort(key=lambda x: pr.get(x, 0), reverse=True)

    lines = []
    lines.append("# 🗺️ REPOMAP EJECUTIVO — PROJECT SAAVEDRA")
    lines.append("> Mapa estructural ordenado por PageRank (relevancia e interconexión).")
    lines.append(f"> Total nodos: {len(G.nodes)} | Controladores: {len(controllers)} | Modelos: {len(models)} | Rutas: {len(routes)} | Vistas: {len(views)}\n")

    lines.append("## 🕹️ CONTROLADORES NÚCLEO (Top Hubs)")
    for c in controllers[:20]:
        cname = c.replace("Controller:", "")
        successors = list(G.successors(c))
        used_models = [s.replace("Model:", "") for s in successors if s.startswith("Model:")]
        rendered_views = [s.replace("View:", "") for s in successors if s.startswith("View:")]

        m_str = f"Models: {', '.join(used_models[:4])}" if used_models else "Models: ninguno"
        v_str = f"Views: {', '.join(rendered_views[:3])}" if rendered_views else "Views: ninguna"
        lines.append(f"- **{cname}**: {m_str} | {v_str}")

    lines.append("\n## 📦 MODELOS ELOQUENT NÚCLEO (Top Hubs)")
    for m in models[:20]:
        mname = m.replace("Model:", "")
        predecessors = list(G.predecessors(m))
        ctrl_callers = [p.replace("Controller:", "") for p in predecessors if p.startswith("Controller:")]
        c_str = f"Usado por: {', '.join(ctrl_callers[:4])}" if ctrl_callers else "Sin llamadas directas"
        lines.append(f"- **{mname}**: {c_str}")

    lines.append("\n## 🌐 RUTAS WEB PRINCIPALES")
    # Group routes by prefix
    prefixes = {}
    for r in routes:
        uri = r.replace("Route:/", "")
        pfx = uri.split("/")[0] if "/" in uri else uri
        prefixes[pfx] = prefixes.get(pfx, 0) + 1

    sorted_pfx = sorted(prefixes.items(), key=lambda x: x[1], reverse=True)[:15]
    lines.append("Prefijos de ruta más utilizados:")
    for pfx, count in sorted_pfx:
        lines.append(f"- `/{pfx}/*` ({count} endpoints)")

    output = "\n".join(lines)
    return output

def main():
    if hasattr(sys.stdout, 'reconfigure'):
        sys.stdout.reconfigure(encoding='utf-8')
    parser = argparse.ArgumentParser(description="Genera el Repomap de Project Saavedra (estilo Aider)")
    parser.add_argument("--save", action="store_true", help="Guardar en repomap.txt")
    args = parser.parse_args()

    content = generate_repomap()
    print(content)

    if args.save:
        with open("repomap.txt", "w", encoding="utf-8") as f:
            f.write(content)
        print("\n✅ Guardado en repomap.txt")

if __name__ == "__main__":
    main()
