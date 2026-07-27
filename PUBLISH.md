# 北港3D 部署包发布与在线更新

把每次打包结果发布到 GitHub，并在网站后台一键拉取最新包更新站点。

---

## 一、首次发布到 GitHub

### 1. 准备
- 安装 [git](https://git-scm.com/)。
- 到 GitHub 新建一个**空仓库**（如 `yourname/beigang-php`），不要勾选 README。
- 生成一个 **Personal Access Token（PAT）**，勾选 `repo` 权限（Fine-grained 则需 Contents + Releases）。

### 2. 配置仓库地址
编辑 `lib/config.php`，把仓库填进去（后台「在线更新」用）：
```php
if (!defined('BG_GITHUB_REPO')) define('BG_GITHUB_REPO', 'yourname/beigang-php');
```

### 3. 运行发布脚本
在本目录执行（token 建议用环境变量，避免出现在命令行历史）：
```bash
# Windows PowerShell
$env:GITHUB_REPO = "yourname/beigang-php"
$env:GITHUB_TOKEN = "ghp_xxx"
python publish.py --version v2026.07.27

# 或 Linux / macOS
export GITHUB_REPO=yourname/beigang-php
export GITHUB_TOKEN=ghp_xxx
python3 publish.py --version v2026.07.27
```
脚本会：打包 → 写入 `version.json` → 本地 `git commit + tag` → 推送到 origin → 在 GitHub 创建 Release 并上传 `beigang-php-deploy.zip`。

> 不带 `--token` 也能跑：仅本地打包 + git 提交，跳过 GitHub 上传，方便先验证。

---

## 二、后台「在线更新」（拉取最新包）

登录后台 → 左侧「系统 / 在线更新」：

1. **检查更新**：调用 GitHub API 获取最新 Release 的 `beigang-php-deploy.zip` 信息与当前版本对比。
2. **立即更新**：下载 zip → 自动备份将被覆盖的文件到 `data/backups/site-YYYYMMDD-HHMM/` → 解压覆盖。
   - **保留数据（默认）**：不覆盖 `data/`、`uploads/`，线上后台编辑与上传文件不丢。
   - **全量覆盖**：需在 `lib/config.php` 开启 `BG_UPDATE_ALLOW_FULL` 才会出现该选项，会覆盖 `data/content.json` 与 `uploads/`（丢失线上编辑，慎选）。

回滚：把 `data/backups/site-*/` 里对应文件复制回站点根目录即可。

### 主机要求
- PHP 开启 `ZipArchive` 扩展。
- PHP 进程对站点目录有写权限（与上传功能一致）。
- 主机能访问 `api.github.com`（见下方国内网络方案）。

---

## 三、国内主机网络适配

若虚拟主机访问 GitHub 不稳定，改用**自定义镜像源**：

1. 自己托管每次发布的 zip（如对象存储 / 另一台服务器 / Gitee Releases），并在某 URL 暴露一个 JSON：
   ```json
   { "tag": "v2026.07.27", "name": "Release v2026.07.27",
     "published_at": "2026-07-27T11:04:00Z",
     "download_url": "https://你的镜像/beigang-php-deploy.zip" }
   ```
2. 编辑 `lib/config.php`：
   ```php
   if (!defined('BG_UPDATE_SOURCE'))   define('BG_UPDATE_SOURCE', 'custom');
   if (!defined('BG_UPDATE_INFO_URL')) define('BG_UPDATE_INFO_URL', 'https://你的镜像/latest.json');
   ```
后台「在线更新」即改为请求该接口，不再依赖 GitHub。

---

## 四、上线前必改（务必）
- `lib/config.php`：`BG_SESSION_SECRET` 改成随机长字符串。
- 后台「修改密码」把默认 `admin / admin123` 改掉。
- 确保 `data/`、`uploads/` 目录 PHP 可写。

---

## 五、版本文件
`version.json`（打进 zip）形如：
```json
{ "version": "v2026.07.27", "date": "2026-07-27 11:04", "repo": "yourname/beigang-php" }
```
后台据此显示「当前版本」。
