import subprocess, os
FF = r"C:/Users/jj/.workbuddy/binaries/python/envs/default/Lib/site-packages/imageio_ffmpeg/binaries/ffmpeg-win-x86_64-v7.1.exe"
base = os.path.dirname(os.path.abspath(__file__))
v = os.path.join(base, "videos", "mvideo06.mp4")
tmp = os.path.join(base, "tmp_m06")
os.makedirs(tmp, exist_ok=True)
parts = []
for ts in [0,2,4,6,8,10,12]:
    out = os.path.join(tmp, f"f{ts}.png")
    subprocess.run([FF, "-y", "-ss", str(ts), "-i", v, "-frames:v", "1", "-vf", "scale=160:-2", out],
                   stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    parts.append(out)
inputs = []
for p in parts: inputs += ["-i", p]
layout = "|".join(f"{(i%7)*160}_{0}" for i in range(len(parts)))
fc = f"xstack=inputs={len(parts)}:layout={layout}"
out = os.path.join(tmp, "strip.png")
subprocess.run([FF, "-y"] + inputs + ["-filter_complex", fc, out], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
print("strip:", out, os.path.getsize(out))
