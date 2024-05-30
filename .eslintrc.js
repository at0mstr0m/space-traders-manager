module.exports = {
  root: true,
  env: {
    node: true,
  },
  extends: [
    "plugin:vue/vue3-recommended",
    "eslint:recommended",
  ],
  rules: {
    "no-console": 0,
    "no-empty": 0,
    "no-irregular-whitespace": 0,
    "arrow-parens": ["error", "always"],
  },
};
