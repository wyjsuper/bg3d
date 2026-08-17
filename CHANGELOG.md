# 更新日志

## v20260817-1306 — 2026-08-17 13:06
同步最新 FDE 资讯（归档更新至 2026-08-17，217 条）；含换脸🤡/电商监控🛒图标、飘图自动更新、AI FDE资讯仅FDE地图页脚

### 提交记录
- release: v20260817-1306 — 同步最新 FDE 资讯（归档更新至 2026-08-17，217 条）；含换脸🤡/电商监控🛒图标、飘图自动更新、AI FDE资讯仅FDE地图页脚 (ac94556)

### 变更文件
```
M	data/content.json.bak
M	data/fde-archive.json
M	data/fde-archive.json.bak
A	tools/fde_daily_update.py
```

---
## v20260810-2313 — 2026-08-10 23:13
FDE地图飘图随导航下拉自动更新(换脸🤡卡通脸/电商监控🛒)；AI FDE资讯仅FDE地图页脚；导航移除顶层WiKi；新增换脸/电商监控两项目

### 提交记录
- release: v20260810-2313 — FDE地图飘图随导航下拉自动更新(换脸🤡卡通脸/电商监控🛒)；AI FDE资讯仅FDE地图页脚；导航移除顶层WiKi；新增换脸/电商监控两项目 (0e42d71)

### 变更文件
```
M	fde-map.php
```

---
## v20260810-1501 — 2026-08-10 15:01
FDE地图飘图随导航下拉自动更新；导航移除顶层WiKi、FDE下拉保留；AI FDE资讯仅FDE地图页脚

### 提交记录
- release: v20260810-1501 — FDE地图飘图随导航下拉自动更新；导航移除顶层WiKi、FDE下拉保留；AI FDE资讯仅FDE地图页脚 (cc8f008)

### 变更文件
```
A	data/content.json.bak
A	data/fde-archive.json
A	data/fde-archive.json.bak
A	data/fde-archive.json.bak2
A	fde-map.php
M	lib/helpers.php
A	lib/news.php
M	lib/render.php
M	lib/schema.php
M	lib/ui-text.php
A	news.php
A	tools/fde_news_write.py
A	tools/fde_seed_archive.py
A	tools/fde_seed_en.py
```

---
## v20260805-1056 — 2026-08-05 10:56
新增3个视频片头10秒跳过(MOXA-mvideo06/暗藏式马桶/三维动画作品3),video01仅10秒恢复不跳过

### 提交记录
- release: v20260805-1056 — 新增3个视频片头10秒跳过(MOXA-mvideo06/暗藏式马桶/三维动画作品3),video01仅10秒恢复不跳过 (d5a1fa6)

### 变更文件
```
M	.gitignore
M	publish.py
A	tmp_m06.py
A	tmp_v01.py
M	videos/g/mvideo06.gif
M	videos/g/mvideo25.gif
M	videos/g/mvideo34.gif
```

---
## v20260805-1013 — 2026-08-05 10:13
9个视频片头跳过秒数统一改为10秒(GIF从10s截取+全屏自动seek)

### 提交记录
- release: v20260805-1013 — 9个视频片头跳过秒数统一改为10秒(GIF从10s截取+全屏自动seek) (9faf1b1)

### 变更文件
```
M	videos/g/mvideo05.gif
M	videos/g/mvideo09.gif
M	videos/g/mvideo15.gif
M	videos/g/mvideo16.gif
M	videos/g/mvideo17.gif
M	videos/g/mvideo23.gif
M	videos/g/mvideo24.gif
M	videos/g/mvideo29.gif
M	videos/g/mvideo31.gif
```

---
## v20260805-0941 — 2026-08-05 09:41
跳过片头logo:9个视频配置introSkip,网格GIF从跳过点生成,全屏播放自动seek

### 提交记录
- release: v20260805-0941 — 跳过片头logo:9个视频配置introSkip,网格GIF从跳过点生成,全屏播放自动seek (bf36662)

### 变更文件
```
M	.gitignore
M	lib/render.php
M	lib/schema.php
M	play.php
M	publish.py
M	videos/g/mvideo05.gif
M	videos/g/mvideo09.gif
M	videos/g/mvideo15.gif
M	videos/g/mvideo16.gif
M	videos/g/mvideo17.gif
M	videos/g/mvideo23.gif
M	videos/g/mvideo24.gif
M	videos/g/mvideo29.gif
M	videos/g/mvideo31.gif
```

---
## v20260805-0909 — 2026-08-05 09:09
GIF网格预览修正:排除videos根旧gif/images/cases gif,仅保留videos/g/网格预览+原版mp4全屏,去掉冗余videos/m

### 提交记录
- release: v20260805-0909 — GIF网格预览修正:排除videos根旧gif/images/cases gif,仅保留videos/g/网格预览+原版mp4全屏,去掉冗余videos/m (f846377)

### 变更文件
```
M	publish.py
```

---
## v20260805-0907 — 2026-08-05 09:07
第二步:网格改GIF短循环预览(4s/320px/10fps,100%不受微信自动播放限制)+点击全屏播原版mp4

### 提交记录
- release: v20260805-0907 — 第二步:网格改GIF短循环预览(4s/320px/10fps,100%不受微信自动播放限制)+点击全屏播原版mp4 (ad04a68)

### 变更文件
```
M	lib/render.php
M	publish.py
A	videos/g/mvideo01.gif
A	videos/g/mvideo02.gif
A	videos/g/mvideo04.gif
A	videos/g/mvideo05.gif
A	videos/g/mvideo06.gif
A	videos/g/mvideo09.gif
A	videos/g/mvideo13.gif
A	videos/g/mvideo14.gif
A	videos/g/mvideo15.gif
A	videos/g/mvideo16.gif
A	videos/g/mvideo17.gif
A	videos/g/mvideo18.gif
A	videos/g/mvideo19.gif
A	videos/g/mvideo23.gif
A	videos/g/mvideo24.gif
A	videos/g/mvideo25.gif
A	videos/g/mvideo29.gif
A	videos/g/mvideo31.gif
A	videos/g/mvideo33.gif
A	videos/g/mvideo34.gif
A	videos/g/video01.gif
A	videos/g/video02.gif
A	videos/g/video03.gif
A	videos/g/video04.gif
A	videos/g/video05.gif
A	videos/g/video06.gif
A	videos/g/video07.gif
A	videos/g/video08.gif
```

---
## v20260805-0856 — 2026-08-05 08:56
回退视频加载:去掉易挂的并发队列,改进视口即播+首次交互DOM兜底(修registry闭包bug),保留手机轻量版

### 提交记录
- release: v20260805-0856 — 回退视频加载:去掉易挂的并发队列,改进视口即播+首次交互DOM兜底(修registry闭包bug),保留手机轻量版 (f2816a3)

### 变更文件
```
M	assets/js/site.js
```

---
## v20260805-0834 — 2026-08-05 08:34
微信加载优化:视频按DOM顺序从前到后依次加载(顶部先填满再向下推进)

### 提交记录
- release: v20260805-0834 — 微信加载优化:视频按DOM顺序从前到后依次加载(顶部先填满再向下推进) (445a4db)

### 变更文件
```
M	assets/js/site.js
```

---
## v20260805-0821 — 2026-08-05 08:21
手机端二次压缩:240px/8s/45kbps预览版,累计比原版小19.6x

### 提交记录
- release: v20260805-0821 — 手机端二次压缩:240px/8s/45kbps预览版,累计比原版小19.6x (447e3ea)

### 变更文件
```
M	videos/m/mvideo01.mp4
M	videos/m/mvideo02.mp4
M	videos/m/mvideo04.mp4
M	videos/m/mvideo05.mp4
M	videos/m/mvideo06.mp4
M	videos/m/mvideo09.mp4
M	videos/m/mvideo13.mp4
M	videos/m/mvideo14.mp4
M	videos/m/mvideo15.mp4
M	videos/m/mvideo16.mp4
M	videos/m/mvideo17.mp4
M	videos/m/mvideo18.mp4
M	videos/m/mvideo19.mp4
M	videos/m/mvideo23.mp4
M	videos/m/mvideo24.mp4
M	videos/m/mvideo25.mp4
M	videos/m/mvideo29.mp4
M	videos/m/mvideo31.mp4
M	videos/m/mvideo33.mp4
M	videos/m/mvideo34.mp4
M	videos/m/video01.mp4
M	videos/m/video02.mp4
M	videos/m/video03.mp4
M	videos/m/video04.mp4
M	videos/m/video05.mp4
M	videos/m/video06.mp4
M	videos/m/video07.mp4
M	videos/m/video08.mp4
```

---
## v20260805-0743 — 2026-08-05 07:43
手机端5倍提速(修正):28个视频全生成15s轻量预览版videos/m/，兼容mvideoXX+videoXX命名

### 提交记录
- release: v20260805-0743 — 手机端5倍提速(修正):28个视频全生成15s轻量预览版videos/m/，兼容mvideoXX+videoXX命名 (51eab5e)

### 变更文件
```
M	lib/render.php
A	videos/m/video01.mp4
A	videos/m/video02.mp4
A	videos/m/video03.mp4
A	videos/m/video04.mp4
A	videos/m/video05.mp4
A	videos/m/video06.mp4
A	videos/m/video07.mp4
A	videos/m/video08.mp4
```

---
## v20260805-0740 — 2026-08-05 07:40
手机端5倍提速：生成15s截断+320px轻量预览版videos/m/，桌面仍走原版

### 提交记录
- release: v20260805-0740 — 手机端5倍提速：生成15s截断+320px轻量预览版videos/m/，桌面仍走原版 (5ac2884)

### 变更文件
```
M	assets/js/site.js
M	lib/render.php
A	videos/m/mvideo01.mp4
A	videos/m/mvideo02.mp4
A	videos/m/mvideo04.mp4
A	videos/m/mvideo05.mp4
A	videos/m/mvideo06.mp4
A	videos/m/mvideo09.mp4
A	videos/m/mvideo13.mp4
A	videos/m/mvideo14.mp4
A	videos/m/mvideo15.mp4
A	videos/m/mvideo16.mp4
A	videos/m/mvideo17.mp4
A	videos/m/mvideo18.mp4
A	videos/m/mvideo19.mp4
A	videos/m/mvideo23.mp4
A	videos/m/mvideo24.mp4
A	videos/m/mvideo25.mp4
A	videos/m/mvideo29.mp4
A	videos/m/mvideo31.mp4
A	videos/m/mvideo33.mp4
A	videos/m/mvideo34.mp4
```

---
## v20260805-0727 — 2026-08-05 07:27
手机端视频加载优化：并发受限+优先当前可见视频队列

### 提交记录
- release: v20260805-0727 — 手机端视频加载优化：并发受限+优先当前可见视频队列 (ceea27f)

### 变更文件
```
M	assets/js/site.js
```

---
## v20260805-0704 — 2026-08-05 07:04
修复手机端视频自动播放(iOS/微信 WebView muted 属性+首次交互兜底)

### 提交记录
- release: v20260805-0704 — 修复手机端视频自动播放(iOS/微信 WebView muted 属性+首次交互兜底) (5176651)

### 变更文件
```
M	assets/js/site.js
```

---
## v20260805-0641 — 2026-08-05 06:41
完整包：视频自动循环播放+手机版优化+英文版完整性修复+去除'工业三维动画'字样

---
## v20260805-0633 — 2026-08-05 06:33
视频自动循环播放+手机版优化(按钮常驻/滚出暂停)+英文版完整性修复+去除'工业三维动画'字样

### 提交记录
- release: v20260805-0633 — 视频自动循环播放+手机版优化(按钮常驻/滚出暂停)+英文版完整性修复+去除'工业三维动画'字样 (2cddce1)

### 变更文件
```
M	assets/js/site.js
M	lib/lang.php
M	lib/render.php
M	lib/ui-text.php
M	publish.py
A	videos/mvideo01.gif
A	videos/mvideo01.jpg
A	videos/mvideo02.gif
A	videos/mvideo02.jpg
A	videos/mvideo04.gif
A	videos/mvideo04.jpg
A	videos/mvideo05.gif
A	videos/mvideo05.jpg
A	videos/mvideo06.gif
A	videos/mvideo06.jpg
A	videos/mvideo09.gif
A	videos/mvideo09.jpg
A	videos/mvideo13.gif
A	videos/mvideo13.jpg
A	videos/mvideo14.gif
A	videos/mvideo14.jpg
A	videos/mvideo15.gif
A	videos/mvideo15.jpg
A	videos/mvideo16.gif
A	videos/mvideo16.jpg
A	videos/mvideo17.gif
A	videos/mvideo17.jpg
A	videos/mvideo18.gif
A	videos/mvideo18.jpg
A	videos/mvideo19.gif
A	videos/mvideo19.jpg
A	videos/mvideo23.gif
A	videos/mvideo23.jpg
A	videos/mvideo24.gif
A	videos/mvideo24.jpg
A	videos/mvideo25.gif
A	videos/mvideo25.jpg
A	videos/mvideo29.gif
A	videos/mvideo29.jpg
A	videos/mvideo31.gif
A	videos/mvideo31.jpg
A	videos/mvideo33.gif
A	videos/mvideo33.jpg
A	videos/mvideo34.gif
A	videos/mvideo34.jpg
A	videos/video01.gif
A	videos/video01.jpg
A	videos/video02.gif
A	videos/video02.jpg
A	videos/video03.gif
A	videos/video03.jpg
A	videos/video04.gif
A	videos/video04.jpg
A	videos/video05.gif
A	videos/video05.jpg
A	videos/video06.gif
A	videos/video06.jpg
A	videos/video07.gif
A	videos/video07.jpg
A	videos/video08.gif
A	videos/video08.jpg
```

---
## v20260804-2207 — 2026-08-04 22:07
批量补全内容库英文（232字段，覆盖率96.8%）；含28个视频与最新content.json

### 提交记录
- release: v20260804-2207 — 批量补全内容库英文（232字段，覆盖率96.8%）；含28个视频与最新content.json (9ae8eae)

### 变更文件
```
M	publish.py
```

---
## v20260804-2155 — 2026-08-04 21:55
新增2个工业产品视频（工业陶瓷压力变送器/黄石三通工业气动马达），作品库 rv27/rv28，共28条

### 提交记录
- release: v20260804-2155 — 新增2个工业产品视频（工业陶瓷压力变送器/黄石三通工业气动马达），作品库 rv27/rv28，共28条 (ac60c37)

### 变更文件
```
A	videos/video07.mp4
A	videos/video08.mp4
```

---
## v20260804-2124 — 2026-08-04 21:24
修复手机版视频不显示：全部 mp4 转 H.264 Baseline/480p/去音/faststart，并增加微信 X5 / iOS playsinline 属性

### 提交记录
- release: v20260804-2124 — 修复手机版视频不显示：全部 mp4 转 H.264 Baseline/480p/去音/faststart，并增加微信 X5 / iOS playsinline 属性 (407645e)

### 变更文件
```
M	lib/render.php
M	play.php
M	videos/mvideo01.mp4
M	videos/mvideo02.mp4
M	videos/mvideo04.mp4
M	videos/mvideo05.mp4
M	videos/mvideo06.mp4
M	videos/mvideo09.mp4
M	videos/mvideo13.mp4
M	videos/mvideo14.mp4
M	videos/mvideo15.mp4
M	videos/mvideo16.mp4
M	videos/mvideo17.mp4
M	videos/mvideo18.mp4
M	videos/mvideo19.mp4
M	videos/mvideo23.mp4
M	videos/mvideo24.mp4
M	videos/mvideo25.mp4
M	videos/mvideo29.mp4
M	videos/mvideo31.mp4
M	videos/mvideo33.mp4
M	videos/mvideo34.mp4
M	videos/video01.mp4
M	videos/video02.mp4
M	videos/video03.mp4
M	videos/video04.mp4
M	videos/video05.mp4
M	videos/video06.mp4
```

---
## v20260804-1629 — 2026-08-04 16:29
移除超过3MB的2个视频(mvideo10/mvideo12)，作品库剩26条

### 提交记录
- release: v20260804-1629 — 移除超过3MB的2个视频(mvideo10/mvideo12)，作品库剩26条 (b3c5b5a)
- test: diagnose (ec7fdf7)

### 变更文件
```
D	_build_content.py
D	_compress.py
D	_rebuild_content.py
D	m3ds.html
D	mp4_urls.txt
D	sm.xml
D	videos/morning-2.mp4
D	videos/mvideo10.mp4
D	videos/mvideo12.mp4
```

---
## v20260804-1619 — 2026-08-04 16:19
用线上 morndesign.com 的 22 个 mp4（ffmpeg 压至 1-3MB）替换 gif 演示，作品库共 28 条

### 提交记录
- release: v20260804-1619 — 用线上 morndesign.com 的 22 个 mp4（ffmpeg 压至 1-3MB）替换 gif 演示，作品库共 28 条 (9cadabc)

### 变更文件
```
A	_compress.py
A	_rebuild_content.py
A	m3ds.html
A	mp4_urls.txt
A	sm.xml
D	videos/c/mrwpa255-x11zzp.gif
D	videos/c/mrwpbx7i-23zjh4.gif
D	videos/c/mrwpqaf4-lwtams.gif
D	videos/c/mrwpr359-8s3sqk.gif
D	videos/c/mrwps3e8-dop0xu.gif
D	videos/c/mrx2th5y-nzycuo.gif
D	videos/c/mrx4bkyu-rwqsuy.gif
D	videos/c/mrx4k9g8-ifth30.gif
D	videos/c/mrx4ksc6-5q5mdu.gif
D	videos/fuchang-boiler.gif
A	videos/morning-2.mp4
D	videos/mrx4cfkv-mpk78x.gif
D	videos/mrx4cz2e-lhor4c.gif
D	videos/mrx4dhyr-k4tou7.gif
D	videos/mrx4e1gc-cwd5mv.gif
D	videos/mrx4f1xx-had242.gif
D	videos/mrx4h75z-tlj7yg.gif
D	videos/mrx4hplv-7nvfiz.gif
D	videos/mrx4i1na-u2oyuc.gif
D	videos/mrx4j3r1-li2frt.gif
A	videos/mvideo01.mp4
A	videos/mvideo02.mp4
A	videos/mvideo04.mp4
A	videos/mvideo05.mp4
A	videos/mvideo06.mp4
A	videos/mvideo09.mp4
A	videos/mvideo10.mp4
A	videos/mvideo12.mp4
A	videos/mvideo13.mp4
A	videos/mvideo14.mp4
A	videos/mvideo15.mp4
A	videos/mvideo16.mp4
A	videos/mvideo17.mp4
A	videos/mvideo18.mp4
A	videos/mvideo19.mp4
A	videos/mvideo23.mp4
A	videos/mvideo24.mp4
A	videos/mvideo25.mp4
A	videos/mvideo29.mp4
A	videos/mvideo31.mp4
A	videos/mvideo33.mp4
A	videos/mvideo34.mp4
```

---
## v20260804-1520 — 2026-08-04 15:20
3D 作品库恢复 19 个演示 gif，与 6 个真实产品视频共存共 25 条

### 提交记录
- release: v20260804-1520 — 3D 作品库恢复 19 个演示 gif，与 6 个真实产品视频共存共 25 条 (33fd0ad)

### 变更文件
```
D	videos/mrx8y8eo-rin8h5.mp4
```

---
## v20260804-1427 — 2026-08-04 14:27
完整部署包：6 个产品视频（ASCII 重命名）与内容库；修正 threeds 为数组

### 提交记录
- release: v20260804-1427 — 完整部署包：6 个产品视频（ASCII 重命名）与内容库；修正 threeds 为数组 (97d4e3e)

### 变更文件
```
M	_build_content.py
```

---
## v20260804-1423 — 2026-08-04 14:23
完整部署包：含 6 个产品视频（ASCII 重命名）与内容库

### 提交记录
- release: v20260804-1423 — 完整部署包：含 6 个产品视频（ASCII 重命名）与内容库 (05ba25e)

### 变更文件
```
A	_build_content.py
M	publish.py
A	videos/video01.mp4
A	videos/video02.mp4
A	videos/video03.mp4
A	videos/video04.mp4
A	videos/video05.mp4
A	videos/video06.mp4
```

---
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
