# 更新日志

## v20260804-1413 — 2026-08-04 14:13
部署包排除运行时数据：content.json 与 videos/

### 提交记录
- release: v20260804-1413 — 部署包排除运行时数据：content.json 与 videos/ (fa34a83)

### 变更文件
```
M	publish.py
```

---
## v20260804-1147 — 2026-08-04 11:47
所有视频卡片（含 gif）均可点击在新窗口放大播放

### 提交记录
- release: v20260804-1147 — 所有视频卡片（含 gif）均可点击在新窗口放大播放 (a758843)

### 变更文件
```
M	lib/render.php
M	play.php
```

---
## v20260804-1140 — 2026-08-04 11:40
点击视频卡片在新窗口放大播放

### 提交记录
- release: v20260804-1140 — 点击视频卡片在新窗口放大播放 (a9f6da8)

### 变更文件
```
M	assets/js/site.js
M	lib/render.php
A	play.php
```

---
## v20260804-1133 — 2026-08-04 11:33
去掉视频卡片中央播放按钮

### 提交记录
- release: v20260804-1133 — 去掉视频卡片中央播放按钮 (8ae7215)

### 变更文件
```
M	assets/css/style.css
M	assets/js/site.js
M	lib/render.php
M	src/input.css
```

---
## v20260731-1129 — 2026-07-31 11:29

液态玻璃卡片的「光泽流动」效果扩展到移动端：
- CSS：`.glow-card.glass` 的扫光动画新增 `.shine` 与 `:active` 触发器，让手机和触摸设备也能看到光泽划过。
- JS：`site.js` 新增 `initGlassShine()`：
  - 玻璃卡片滚动进入视口时自动触发一次扫光（仅在非 hover 设备）。
  - 用户点击/触摸卡片时也会触发一次扫光反馈。

### 提交记录
- feat: glass sweep shine for mobile (scroll-in + tap trigger) (d05f788)

### 变更文件
```
M	assets/css/style.css
M	assets/js/site.js
```

---

## v20260731-1002 — 2026-07-31 10:02

后台工作台新增「系统」区块，在右侧主内容区显示「在线更新」与「修改密码」两个快捷入口卡片，与左侧系统菜单对应，方便快速进入。

### 提交记录
- feat: dashboard system cards (online update + change password) (f500464)

### 变更文件
```
M	admin/index.php
```

---

## v20260731-0957 — 2026-07-31 09:57

`.glow-card.glass` 增加鼠标 hover 时的光泽流动扫光：斜向白光（混入淡青高光）从卡片表面划过，配合已有的 iOS 液态玻璃材质（强 backdrop-filter 折射 + rim 亮线 + specular 内高光），让 benefits 等卡片在 hover 时呈现晶莹流动感。

### 提交记录
- feat: glass card hover sweep shine (4950bdb)

### 变更文件
```
M	assets/css/style.css
```

---

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
