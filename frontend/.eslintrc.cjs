module.exports = {
  extends: ["eslint:recommended", "plugin:import/recommended", "prettier"],
  plugins: ["import"],
  rules: {
    "import/order": ["error", { alphabetize: { order: "asc" } }],
    "@typescript-eslint/no-unused-vars": ["error", { argsIgnorePattern: "^_" }],
  },
};
