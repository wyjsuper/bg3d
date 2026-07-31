# 更新日志

## v20260731-0942 — 2026-07-31 09:42

重写 `.glass` 为 iOS 液态玻璃材质：强 `backdrop-filter` 折射（blur + saturate + brightness）+ 边缘 rim 亮线 + 顶部 specular 内高光；新增 `.bg-aurora` 整页流动彩光背景层，让前后台所有玻璃元素都能折射出色彩；`.glow-card.glass` 与 `.glass-panel` 同步液态玻璃化并带彩色 rim；前台 header/hero/benefits/footer 与后台 sidebar/顶栏/面板折射生效；后台登录页右侧改为强彩光舞台，登录卡改为 28px 大圆角玻璃，输入框也改为半透明玻璃底。

### 提交记录
- release: v20260731-0942 (11995f3)

### 变更文件
```
M	admin/login.php
M	assets/css/style.css
M	lib/admin.php
M	lib/render.php
```

---
## v20260731-0919 — 2026-07-31 09:19

前台整体改回浅色科技风 + 真正生效的 glass 玻璃质感；后台登录页与后台框架同步浅色玻璃化。

### 提交记录
- release: v20260731-0919 (c537098)
- fix: publish.py import Path + drop git-tracked version.json (gitignored) (606e5c6)

### 变更文件
```
M	3d.php
M	admin/index.php
M	admin/login.php
M	assets/css/style.css
M	contact.php
M	index.php
M	lib/admin.php
M	lib/render.php
M	publish.py
```

---
## v2026.07.30.3 — 2026-07-30 18:08
在线更新页增加历史版本列表与修改说明，支持按版本更新/回滚；发布脚本自动生成变更日志

### 提交记录
- feat: online update shows changelog & version list (update/rollback by tag); publish.py auto-generates changelog (22f2771)
- feat: comprehensive dark glassmorphism (hero, benefits, contact, footer, admin) (3f85e76)
- feat: add liquid-glass stage background to benefits section (0f7d601)
- feat: glassmorphism effect on threeds benefit cards (d4faa9c)

### 变更文件
```
M	3d.php
M	admin/index.php
M	admin/login.php
M	admin/update.php
M	api/update.php
M	assets/css/style.css
M	assets/js/admin.js
M	contact.php
M	index.php
M	lib/admin.php
M	lib/render.php
M	lib/update.php
M	publish.py
```

---
## v2026.07.30.3 — 2026-07-30 18:08
在线更新页增加历史版本列表与修改说明，支持按版本更新/回滚；发布脚本自动生成变更日志

### 提交记录
- feat: online update shows changelog & version list (update/rollback by tag); publish.py auto-generates changelog (22f2771)
- feat: comprehensive dark glassmorphism (hero, benefits, contact, footer, admin) (3f85e76)
- feat: add liquid-glass stage background to benefits section (0f7d601)
- feat: glassmorphism effect on threeds benefit cards (d4faa9c)

### 变更文件
```
M	3d.php
M	admin/index.php
M	admin/login.php
M	admin/update.php
M	api/update.php
M	assets/css/style.css
M	assets/js/admin.js
M	contact.php
M	index.php
M	lib/admin.php
M	lib/render.php
M	lib/update.php
M	publish.py
```

---
