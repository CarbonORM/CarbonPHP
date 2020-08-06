const {createProxyMiddleware} = require('http-proxy-middleware');

module.exports = function (app) {
    app.use('/rest/**', createProxyMiddleware({
        target: 'http://dev.carbonphp.com:80/',
        changeOrigin: true,
        secure: false,
        proxyTimeout: 4000,
        pathRewrite(path, req) {
            return path.replace(/^\/rest/, 'rest')
        },
        logLevel: "debug"
    }));
    app.use('/carbon/**', createProxyMiddleware({
        target: 'http://dev.carbonphp.com:80/',
        changeOrigin: true,
        secure: false,
        proxyTimeout: 4000,
        pathRewrite(path, req) {
            return path.replace(/^\/carbons/, 'carbons')
        },
        logLevel: "debug"
    }));
};
