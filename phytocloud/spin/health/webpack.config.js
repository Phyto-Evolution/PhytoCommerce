const path = require('path');
module.exports = {
  entry: './src/index.ts',
  mode: 'production',
  output: { filename: 'dist.js', path: path.resolve(__dirname, '.') },
  module: { rules: [{ test: /\.tsx?$/, use: 'ts-loader', exclude: /node_modules/ }] },
  resolve: { extensions: ['.ts', '.js'] },
};
