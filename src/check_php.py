#!/usr/bin/env python3
"""Syntax-check all .php files using phply (no PHP runtime needed)."""
import sys, glob, os
from phply.phpparse import make_parser
from phply import phplex

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

def check(path):
    with open(path, 'r', encoding='utf-8') as f:
        src = f.read()
    parser = make_parser()
    lexer = phplex.lexer.clone()
    parser.parse(src, lexer=lexer, tracking=True)

def main():
    files = []
    for pat in ('*.php', 'lib/*.php', 'admin/*.php', 'admin/**/*.php', 'api/**/*.php'):
        files.extend(glob.glob(os.path.join(ROOT, pat), recursive=True))
    files = sorted(set(files))
    if not files:
        print('No PHP files found under', ROOT)
        return 0
    errors = 0
    for path in files:
        rel = os.path.relpath(path, ROOT)
        try:
            check(path)
            print(f'  OK   {rel}')
        except Exception as e:
            errors += 1
            print(f'  FAIL {rel}: {e}')
    print(f'\n{len(files)} files, {errors} error(s)')
    return 1 if errors else 0

if __name__ == '__main__':
    sys.exit(main())
