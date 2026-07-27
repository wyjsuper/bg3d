#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
北港3D 部署包发布脚本
===================
将当前 beigang-php 源码打包为 beigang-php-deploy.zip，写入版本号，
提交到本地 git 并打 tag，再把 zip 作为 GitHub Release 附件上传。

前置：
  1. 安装 git。
  2. 在 GitHub 建一个空仓库（如 yourname/beigang-php）。
  3. 生成一个有 `repo` 权限的 Personal Access Token（PAT）。

用法：
  # 仅本地打包 + git 提交（不推 GitHub，适合先验证）：
  python publish.py

  # 指定版本号并发布到 GitHub：
  python publish.py --version v2026.07.27 --repo yourname/beigang-php --token ghp_xxx

  # 或从环境变量读取（推荐，避免 token 出现在命令行历史）：
  set GITHUB_REPO=yourname/beigang-php
  set GITHUB_TOKEN=ghp_xxx
  python publish.py --version v2026.07.27

说明：
  - zip 输出到上一级目录（与 make_zip.py 一致）：../beigang-php-deploy.zip
  - 版本号写入 beigang-php/version.json，并打进 zip，供后台「在线更新」显示当前版本
  - 仓库根目录为 beigang-php/，运行时数据（uploads/、data/auth.json、
    data/content.json、data/backups/、version.json）已在 .gitignore 中排除，
    不会进入 git 历史（部署 zip 仍会包含 seed 数据用于首次部署）
"""
import os
import sys
import json
import zipfile
from pathlib import Path
import subprocess
import argparse
import datetime
import urllib.request
import urllib.error

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.dirname(HERE)                      # Ai-Web
ZIP_NAME = "beigang-php-deploy.zip"
OUT_ZIP = os.path.join(ROOT, ZIP_NAME)
VERSION_FILE = os.path.join(HERE, "version.json")

EXCLUDE_DIRS = {".git", "uploads", "preview", "__pycache__", "src"}
EXCLUDE_FILES = {".DS_Store", "Thumbs.db"}
EXCLUDE_EXT = {".zip"}


def stamp_version():
    now = datetime.datetime.now()
    return "v" + now.strftime("%Y%m%d-%H%M"), now.strftime("%Y-%m-%d %H:%M")


def write_version(version, date, repo):
    data = {"version": version, "date": date, "repo": repo}
    with open(VERSION_FILE, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
    return data


def pack():
    n = 0
    with zipfile.ZipFile(OUT_ZIP, "w", zipfile.ZIP_DEFLATED) as zf:
        for p in sorted(Path(HERE).rglob("*")):
            if not p.is_file():
                continue
            rel = p.relative_to(HERE).as_posix()
            parts = Path(rel).parts
            if parts[0] in EXCLUDE_DIRS:
                continue
            if p.name in EXCLUDE_FILES:
                continue
            if rel == "data/auth.json":
                continue
            if p.suffix in EXCLUDE_EXT:
                continue
            if any(part.startswith(".") and part != ".htaccess" for part in parts):
                continue
            zf.write(p, rel)
            n += 1
    return n


def run_git(args, check=True):
    r = subprocess.run(["git"] + args, cwd=HERE, capture_output=True, text=True)
    if check and r.returncode != 0:
        print("  ✗ git 命令失败:", " ".join(args))
        print(r.stderr.strip())
        sys.exit(1)
    return r


def ensure_git():
    if not (Path(HERE) / ".git").exists():
        print("· 初始化 git 仓库（beigang-php/）")
        run_git(["init"])
        # 设置本地 user（若全局未配置）
        cfg = run_git(["config", "user.email"], check=False)
        if cfg.returncode != 0:
            run_git(["config", "user.email", "deploy@beigang.local"])
            run_git(["config", "user.name", "Beigang Deploy"])


def git_commit_and_tag(version, message):
    run_git(["add", "-A"])
    # 若没有任何变化则跳过提交
    status = run_git(["status", "--porcelain"], check=False)
    if not status.stdout.strip():
        print("· 无代码变动，跳过提交")
    else:
        run_git(["commit", "-m", message])
    # 若 tag 已存在则先删（覆盖式发布）
    exist = run_git(["tag", "-l", version], check=False)
    if exist.stdout.strip():
        run_git(["tag", "-d", version])
    run_git(["tag", version])
    print("· 已打 tag:", version)


def gh_api(method, url, token, data=None, binary=None, content_type=None):
    headers = {
        "Authorization": "Bearer " + token,
        "User-Agent": "beigang-publish",
        "Accept": "application/vnd.github+json",
    }
    body = None
    if binary is not None:
        body = binary
        headers["Content-Type"] = content_type or "application/octet-stream"
    elif data is not None:
        body = json.dumps(data).encode("utf-8")
        headers["Content-Type"] = "application/json"
    req = urllib.request.Request(url, data=body, headers=headers, method=method)
    try:
        with urllib.request.urlopen(req, timeout=120) as resp:
            return resp.getcode(), resp.read().decode("utf-8", "replace")
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode("utf-8", "replace")
    except Exception as e:  # noqa
        return 0, str(e)


def github_release(repo, version, message, token):
    api_url = f"https://api.github.com/repos/{repo}/releases"
    print("· 创建 GitHub Release:", version)
    code, body = gh_api("POST", api_url, token, data={
        "tag_name": version,
        "name": f"Release {version}",
        "body": message,
        "draft": False,
        "prerelease": False,
    })
    if code not in (200, 201):
        print("  ✗ 创建 Release 失败 (HTTP %s):" % code)
        print(body[:500])
        return False
    rel = json.loads(body)
    upload_url = rel.get("upload_url", "").split("{")[0]
    if not upload_url:
        print("  ✗ 未获取到 upload_url")
        return False
    up = upload_url + "?name=" + ZIP_NAME
    with open(OUT_ZIP, "rb") as f:
        bin_data = f.read()
    print("· 上传附件 %s (%.1f MB)" % (ZIP_NAME, len(bin_data) / 1024 / 1024))
    code2, body2 = gh_api("POST", up, token, binary=bin_data, content_type="application/zip")
    if code2 not in (200, 201):
        print("  ✗ 上传附件失败 (HTTP %s):" % code2)
        print(body2[:500])
        return False
    print("  ✓ 已发布: https://github.com/%s/releases/tag/%s" % (repo, version))
    return True


def main():
    ap = argparse.ArgumentParser(description="北港3D 部署包发布工具")
    ap.add_argument("--version", help="版本号，如 v2026.07.27（默认按当前时间生成）")
    ap.add_argument("--repo", help="GitHub 仓库 owner/repo（也可设环境变量 GITHUB_REPO）")
    ap.add_argument("--token", help="GitHub PAT（也可设环境变量 GITHUB_TOKEN）")
    ap.add_argument("--message", help="Release / commit 说明", default="自动发布部署包")
    args = ap.parse_args()

    version = args.version
    date = ""
    if not version:
        version, date = stamp_version()
    else:
        date = datetime.datetime.now().strftime("%Y-%m-%d %H:%M")

    repo = args.repo or os.environ.get("GITHUB_REPO", "")
    token = args.token or os.environ.get("GITHUB_TOKEN", "")

    print("== 北港3D 部署包发布 ==")
    print("· 版本号:", version)

    # 1. 写版本
    write_version(version, date, repo)
    print("· 已写入 version.json")

    # 2. 打包
    n = pack()
    print("· 已打包 %d 个文件 -> %s (%.1f MB)" % (
        n, OUT_ZIP, os.path.getsize(OUT_ZIP) / 1024 / 1024))

    # 3. git 提交 + tag
    ensure_git()
    git_commit_and_tag(version, args.message)

    # 4. 推送（若已配置 remote）
    rem = run_git(["remote", "get-url", "origin"], check=False)
    if rem.returncode == 0 and rem.stdout.strip():
        print("· 推送到 origin（含 tag）…")
        run_git(["push", "origin", "HEAD"])
        run_git(["push", "origin", "--tags"])
    else:
        print("· 未配置 git remote，跳过推送。如需推送请执行：")
        print('    git remote add origin https://github.com/%s.git' % (repo or "<owner/repo>"))
        print("    git push -u origin --tags HEAD")

    # 5. GitHub Release
    if repo and token:
        ok = github_release(repo, version, args.message, token)
        if not ok:
            print("发布到 GitHub 失败，但本地 zip 与 git 已完成。")
    else:
        print("· 未提供 repo/token，跳过 GitHub Release 上传。")
        print("  配置方法：在 lib/config.php 设置 BG_GITHUB_REPO，并用 --token 运行本脚本即可自动上传。")
        print("  或在后台「在线更新」中配置镜像源。")

    print("\n✓ 完成。部署包：", OUT_ZIP)


if __name__ == "__main__":
    main()
