import { build } from 'esbuild';
import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';
const here = dirname(fileURLToPath(import.meta.url));
await build({
  entryPoints: { editor: resolve(here,'src/main.tsx') },
  outdir: resolve(here,'../../redaktsiya/assets'),
  bundle: true, minify: true, format: 'esm', target: 'es2020',
  supported: { 'template-literal': false },
  jsx: 'automatic', alias: {'@':resolve(here,'src')},
  define: {'process.env.NODE_ENV':'"production"'},
  legalComments: 'eof', charset: 'utf8',
});
