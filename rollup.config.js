import { nodeResolve } from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import vue from 'rollup-plugin-vue';
import postcss from 'rollup-plugin-postcss';
import replace from '@rollup/plugin-replace';
import terser from '@rollup/plugin-terser';

export default {
  input: 'src/main.js',
  output: {
    file: 'public/build/bundle.js',
    format: 'iife',
    name: 'app',
    sourcemap: true
  },
  plugins: [
    nodeResolve({
      browser: true,
      extensions: ['.js', '.vue']
    }),
    commonjs(),
    vue({
      preprocessStyles: true
    }),
    postcss({
      config: {
        path: './postcss.config.js'
      },
      extensions: ['.css'],
      minimize: true,
      inject: {
        insertAt: 'top'
      }
    }),
    replace({
      'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'development'),
      preventAssignment: true
    }),
    process.env.NODE_ENV === 'production' && terser()
  ]
}
