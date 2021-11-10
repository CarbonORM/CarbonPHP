const {createProxyMiddleware} = require('http-proxy-middleware');

const target = 'http://dev.carbonphp.com:8080/';

module.exports = function (app) {
    app.use('/rest/**', createProxyMiddleware({
        target: target,
        changeOrigin: true,
        secure: false,
        proxyTimeout: 4000,
        pathRewrite(path, req) {
            return path.replace(/^\/rest/, 'rest')
        },
        logLevel: "debug"
    }));
    app.use(createProxyMiddleware([
        '/view/**',
        '!/' + process.env.REACT_APP_URI + '/**'
    ],{
        target: target,
        changeOrigin: true,
        secure: false,
        proxyTimeout: 4000,
        pathRewrite(path, req) {
            return path.replace(/^\/view/, 'view')
        },
        logLevel: "debug"
    }));
    app.use('/view/releases/2.0.0/**', createProxyMiddleware({
        target: target,
        changeOrigin: true,
        secure: false,
        proxyTimeout: 4000,
        pathRewrite(path, req) {
            return path.replace(/^\/view\/releases\/2.0.0/, 'view/releases/2.0.0')
        },
        logLevel: "debug"
    }));
    app.use('/carbon/**', createProxyMiddleware({
        target: target,
        changeOrigin: true,
        secure: false,
        proxyTimeout: 4000,
        pathRewrite(path, req) {
            return path.replace(/^\/carbons/, 'carbons')
        },
        logLevel: "debug"
    }));
};
