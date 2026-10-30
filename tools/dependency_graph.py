#!/usr/bin/env python3
"""
Dependency Graph & Impact Analysis — Token Optimization Tool
Uses NetworkX to build a Directed Acyclic Graph (DAG) of the Laravel codebase:
Routes -> Controllers -> Models -> Views.
Enables instant impact queries: 'If I edit X, what else is affected?' without scanning the entire repo.
"""

import sys
import os
import re
import io
import json
import argparse

import networkx as nx

GRAPH_CACHE_FILE = os.path.join("storage", "code_dependency_graph.json")

def build_graph(root_dir=".") -> nx.DiGraph:
    G = nx.DiGraph()

    # 1. Parse Routes from routes/web.php
    routes_file = os.path.join(root_dir, "routes", "web.php")
    if os.path.isfile(routes_file):
        with open(routes_file, "r", encoding="utf-8", errors="ignore") as f:
            content = f.read()

        # Match Route::[get|post|put|delete]('uri', [Controller::class, 'method'])
        route_matches = re.findall(r"Route::(get|post|put|delete|patch|match|any)\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*\[\s*([A-Za-z0-9_]+)::class\s*,\s*['\"]([A-Za-z0-9_]+)['\"]", content)
        for method, uri, controller, action in route_matches:
            route_node = f"Route:/{uri.strip('/')}"
            ctrl_node = f"Controller:{controller}"
            G.add_node(route_node, type="route", method=method, uri=uri)
            G.add_node(ctrl_node, type="controller")
            G.add_edge(route_node, ctrl_node, relation="calls", action=action)

        # Match string syntax: 'Controller@method'
        string_matches = re.findall(r"Route::(get|post|put|delete|patch)\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]([A-Za-z0-9_]+)@([A-Za-z0-9_]+)['\"]", content)
        for method, uri, controller, action in string_matches:
            route_node = f"Route:/{uri.strip('/')}"
            ctrl_node = f"Controller:{controller}"
            G.add_node(route_node, type="route", method=method, uri=uri)
            G.add_node(ctrl_node, type="controller")
            G.add_edge(route_node, ctrl_node, relation="calls", action=action)

    # 2. Parse Controllers from app/Http/Controllers
    ctrl_dir = os.path.join(root_dir, "app", "Http", "Controllers")
    if os.path.isdir(ctrl_dir):
        for root, _, files in os.walk(ctrl_dir):
            for file in files:
                if file.endswith(".php"):
                    ctrl_name = file[:-4]
                    ctrl_node = f"Controller:{ctrl_name}"
                    G.add_node(ctrl_node, type="controller", path=os.path.join(root, file))

                    with open(os.path.join(root, file), "r", encoding="utf-8", errors="ignore") as f:
                        code = f.read()

                    # Find imported models: use App\Models\ModelName;
                    models = re.findall(r"use\s+App\\Models\\([A-Za-z0-9_]+);", code)
                    for m in set(models):
                        model_node = f"Model:{m}"
                        G.add_node(model_node, type="model")
                        G.add_edge(ctrl_node, model_node, relation="uses_model")

                    # Find returned views: view('view.name') or view("view.name")
                    views = re.findall(r"view\s*\(\s*['\"]([A-Za-z0-9_.-]+)['\"]", code)
                    for v in set(views):
                        view_node = f"View:{v}"
                        G.add_node(view_node, type="view")
                        G.add_edge(ctrl_node, view_node, relation="renders_view")

    # 3. Parse Models from app/Models
    models_dir = os.path.join(root_dir, "app", "Models")
    if os.path.isdir(models_dir):
        for root, _, files in os.walk(models_dir):
            for file in files:
                if file.endswith(".php"):
                    model_name = file[:-4]
                    model_node = f"Model:{model_name}"
                    G.add_node(model_node, type="model", path=os.path.join(root, file))

                    with open(os.path.join(root, file), "r", encoding="utf-8", errors="ignore") as f:
                        code = f.read()

                    # Find relations: hasMany(OtherModel::class), belongsTo(OtherModel::class)
                    rel_matches = re.findall(r"(hasMany|belongsTo|hasOne|belongsToMany)\s*\(\s*([A-Za-z0-9_]+)::class", code)
                    for rel_type, target_model in rel_matches:
                        target_node = f"Model:{target_model}"
                        G.add_node(target_node, type="model")
                        G.add_edge(model_node, target_node, relation=rel_type)

    return G

def save_graph(G: nx.DiGraph, filepath=GRAPH_CACHE_FILE):
    os.makedirs(os.path.dirname(filepath), exist_ok=True)
    data = nx.node_link_data(G)
    with open(filepath, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=2)

def load_or_build_graph(force_rebuild=False) -> nx.DiGraph:
    if not force_rebuild and os.path.isfile(GRAPH_CACHE_FILE):
        try:
            with open(GRAPH_CACHE_FILE, "r", encoding="utf-8") as f:
                data = json.load(f)
            return nx.node_link_graph(data)
        except Exception:
            pass
    G = build_graph()
    save_graph(G)
    return G

def query_impact(G: nx.DiGraph, query: str):
    matches = [n for n in G.nodes if query.lower() in n.lower()]
    if not matches:
        print(f"❌ No se encontró ningún nodo que coincida con '{query}'.")
        return

    for target in matches:
        print(f"\n🔍 NODO: {target} [{G.nodes[target].get('type', 'desconocido')}]")

        # Incoming edges (who depends on this node)
        predecessors = list(G.predecessors(target))
        if predecessors:
            print(f"   ⬅️  Dependen de este nodo ({len(predecessors)} entrantes):")
            for p in predecessors:
                rel = G.edges[p, target].get('relation', 'connects')
                action = G.edges[p, target].get('action', '')
                action_str = f" -> {action}()" if action else ""
                print(f"       • {p} [{rel}{action_str}]")
        else:
            print("   ⬅️  Ningún nodo entrante directo.")

        # Outgoing edges (what this node depends on)
        successors = list(G.successors(target))
        if successors:
            print(f"   ➡️  Este nodo utiliza ({len(successors)} salientes):")
            for s in successors:
                rel = G.edges[target, s].get('relation', 'connects')
                print(f"       • {s} [{rel}]")
        else:
            print("   ➡️  Ninguna dependencia saliente directa.")

def show_stats(G: nx.DiGraph):
    print("📈 Estadísticas del Grafo de Dependencias del Proyecto:")
    print(f"   - Total de Nodos: {G.number_of_nodes()}")
    print(f"   - Total de Conexiones (Edges): {G.number_of_edges()}")

    type_counts = {}
    for _, data in G.nodes(data=True):
        t = data.get("type", "otro")
        type_counts[t] = type_counts.get(t, 0) + 1

    for t, count in type_counts.items():
        print(f"   - Nodos de tipo '{t}': {count}")

    # Centrality (PageRank)
    try:
        pr = nx.pagerank(G)
        top_pr = sorted(pr.items(), key=lambda x: x[1], reverse=True)[:10]
        print("\n🏆 Top 10 Nodos Más Conectados / Críticos (PageRank):")
        for node, score in top_pr:
            print(f"   {score:.4f} | {node}")
    except Exception:
        pass

def main():
    parser = argparse.ArgumentParser(description="Grafo de Dependencias y Análisis de Impacto de Código (NetworkX)")
    parser.add_argument("--query", "-q", help="Nombre o fragmento del modelo, controlador o vista a consultar")
    parser.add_argument("--rebuild", action="store_true", help="Reconstruir el grafo desde cero")
    parser.add_argument("--stats", action="store_true", help="Mostrar estadísticas y nodos más críticos")
    args = parser.parse_args()

    G = load_or_build_graph(force_rebuild=args.rebuild)

    if args.stats:
        show_stats(G)
    elif args.query:
        query_impact(G, args.query)
    else:
        show_stats(G)
        print("\nTip: Usa 'python tools/dependency_graph.py --query <nombre>' para ver las conexiones de un nodo.")

if __name__ == "__main__":
    main()
