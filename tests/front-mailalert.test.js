const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const script = fs.readFileSync(path.join(__dirname, '..', 'views', 'js', 'front.js'), 'utf8');

function setup(options = {}) {
    let prefilter;
    let ready;
    let resets = 0;
    const widget = {};
    const alert = {
        getAttribute(name) {
            return name === 'data-url' ? '/custom/alert?process=add' : null;
        },
        querySelector(selector) {
            if (selector === '.cf-turnstile') return widget;
            if (selector === 'input[name="cf-turnstile-response"]') {
                return options.rendered === false ? null : { value: options.hiddenToken || '' };
            }
            return null;
        },
    };
    const document = {
        baseURI: 'https://shop.example/product',
        readyState: 'loading',
        querySelectorAll(selector) {
            return selector === '.js-mailalert' ? [alert] : [];
        },
        querySelector(selector) {
            return selector === '.js-mailalert' ? alert : null;
        },
        addEventListener(event, callback) {
            assert.equal(event, 'DOMContentLoaded');
            ready = callback;
        },
    };
    const jquery = {
        ajaxPrefilter(callback) {
            prefilter = callback;
        },
    };
    const window = {
        mcsTurnstileConfig: { siteKey: 'site', forms: { back_in_stock: 1 } },
        turnstile: {
            getResponse(element) {
                assert.equal(element, widget);
                return options.apiToken;
            },
            reset(element) {
                assert.equal(element, widget);
                resets++;
            },
        },
    };
    if (!options.deferredJquery) window.jQuery = jquery;
    vm.runInNewContext(script, { document, window, URL, console });

    return {
        ready() {
            window.jQuery = jquery;
            ready();
        },
        send(requestOptions) {
            let complete;
            prefilter(requestOptions, {}, { always(callback) { complete = callback; } });
            return () => { if (complete) complete(); };
        },
        resets() { return resets; },
    };
}

test('forwards the widget token to the exact alert URL and refreshes it after the request', () => {
    const app = setup({ apiToken: 'token+value' });
    const request = {
        type: 'POST',
        url: 'https://shop.example/custom/alert?process=add',
        data: 'id_product=1',
    };
    const complete = app.send(request);
    assert.equal(request.data, 'id_product=1&cf-turnstile-response=token%2Bvalue');
    complete();
    assert.equal(app.resets(), 1);
});

test('registers the prefilter when jQuery loads before DOMContentLoaded', () => {
    const app = setup({ deferredJquery: true, hiddenToken: 'hidden-token' });
    app.ready();
    const request = { type: 'POST', url: '/custom/alert?process=add', data: 'id_product=1' };
    app.send(request);
    assert.equal(request.data, 'id_product=1&cf-turnstile-response=hidden-token');
});

test('does not send the token to another URL or for a GET request', () => {
    const app = setup({ hiddenToken: 'hidden-token' });
    for (const request of [
        { type: 'POST', url: '/custom/alert?process=remove', data: 'id_product=1' },
        { type: 'POST', url: 'https://other.example/custom/alert?process=add', data: 'id_product=1' },
        { type: 'GET', url: '/custom/alert?process=add', data: 'id_product=1' },
    ]) {
        app.send(request)();
        assert.equal(request.data, 'id_product=1');
    }
    assert.equal(app.resets(), 0);
});

test('does not attach a token before the widget is ready', () => {
    const app = setup({ rendered: false, apiToken: 'not-ready' });
    const request = { type: 'POST', url: '/custom/alert?process=add', data: 'id_product=1' };
    app.send(request)();
    assert.equal(request.data, 'id_product=1');
    assert.equal(app.resets(), 0);
});
