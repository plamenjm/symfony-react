module.exports = {
  parserOptions: {
    //ecmaVersion: 6,
    ecmaVersion: 2022,
    sourceType: 'module',
    ecmaFeatures: {jsx: true},
  },
  env: {
    browser: true,
    es6: true,
    node: true,
  },
  extends: [
    'eslint:recommended',
  ],
  rules: {
    "no-unused-vars": 0,
    //"no-console": 0,
  },

  root: true,
  overrides: [{ // npm install --save-dev @typescript-eslint/parser @typescript-eslint/eslint-plugin
    files: ['*.ts', '*.tsx'],
    parser: '@typescript-eslint/parser',
    plugins: ['@typescript-eslint'],
    extends: [
      'plugin:@typescript-eslint/recommended',
    ],
    rules: {
      '@typescript-eslint/no-unused-vars': 'off',
      //'@typescript-eslint/no-explicit-any': 'warn',
    },
  }],
}
