module.exports = {
    root: true,
    env: {
        browser: true,
        node: true,
        es6: true,
    },
    parserOptions: {
        ecmaVersion: 2021,
        sourceType: 'module',
        ecmaFeatures: {
            jsx: true,
        },
    },
    settings: {
        react: {
            version: 'detect',
        },
    },
    extends: ['eslint:recommended', 'plugin:react/recommended'],
    plugins: ['react'],
    rules: {
        'react/prop-types': 'off',
        'react/react-in-jsx-scope': 'off',
    },
};
