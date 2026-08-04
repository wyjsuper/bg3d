import json, os, glob, shutil
from pathlib import Path

ROOT = Path("C:/Users/jj/WorkBuddy/20260721103234/Ai-Web/beigang-php")
VIDEOS = ROOT / "videos"
COMP = ROOT / "videos_compressed"

# 1. 读取现有 content.json（6 产品 + 19 gif）
cj = json.load(open(ROOT / "data/content.json", encoding="utf-8"))
th = cj["threeds"]

products = [it for it in th if it.get("videoUrl","").endswith(".mp4") and "mvideo" not in it.get("videoUrl","")]
gifs = [it for it in th if it.get("videoUrl","").endswith(".gif")]
print("现有: 产品视频", len(products), "gif", len(gifs))

# 2. 压缩视频（按序号排序，mvideo01..mvideo22）
comps = sorted(glob.glob(str(COMP / "mvideo*.mp4")))
comps = [Path(c).name for c in comps]
print("压缩视频数:", len(comps))

# 3. 移动压缩视频到 videos/（覆盖原大文件）
for c in comps:
    src = COMP / c
    dst = VIDEOS / c
    if src.exists():
        shutil.move(str(src), str(dst))
print("已移入 videos/")

# 4. 把不再引用的 gif 移出备份（避免打进包）
backup = ROOT.parent / "_old_gifs_backup"
backup.mkdir(exist_ok=True)
moved_gif = 0
for g in gifs:
    fn = g.get("videoUrl","").lstrip("/")
    src = ROOT / fn
    if src.exists() and src.is_file():
        shutil.move(str(src), str(backup / src.name))
        moved_gif += 1
# 同时把 c/ 子目录下残留 gif 也移走（若未被引用）
for gf in glob.glob(str(VIDEOS / "**/*.gif"), recursive=True):
    pass
print("移出 gif 数:", moved_gif)

# 5. 重建 threeds：6 产品 + 22 压缩视频（前19复用gif标题，后3通用标题）
new_th = list(products)
for i, c in enumerate(comps):
    url = f"/videos/{c}"
    if i < len(gifs):
        g = gifs[i]
        title = g.get("title", {"zh":"工业三维动画"})
        category = g.get("category", {"zh":"工业三维动画"})
        desc = g.get("description", {"zh":"","en":""})
    else:
        title = {"zh": f"工业三维动画作品 {i-len(gifs)+1}", "en": f"3D Animation Work {i-len(gifs)+1}"}
        category = {"zh": "工业三维动画", "en": "Industrial 3D Animation"}
        desc = {"zh":"","en":""}
    new_th.append({
        "id": f"mv{i+1:02d}",
        "title": title,
        "category": category,
        "description": desc,
        "videoUrl": url,
        "poster": ""
    })

cj["threeds"] = new_th
json.dump(cj, open(ROOT / "data/content.json","w",encoding="utf-8"), ensure_ascii=False, indent=2)
print("新 threeds 总数:", len(new_th))

# 6. 清理临时目录
if COMP.exists():
    shutil.rmtree(str(COMP))
print("已清理 videos_compressed/")
