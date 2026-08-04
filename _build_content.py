# -*- coding: utf-8 -*-
"""一次性脚本：把 6 个中文命名的 mp4 重命名为 ASCII 名，并重建 content.json 的 threeds。"""
import os, json, shutil

HERE = os.path.dirname(os.path.abspath(__file__))
VIDEOS = os.path.join(HERE, "videos")
CONTENT = os.path.join(HERE, "data", "content.json")

# (原中文文件名, 目标 ASCII 名, 中文标题, 英文标题)
mapping = [
    ("MOXA工业以太网交换机，型号EDS_A，金属外壳长方.mp4", "video01.mp4",
     "MOXA工业以太网交换机三维动画", "MOXA Industrial Ethernet Switch 3D"),
    ("工业声光报警器，杭州天冠科技TGSG_型号，DC_V.mp4", "video02.mp4",
     "工业声光报警器三维动画", "Industrial Audible-Visual Alarm 3D"),
    ("工业安全继电器，矩形塑料外壳，宽约_mm，高约_mm，厚.mp4", "video03.mp4",
     "工业安全继电器三维动画", "Industrial Safety Relay 3D"),
    ("工业用大功率桶式吸尘器，杰诺JN_型号，_升容量，_.mp4", "video04.mp4",
     "工业用大功率桶式吸尘器三维动画", "Industrial Drum Vacuum Cleaner 3D"),
    ("工业设备按钮安装支架，短款，安装孔径_mm，外形尺寸_m.mp4", "video05.mp4",
     "工业设备按钮安装支架三维动画", "Industrial Button Mounting Bracket 3D"),
    ("欧姆龙工业光电传感器，型号E_ZG_D_S，圆柱形金属外.mp4", "video06.mp4",
     "欧姆龙工业光电传感器三维动画", "Omron Photoelectric Sensor 3D"),
]

print("=== 重命名视频 ===")
for src, dst, zh, en in mapping:
    s = os.path.join(VIDEOS, src)
    d = os.path.join(VIDEOS, dst)
    if os.path.exists(s):
        if os.path.exists(d):
            os.remove(d)
        shutil.move(s, d)
        print(f"  {src}  ->  {dst}  ({os.path.getsize(d)} bytes)")
    else:
        print(f"  [缺失] {src}")

print("\n=== 重建 content.json ===")
with open(CONTENT, "r", encoding="utf-8") as f:
    data = json.load(f)

items = []
for i, (src, dst, zh, en) in enumerate(mapping):
    items.append({
        "id": "3ds-%d" % i,
        "title": {"zh": zh, "en": en},
        "category": {"zh": "工业三维动画", "en": "Industrial 3D Animation"},
        "description": {"zh": "", "en": ""},
        "videoUrl": "/videos/" + dst,
        "poster": "",
    })

# 集合在内容库里以纯数组存储（bg_create_item 用 $data[$type][] 追加），
# 不可包成 {"items":[...]}，否则 bg_get_collection 返回的 dict 会被 foreach 当成单个元素。
data["threeds"] = items

with open(CONTENT, "w", encoding="utf-8") as f:
    json.dump(data, f, ensure_ascii=False, indent=2)
print("  threeds 条目数 =", len(items))
for it in items:
    print("   -", it["videoUrl"], "::", it["title"]["zh"])
print("\nOK")
