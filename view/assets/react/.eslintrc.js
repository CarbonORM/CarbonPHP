module.exports = {
  parser: "@typescript-eslint/parser",
  env: {
    es6: true,
    node: true,
    browser: true,
    es2021: true
  },
  parserOptions: {
    ecmaVersion: 12,
    sourceType: "module",
    ecmaFeatures: {
      jsx: true
    }
  },
  plugins: ["react", "prettier"],
  extends: [
    "eslint:recommended",
    "plugin:react/recommended",
    "plugin:prettier/recommended"
  ],
  rules: {
    "prettier/prettier": [
      "error",
      {
        printWidth: 140,
        singleQuote: true,
        "editor.formatOnSave": true,
        arrowParens: "always",
        jsxSingleQuote: true,
        tabWidth: 2,
        trailingComma: "none"
      }
    ],
    "no-unused-vars": "error"
  }
};
