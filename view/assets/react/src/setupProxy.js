const {createProxyMiddleware} = require('http-proxy-middleware');

module.exports = function (app) {
    app.use('/rest/**', createProxyMiddleware({
        target: 'http://dev.carbonphp.com:8080/',
        changeOrigin: true,
        secure: false,
        proxyTimeout: 4000,
        pathRewrite(path, req) {
            return path.replace(/^\/rest/, 'rest')
        },
        logLevel: "debug"
    }));
    app.use('/carbon/**', createProxyMiddleware({
        target: 'http://dev.carbonphp.com:8080/',
        changeOrigin: true,
        secure: false,
        proxyTimeout: 4000,
        pathRewrite(path, req) {
            return path.replace(/^\/carbons/, 'carbons')
        },
        logLevel: "debug"
    }));
    app.use('/carbon/**', createProxyMiddleware({
        target: 'ws://dev.carbonphp.com:8888/ws',
        changeOrigin: true,
        secure: false,
        ws: true,
        proxyTimeout: 4000,
        pathRewrite(path, req) {
            return path.replace(/^\/carbons/, 'carbons')
        },
        logLevel: "debug"
    }));
};
