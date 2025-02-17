const { access } = require('fs/promises');
const { constants } = require('fs');
const Autoprefixer = require('autoprefixer');
const CssNano = require('cssnano');
const { basename, sep, resolve } = require('path');
const rollup = require('rollup');
const { nodeResolve } = require('@rollup/plugin-node-resolve');
const replace = require('@rollup/plugin-replace');
const { babel } = require('@rollup/plugin-babel');
const Postcss = require('postcss');
const { renderSync } = require('sass');
const { minifyJs } = require('./minify.es6.js');
const { handleESMToLegacy } = require('./compile-to-es5.es6.js');

const getWcMinifiedCss = async (file) => {
  let scssFileExists = false;
  const scssFile = file.replace(`${sep}js${sep}`, `${sep}scss${sep}`).replace(/\.w-c\.es6\.js$/, '.scss');
  try {
    // eslint-disable-next-line no-bitwise
    await access(scssFile, constants.R_OK | constants.W_OK);

    scssFileExists = true;
  } catch { /* nothing */ }

  /// {{CSS_CONTENTS_PLACEHOLDER}}
  if (scssFileExists) {
    let compiled;
    try {
      compiled = renderSync({ file: scssFile });
    } catch (error) {
      // eslint-disable-next-line no-console
      console.error(`${error.column}
                    ${error.message}
                    ${error.line}`);
    }

    if (typeof compiled === 'object' && compiled.css) {
      return Postcss([Autoprefixer(), CssNano()])
        .process(compiled.css.toString(), { from: undefined });
    }
  }

  return '';
};

/**
 * Compiles es6 files to es5.
 *
 * @param file the full path to the file + filename + extension
 */
module.exports.handleESMFile = async (file) => {
  // eslint-disable-next-line no-console
  console.log(`Tranpiling ES2017 file: ${basename(file).replace('.es6.js', '.js')}...`);
  const newPath = file.replace(/\.w-c\.es6\.js$/, '').replace(/\.es6\.js$/, '').replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`);
  const minifiedCss = await getWcMinifiedCss(file);
  const bundle = await rollup.rollup({
    input: resolve(file),
    plugins: [
      nodeResolve({
        preferBuiltins: false,
      }),
      replace({
        CSS_CONTENTS_PLACEHOLDER: minifiedCss,
        delimiters: ['{{', '}}'],
      }),
      babel({
        exclude: 'node_modules/core-js/**',
        babelHelpers: 'bundled',
        babelrc: false,
        presets: [
          [
            '@babel/preset-env',
            {
              targets: {
                esmodules: true,
              },
              bugfixes: true,
              loose: true,
            },
          ],
        ],
      }),
    ],
    external: [],
  });

  await bundle.write({
    format: 'es',
    sourcemap: false,
    file: resolve(`${newPath}.js`),
  });

  // eslint-disable-next-line no-console
  console.log(`ES2017 file: ${basename(file).replace('.es6.js', '.js')}: transpiled ✅`);

  await handleESMToLegacy(resolve(`${newPath}.js`));
  await minifyJs(resolve(`${newPath}.js`));
};
