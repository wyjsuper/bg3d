// 本地编译 Tailwind v4 样式为单文件（虚拟主机无需再构建）
// 用法：node beigang-php/src/build-css.mjs  （在 Ai-Web 根目录运行）
import postcss from 'postcss';
import tailwind from '@tailwindcss/postcss';
import fs from 'fs';
import path from 'path';

const root = path.resolve('beigang-php');
const inputFile = path.join(root, 'src/input.css');
const outFile = path.join(root, 'assets/css/style.css');

let css = fs.readFileSync(inputFile, 'utf8');
// 显式声明扫描路径（相对 input.css 所在目录 beigang-php/src）
css += `
@source "../*.php";
@source "../admin/*.php";
@source "../lib/*.php";
@source "../../src/**/*.{ts,tsx}";
`;

const result = await postcss([tailwind]).process(css, { from: inputFile });
fs.mkdirSync(path.dirname(outFile), { recursive: true });
fs.writeFileSync(outFile, result.css);
console.log('CSS compiled ->', outFile, 'bytes:', result.css.length);
if (result.warnings().length) {
  console.warn('warnings:', result.warnings().map(w => w.text));
}
