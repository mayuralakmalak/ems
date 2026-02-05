#!/usr/bin/env python3
"""
Extract repository text files into JSONL for fine-tuning and a plain-text file for embeddings.
Usage:
  python3 tools/extract_repo_for_training.py --root /path/to/repo --out training_dataset.jsonl --emb embeddings_input.txt
"""
import argparse
import os
import io
import json

SKIP_DIRS = {"vendor", "node_modules", ".git", "storage", "public/storage", "bootstrap/cache"}
ALLOWED_EXT = {'.php', '.md', '.txt', '.json', '.js', '.ts', '.vue', '.html', '.css', '.scss', '.py', '.yml', '.yaml'}
MAX_BYTES = 1024 * 1024  # 1MB per file


def is_text_file(path):
    _, ext = os.path.splitext(path)
    return ext.lower() in ALLOWED_EXT


def walk_and_collect(root):
    for dirpath, dirnames, filenames in os.walk(root):
        # normalize and skip
        rel = os.path.relpath(dirpath, root)
        parts = rel.split(os.sep)
        if any(p in SKIP_DIRS for p in parts):
            continue
        for fname in filenames:
            fpath = os.path.join(dirpath, fname)
            relpath = os.path.relpath(fpath, root)
            if is_text_file(fpath):
                yield relpath, fpath


def read_text(path):
    try:
        with io.open(path, 'r', encoding='utf-8', errors='replace') as f:
            data = f.read(MAX_BYTES)
            return data
    except Exception:
        return None


def main():
    p = argparse.ArgumentParser()
    p.add_argument('--root', default='.', help='Repository root')
    p.add_argument('--out', default='training_dataset.jsonl', help='Output JSONL path')
    p.add_argument('--emb', default='embeddings_input.txt', help='Plain text file for embeddings')
    args = p.parse_args()

    root = os.path.abspath(args.root)
    out_path = os.path.abspath(args.out)
    emb_path = os.path.abspath(args.emb)

    count = 0
    with io.open(out_path, 'w', encoding='utf-8') as out_f, io.open(emb_path, 'w', encoding='utf-8') as emb_f:
        for relpath, fullpath in walk_and_collect(root):
            text = read_text(fullpath)
            if not text:
                continue
            # basic cleaning: remove very long binary-like lines
            text = '\n'.join([ln for ln in text.splitlines() if len(ln) < 10000])
            obj = {'path': relpath, 'text': text}
            out_f.write(json.dumps(obj, ensure_ascii=False) + "\n")
            emb_f.write(text + "\n---\n")
            count += 1

    print(f"Wrote {count} files to {out_path} and embeddings to {emb_path}")

if __name__ == '__main__':
    main()
