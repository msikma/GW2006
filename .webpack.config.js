// Gaming World 2006 <https://gamingw.net/>
// © MIT License

const path = require('path')

const config = {
  entry: './scripts/index.js',
  output: {
    path: path.resolve(__dirname, 'scripts', 'dist'),
  },
  mode: process.env.NODE_ENV ?? 'production',
  module: {
    rules: [
      {
        test: /\.(js)$/i,
        loader: 'babel-loader',
      },
    ],
  },
}

module.exports = config
