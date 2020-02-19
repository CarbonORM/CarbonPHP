const proxy = require('http-proxy-middleware');

module.exports = function (app) {
    app.use(proxy('/carbon/*', { target: 'http://dev.carbonphp.com:80/'}))
};
