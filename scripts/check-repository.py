#!/usr/bin/env python3
"""Inspect the Git index without printing credential values or loading the app."""

from __future__ import annotations

import fnmatch
import io
from pathlib import Path, PurePosixPath
import re
import subprocess
import sys


ROOT = Path(__file__).resolve().parents[1]
RUNTIME_ROOTS = {"cache", "tmp", "return", "userfls"}
LOCAL_ROOTS = {"public_html", ".idea", ".vscode", ".local", "backups", "node_modules"}
DATA_PATTERNS = (
    "*.sql", "*.sql.gz", "*.sql.bz2", "*.dump", "*.sqlite", "*.sqlite3",
    "*.log", "*.log.*", "*.zip", "*.tar", "*.tar.gz", "*.tgz", "*.7z",
    "*.rar", "*.bak", "*.backup", "*.orig", "*.pem", "*.key", "*.p12", "*.pfx",
    "*.pyc", "*.iml", ".ds_store", "thumbs.db", "desktop.ini",
)
DSN = re.compile(rb"\b(?:mysql|mysqli)://[^\s'\"<>/:]+:[^\s'\"<>]*@[^\s'\"<>/]+/[^\s'\"<>]*")
VENDOR_EXAMPLES = {
    b"mysqli://user:password@localhost/dbname",
    b"mysqli://user:pass@localhost/dbname",
}
LOCAL_EXAMPLE = b"mysqli://nwd_local:change-me-local@127.0.0.1/nwdelectricity_local"
SECRET_PATTERNS = (
    ("GitHub token", re.compile(rb"\bgh[pousr]_[A-Za-z0-9]{36}\b")),
    ("GitHub fine-grained token", re.compile(rb"\bgithub_pat_[A-Za-z0-9_]{40,}\b")),
    ("AWS access key", re.compile(rb"\b(?:AKIA|ASIA)[A-Z0-9]{16}\b")),
    ("private key", re.compile(
        rb"-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----\s*[A-Za-z0-9+/=]{20,}"
    )),
)


def git(*args: str, input_bytes: bytes | None = None) -> bytes:
    result = subprocess.run(
        ["git", *args], cwd=ROOT, input=input_bytes,
        stdout=subprocess.PIPE, stderr=subprocess.PIPE,
    )
    if result.returncode:
        raise RuntimeError("Git command failed; check that this is a Git repository.")
    return result.stdout


def path_problem(name: str) -> str | None:
    path = PurePosixPath(name)
    parts = path.parts
    first = parts[0].casefold()
    filename = path.name.casefold()
    if path.is_absolute() or ".." in parts or "\n" in name or "\r" in name:
        return "unsupported path"
    if first in LOCAL_ROOTS or "__pycache__" in parts or "node_modules" in parts:
        return "local/runtime directory"
    if first in RUNTIME_ROOTS and not (
        len(parts) == 2 and filename in {".gitkeep", ".htaccess"}
    ):
        return "runtime or uploaded content"
    if len(parts) == 1:
        if filename == "index.html":
            return "generated homepage cache"
        if filename == "config.php" or (
            fnmatch.fnmatchcase(filename, "config.*.php") and filename != "config.example.php"
        ):
            return "private site configuration"
    if filename == ".env" or (filename.startswith(".env.") and filename != ".env.example"):
        return "environment configuration"
    if any(fnmatch.fnmatchcase(filename, pattern) for pattern in DATA_PATTERNS):
        return "backup, database, key, log, or editor state"
    return None


def allowed_dsn(name: str, value: bytes) -> bool:
    if name == "scripts/check-repository.py":
        return value in VENDOR_EXAMPLES or value == LOCAL_EXAMPLE
    if name in {"includes/demo.php", "modules/config/admin/config.admin.php"}:
        return value in VENDOR_EXAMPLES
    return name == "config.example.php" and value == LOCAL_EXAMPLE


def main() -> int:
    records = []
    errors = []
    seen = {}
    for entry in git("ls-files", "--stage", "-z").split(b"\0"):
        if not entry:
            continue
        metadata, raw_path = entry.split(b"\t", 1)
        mode, sha, stage = metadata.decode("ascii").split()
        name = raw_path.decode("utf-8")
        if stage != "0":
            errors.append((name, "unresolved merge conflict"))
            continue
        if mode not in {"100644", "100755"}:
            errors.append((name, "symlink or submodule requires explicit review"))
            continue
        folded = name.casefold()
        if folded in seen and seen[folded] != name:
            errors.append((name, "case conflict on Windows"))
        seen[folded] = name
        problem = path_problem(name)
        if problem:
            errors.append((name, problem))
        records.append((name, sha))
    if not records:
        raise RuntimeError("No tracked files; stage the intended source files first.")

    request = b"\n".join(sha.encode("ascii") for _, sha in records) + b"\n"
    blobs = io.BytesIO(git("cat-file", "--batch", input_bytes=request))
    for name, sha in records:
        header = blobs.readline().decode("ascii").strip().split()
        if len(header) != 3 or header[0] != sha or header[1] != "blob":
            raise RuntimeError("Unexpected Git object; inspection stopped.")
        size = int(header[2])
        data = blobs.read(size)
        if len(data) != size or blobs.read(1) != b"\n":
            raise RuntimeError("Incomplete Git object; inspection stopped.")
        if size >= 100 * 1024 * 1024:
            errors.append((name, "file reaches GitHub's regular-file size limit"))
        if name.lower().endswith(".php") and data.startswith(b"\xef\xbb\xbf"):
            errors.append((name, "UTF-8 BOM in PHP"))
        if b"\0" in data[:4096]:
            continue
        if any(not allowed_dsn(name, match.group()) for match in DSN.finditer(data)):
            errors.append((name, "literal database credentials; use private configuration"))
        for label, pattern in SECRET_PATTERNS:
            if pattern.search(data):
                errors.append((name, label))

    for name, reason in errors:
        print(f"ERROR: {name}: {reason}")
    if errors:
        print(f"REPOSITORY_CHECK_FAILED: {len(errors)} issue(s); values are not displayed.")
        return 1
    print(f"REPOSITORY_CHECK_OK: {len(records)} tracked files inspected from the Git index.")
    return 0


if __name__ == "__main__":
    try:
        sys.exit(main())
    except (OSError, RuntimeError, UnicodeError, ValueError) as exc:
        print(f"REPOSITORY_CHECK_ERROR: {exc}", file=sys.stderr)
        sys.exit(2)
