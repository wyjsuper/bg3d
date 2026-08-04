import subprocess, re, os, glob

FFMPEG = r"C:\Users\jj\.workbuddy\binaries\python\envs\default\Lib\site-packages\imageio_ffmpeg\binaries\ffmpeg-win-x86_64-v7.1.exe"
SRC_DIR = "videos"
DST_DIR = "videos_compressed"
os.makedirs(DST_DIR, exist_ok=True)

TARGET_BYTES = 1.5 * 1024 * 1024  # 1.5 MB 目标

def get_duration(path):
    out = subprocess.run([FFMPEG, "-i", path], capture_output=True,
                         text=True, encoding="utf-8", errors="ignore", timeout=60).stderr
    m = re.search(r"Duration:\s*(\d+):(\d+):(\d+(?:\.\d+)?)", out)
    if not m:
        return None
    h, mi, s = int(m.group(1)), int(m.group(2)), float(m.group(3))
    return h * 3600 + mi * 60 + s

files = sorted(glob.glob(os.path.join(SRC_DIR, "mvideo*.mp4")))
print(f"待压缩: {len(files)}")
ok, fail = [], []
for f in files:
    name = os.path.basename(f)
    dst = os.path.join(DST_DIR, name)
    dur = get_duration(f)
    if dur is None or dur <= 0:
        fail.append((name, "no-duration"))
        continue
    # 目标 1.5MB 全给视频（无音频）
    vbit = int(TARGET_BYTES * 8 / dur)
    vbit = max(150_000, min(vbit, 1_500_000))  # 下限 150kbps 避免过糊，上限 1.5Mbps
    cmd = [FFMPEG, "-y", "-i", f, "-vf", "scale=480:-2",
           "-c:v", "libx264", "-b:v", str(vbit),
           "-maxrate", str(int(vbit * 1.2)), "-bufsize", str(vbit * 2),
           "-preset", "veryfast", "-an", "-movflags", "+faststart", dst]
    r = subprocess.run(cmd, capture_output=True, text=True, encoding="utf-8", errors="ignore", timeout=300)
    if r.returncode == 0 and os.path.exists(dst) and os.path.getsize(dst) > 1000:
        ok.append((name, round(os.path.getsize(dst) / 1024 / 1024, 2)))
    else:
        fail.append((name, f"rc={r.returncode}"))
        if os.path.exists(dst):
            os.remove(dst)

print(f"成功: {len(ok)}  失败: {len(fail)}")
for n, sz in ok:
    print(f"  OK {n}  {sz} MB")
for n, e in fail:
    print(f"  FAIL {n}  {e}")
