#!/usr/bin/env python3
"""Extract an All-in-One WP Migration .wpress package."""

import argparse
import os
import sys

HEADER_SIZE = 4377
NAME_LEN = 255
SIZE_LEN = 14
MTIME_LEN = 12


def extract(src: str, dest: str, skip_prefixes: tuple[str, ...]) -> None:
    os.makedirs(dest, exist_ok=True)
    file_size = os.path.getsize(src)
    extracted = 0
    skipped = 0
    with open(src, "rb") as handle:
        while True:
            pos = handle.tell()
            header = handle.read(HEADER_SIZE)
            if len(header) < HEADER_SIZE:
                raise SystemExit(f"archivo incompleto en offset {pos}")
            name = header[0:NAME_LEN].split(b"\0", 1)[0].decode("utf-8", "replace")
            size_raw = header[NAME_LEN : NAME_LEN + SIZE_LEN].split(b"\0", 1)[0].decode().strip()
            rel_dir = header[NAME_LEN + SIZE_LEN + MTIME_LEN :].split(b"\0", 1)[0].decode("utf-8", "replace")
            if not name and not size_raw:
                break
            try:
                size = int(size_raw)
            except ValueError as exc:
                raise SystemExit(f"tamano invalido para {name}: {size_raw}") from exc
            rel = os.path.join(rel_dir, name) if rel_dir else name
            rel = rel.replace("\\", "/").lstrip("./")
            if any(rel == prefix or rel.startswith(prefix.rstrip("/") + "/") for prefix in skip_prefixes):
                handle.seek(size, os.SEEK_CUR)
                skipped += 1
                continue
            if pos + HEADER_SIZE + size > file_size:
                raise SystemExit(f"archivo truncado: {rel}")
            target = os.path.join(dest, rel)
            if not os.path.abspath(target).startswith(os.path.abspath(dest) + os.sep):
                raise SystemExit(f"ruta fuera del destino: {rel}")
            os.makedirs(os.path.dirname(target), exist_ok=True)
            remaining = size
            with open(target, "wb") as out:
                while remaining:
                    chunk = handle.read(min(1024 * 1024, remaining))
                    if not chunk:
                        raise SystemExit(f"lectura corta: {rel}")
                    out.write(chunk)
                    remaining -= len(chunk)
            extracted += 1
            if extracted % 500 == 0:
                print(f"extraidos {extracted}", flush=True)
    print(f"listo: {extracted} archivos, omitidos {skipped}")


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("src")
    parser.add_argument("dest")
    parser.add_argument("--skip", action="append", default=[])
    args = parser.parse_args()
    extract(args.src, args.dest, tuple(args.skip))


if __name__ == "__main__":
    main()
