#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
北港3D 部署包发布脚本
===================
将当前 beigang-php 源码打包为 beigang-php-deploy.zip，写入版本号与变更日志，
提交到本地 git 并打 tag，再把 zip 作为 GitHub Release 附件上传，并把本次
「修改信息」自动写入 Release 说明、仓库 CHANGELOG.md 与 version.json。

每次打包都会自动从 git 提交记录生成变更日志（上一版本 tag 以来的提交 + 变更文件），
无需手工撰写说明（仍可用 --message 追加一句话摘要）。

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
  python publish.py --version v2026.07.27 --message "修复登录验证码"

说明：
  - zip 输出到上一级目录（与 make_zip.py 一致）： ../beigang-php-deploy.zip
  - 版本号写入 beigang-php/version.json，并打进 zip，供后台「在线更新」显示当前版本
  - version.json 额外包含 changes(提交列表) / body(变更日志全文) / commit(短哈希)
  - 仓库根目录 CHANGELOG.md 会追加本次条目（最新在最前）
  - 仓库根目录为 beigang-php/，运行时数据（uploads/、videos/、data/auth.json、
    data/content.json、data/backups/、version.json）已在 .gitignore 中排除，
    不会进入 git 历史；部署 zip 也不会包含这些运行时数据，避免覆盖线上用户上传的内容。
"""
import os
import sys
import re
import json
import zipfile
import shutil
from pathlib import Path
import subprocess
import argparse
import datetime
import urllib.request
import urllib.error
import contextlib

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.dirname(HERE)                      # Ai-Web
ZIP_NAME = "beigang-php-deploy.zip"
OUT_ZIP = os.path.join(ROOT, ZIP_NAME)
VERSION_FILE = os.path.join(HERE, "version.json")
CHANGELOG_FILE = os.path.join(HERE, "CHANGELOG.md")

EXCLUDE_DIRS = {".git", "uploads", "preview", "__pycache__", "src", "videos"}
EXCLUDE_FILES = {".DS_Store", "Thumbs.db"}
EXCLUDE_EXT = {".zip"}          # gif 重新启用：网格短循环预览 videos/g/*.gif 需打进部署包

# 形如 v2026.07.30 / v2026.07.30.3 的正式版本号（用于定位上一版本 tag）
VERSION_RE = re.compile(r'^v\d{4}\.\d{2}\.\d{2}(\.\d+)?$')


def git_run(args, check=True):
    r = subprocess.run(["git"] + list(args), cwd=HERE, capture_output=True, text=True)
    if check and r.returncode != 0:
        print("  ✗ git 命令失败:", " ".join(args))
        print(r.stderr.strip())
        sys.exit(1)
    return r


def stamp_version():
    now = datetime.datetime.now()
    return "v" + now.strftime("%Y%m%d-%H%M"), now.strftime("%Y-%m-%d %H:%M")


def read_prev_version():
    if os.path.exists(VERSION_FILE):
        try:
            d = json.load(open(VERSION_FILE, encoding="utf-8"))
            return d.get("version", "")
        except Exception:
            return ""
    return ""


def find_prev_ref(prev_version):
    """找到作为变更基准的上一版本 tag（精确匹配优先，否则取 HEAD 可达的最新版本 tag）。"""
    prev_version = (prev_version or "").strip()
    if prev_version:
        r = git_run(["tag", "-l", prev_version], check=False)
        if r.returncode == 0 and r.stdout.strip():
            return prev_version
    r = git_run(["tag", "--list"], check=False)
    tags = [t for t in r.stdout.split() if VERSION_RE.match(t)]
    if not tags:
        return ""
    r = git_run(["for-each-ref", "--format=%(refname:short) %(committerdate:unix)",
                 "--sort=-committerdate"] + ["refs/tags/" + t for t in tags], check=False)
    for line in r.stdout.splitlines():
        t = line.split()[0]
        a = git_run(["merge-base", "--is-ancestor", t, "HEAD"], check=False)
        if a.returncode == 0:
            return t
    return ""


def git_changelog(prev_ref):
    if prev_ref:
        r = git_run(["log", f"{prev_ref}..HEAD", "--no-merges",
                     "--pretty=format:- %s (%h)"], check=False)
    else:
        r = git_run(["log", "--no-merges", "--pretty=format:- %s (%h)"], check=False)
    return [l for l in r.stdout.splitlines() if l.strip()]


def git_changed_files(prev_ref):
    if not prev_ref:
        return []
    r = git_run(["diff", f"{prev_ref}..HEAD", "--name-status", "--no-merges"], check=False)
    out = []
    for line in r.stdout.splitlines():
        line = line.rstrip("\n")
        if not line.strip():
            continue
        out.append(line)
    return out


def git_head_sha():
    r = git_run(["rev-parse", "--short", "HEAD"], check=False)
    return r.stdout.strip()


def build_body(version, date, repo, headline, changelog, files_changed):
    b = []
    b.append(f"## {version}  ·  {date}")
    if headline:
        b.append("")
        b.append(headline)
    if changelog:
        b.append("")
        b.append("### 提交记录")
        b.extend(changelog)
    if files_changed:
        b.append("")
        b.append("### 变更文件")
        b.append("```")
        b.extend(files_changed)
        b.append("```")
    b.append("")
    b.append("---")
    b.append(f"_自动生成 · 仓库 `{repo}`_")
    return "\n".join(b)


def write_version(version, date, repo, commit, changes, body):
    data = {"version": version, "date": date, "repo": repo,
            "commit": commit, "changes": changes, "body": body}
    with open(VERSION_FILE, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
    return data


def write_changelog(version, date, headline, changelog, files_changed):
    parts = [f"## {version} — {date}\n"]
    if headline:
        parts.append(headline + "\n")
    if changelog:
        parts.append("\n### 提交记录\n" + "\n".join(changelog) + "\n")
    if files_changed:
        parts.append("\n### 变更文件\n```\n" + "\n".join(files_changed) + "\n```\n")
    parts.append("\n---\n")
    entry = "".join(parts)
    header = "# 更新日志\n\n"
    if os.path.exists(CHANGELOG_FILE):
        old = open(CHANGELOG_FILE, encoding="utf-8").read()
        if old.startswith(header):
            old = old[len(header):]
        new = header + entry + old
    else:
        new = header + entry
    with open(CHANGELOG_FILE, "w", encoding="utf-8") as f:
        f.write(new)


def pack():
    n = 0
    full = os.environ.get("BG_PACK_FULL") == "1"
    exclude_dirs = set(EXCLUDE_DIRS)
    if full:
        exclude_dirs.discard("videos")          # 完整包：包含用户上传的视频
    with zipfile.ZipFile(OUT_ZIP, "w", zipfile.ZIP_DEFLATED) as zf:
        for p in sorted(Path(HERE).rglob("*")):
            if not p.is_file():
                continue
            rel = p.relative_to(HERE).as_posix()
            parts = Path(rel).parts
            if parts[0] in exclude_dirs or parts[0].startswith("tmp"):
                continue
            if p.name in EXCLUDE_FILES:
                continue
            if rel == "data/auth.json":
                continue                                # 凭证永不打进包
            if rel == "data/content.json" and not full:
                continue                                # 完整包：包含内容库
            # 手机版 mp4 预览已弃用（网格改用 GIF），不再打进部署包
            if rel.startswith("videos/m/"):
                continue
            # gif 默认不打进包（旧产物/案例封面等非必需），但 videos/g/ 下由 render.php 输出的网格预览 GIF 必须包含
            if p.suffix == ".gif" and not rel.startswith("videos/g/"):
                continue
            if p.suffix in EXCLUDE_EXT:
                continue
            if p.name.startswith("_echotest"):
                continue                                # 本地调试探针文件，不打进部署包
            if any(part.startswith(".") and part != ".htaccess" for part in parts):
                continue
            zf.write(p, rel)
            n += 1
    return n


def ensure_git():
    if not (Path(HERE) / ".git").exists():
        print("· 初始化 git 仓库（beigang-php/）")
        git_run(["init"])
        cfg = git_run(["config", "user.email"], check=False)
        if cfg.returncode != 0:
            git_run(["config", "user.email", "deploy@beigang.local"])
            git_run(["config", "user.name", "Beigang Deploy"])


def commit_and_tag(version, message):
    git_run(["add", "-A"])
    # 若没有可提交变更（例如仅修改了被 .gitignore 排除的 data/content.json / videos/），
    # 跳过 commit 以免脚本因 "nothing to commit" 退出，但仍继续打包并打 tag。
    st = git_run(["status", "--porcelain"], check=False)
    if st.stdout.strip():
        git_run(["commit", "-m", message], check=False)
    else:
        print("· 无 git 可提交变更（如仅改动被忽略的内容库），跳过 commit，继续打包")
    # 若 tag 已存在则先删（覆盖式发布）
    exist = git_run(["tag", "-l", version], check=False)
    if exist.stdout.strip():
        git_run(["tag", "-d", version], check=False)
    git_run(["tag", version], check=False)
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


def upload_asset(upload_url, token, zip_path):
    with open(zip_path, "rb") as f:
        bin_data = f.read()
    code, body = gh_api("POST", upload_url, token, binary=bin_data, content_type="application/zip")
    if code in (200, 201):
        return True, body
    # 沙箱环境下 uploads.github.com 对 Python 可能被拦截，回退 curl
    print("  · urllib 上传失败（HTTP %s），回退 curl…" % code)
    if shutil.which("curl"):
        cmd = ["curl", "-s", "-X", "POST", upload_url,
               "-H", "Authorization: Bearer " + token,
               "-H", "Accept: application/vnd.github+json",
               "-H", "Content-Type: application/zip",
               "--data-binary", "@" + zip_path]
        p = subprocess.run(cmd, capture_output=True, text=True)
        if p.returncode == 0 and '"id"' in p.stdout:
            return True, p.stdout
        return False, p.stderr
    return False, body


def github_release(repo, version, body, token):
    api_url = f"https://api.github.com/repos/{repo}/releases"
    print("· 创建 GitHub Release:", version)
    code, resp = gh_api("POST", api_url, token, data={
        "tag_name": version,
        "name": f"Release {version}",
        "body": body,
        "draft": False,
        "prerelease": False,
    })
    if code not in (200, 201):
        print("  ✗ 创建 Release 失败 (HTTP %s):" % code)
        print(resp[:500])
        return False
    rel = json.loads(resp)
    upload_url = rel.get("upload_url", "").split("{")[0]
    if not upload_url:
        print("  ✗ 未获取到 upload_url")
        return False
    up = upload_url + "?name=" + ZIP_NAME
    print("· 上传附件 %s (%.1f MB)" % (ZIP_NAME, os.path.getsize(OUT_ZIP) / 1024 / 1024))
    ok, _ = upload_asset(up, token, OUT_ZIP)
    if not ok:
        print("  ✗ 上传附件失败")
        return False
    print("  ✓ 已发布: https://github.com/%s/releases/tag/%s" % (repo, version))
    return True


@contextlib.contextmanager
def git_remote_token(token):
    """临时把 github.com 的 remote 带上 token，便于在 CI / 沙箱无交互推送。"""
    rem = git_run(["remote", "get-url", "origin"], check=False)
    url = rem.stdout.strip() if rem.returncode == 0 else ""
    restored = None
    if token and "github.com" in url:
        nu = re.sub(r'https://', 'https://' + token + '@', url)
        if nu != url:
            git_run(["remote", "set-url", "origin", nu])
            restored = url
    try:
        yield
    finally:
        if restored:
            git_run(["remote", "set-url", "origin", restored])


def push(token):
    rem = git_run(["remote", "get-url", "origin"], check=False)
    if rem.returncode != 0 or not rem.stdout.strip():
        print("· 未配置 git remote，跳过推送。")
        return
    with git_remote_token(token):
        print("· 推送到 origin（含 tag）…")
        r1 = git_run(["push", "origin", "HEAD"], check=False)
        if r1.returncode != 0:
            print("  ⚠ push 失败：", r1.stderr.strip() or "网络不可达")
            print("    请在本机手动执行： git push origin HEAD && git push origin --tags")
            return
        r2 = git_run(["push", "origin", "--tags"], check=False)
        if r2.returncode != 0:
            print("  ⚠ push tags 失败：", r2.stderr.strip() or "网络不可达")


def main():
    ap = argparse.ArgumentParser(description="北港3D 部署包发布工具（自动生成变更日志）")
    ap.add_argument("--version", help="版本号，如 v2026.07.27（默认按当前时间生成）")
    ap.add_argument("--repo", help="GitHub 仓库 owner/repo（也可设环境变量 GITHUB_REPO）")
    ap.add_argument("--token", help="GitHub PAT（也可设环境变量 GITHUB_TOKEN）")
    ap.add_argument("--message", help="一句话摘要，追加到自动变更日志顶部", default="")
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

    # 1. 先提交所有源码改动并打 tag
    ensure_git()
    commit_msg = "release: %s" % version
    if args.message:
        commit_msg += " — " + args.message
    commit_and_tag(version, commit_msg)
    sha = git_head_sha()

    # 2. 计算变更日志（基于上一版本 tag，此时 HEAD 已包含本次 release commit）
    prev_version = read_prev_version()
    prev_ref = find_prev_ref(prev_version)
    changelog = git_changelog(prev_ref)
    files_changed = git_changed_files(prev_ref)
    print("· 上一版本基准:", prev_ref or "(初始，无历史)")
    print("· 本次提交 %d 条，变更文件 %d 个" % (len(changelog), len(files_changed)))

    body_text = build_body(version, date, repo, args.message, changelog, files_changed)

    # 3. 写 version.json（含 changes/body）
    write_version(version, date, repo, sha, changelog, body_text)
    print("· 已写入 version.json")

    # 4. 写 CHANGELOG.md
    write_changelog(version, date, args.message, changelog, files_changed)
    print("· 已更新 CHANGELOG.md")

    # 5. 把 version.json / CHANGELOG.md 更新纳入 release commit 并重新指向 tag
    git_run(["add", "-A"])
    git_run(["commit", "--amend", "--no-edit"])
    git_run(["tag", "-d", version])
    git_run(["tag", version])
    sha = git_head_sha()
    write_version(version, date, repo, sha, changelog, body_text)

    # 6. 打包
    n = pack()
    print("· 已打包 %d 个文件 -> %s (%.1f MB)" % (
        n, OUT_ZIP, os.path.getsize(OUT_ZIP) / 1024 / 1024))

    # 7. 推送
    push(token)

    # 8. GitHub Release（含自动变更日志）
    if repo and token:
        ok = github_release(repo, version, body_text, token)
        if not ok:
            print("发布到 GitHub 失败，但本地 zip 与 git 已完成。")
    else:
        print("· 未提供 repo/token，跳过 GitHub Release 上传。")
        print("  配置方法：设置 GITHUB_REPO 与 GITHUB_TOKEN 后重跑本脚本即可自动上传。")

    print("\n✓ 完成。部署包：", OUT_ZIP)


if __name__ == "__main__":
    main()
