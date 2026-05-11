import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    outDir: '../../htdocs/core.staratlasmedia.com/public/sdk',
    emptyOutDir: true,
    lib: {
      entry: 'src/index.ts',
      name: 'StarAtlasCoreSdk',
      formats: ['es', 'iife'],
      fileName: (format) => `core-sdk.${format}.js`,
    },
  },
});
