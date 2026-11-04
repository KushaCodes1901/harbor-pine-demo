import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import assert from 'node:assert/strict';
const code = readFileSync(new URL('./demo.js', import.meta.url), 'utf8');
async function scenario(id, properties, valid = true) {
  const events = {};
  const form = { addEventListener: (type, fn) => { events[type] = fn; }, reportValidity: () => valid, reset: () => { form.resetCalled = true; } };
  const response = { hidden: true, focus() { this.focused = true; } };
  const selected = { hidden: true, replaceChildren(...children) { this.children = children; } };
  const button = { type: 'button' };
  const nodes = { '#hp-demo-form': form, '#hp-demo-response': response, '#hp-selected-property': selected, '#hp-demo-submit': button };
  const requests = [];
  const sandbox = {
    document: { querySelector: (selector) => nodes[selector], createElement: (tag) => ({ tag }) },
    location: { search: id === null ? '' : `?property_id=${encodeURIComponent(id)}`, pathname: '/contact/' }, URLSearchParams,
    fetch: async (url, options) => { requests.push({ url, options }); assert.equal(url, '/assets/properties.json'); assert.equal(options, undefined); return { ok: true, json: async () => properties }; },
    localStorage: { setItem() { throw new Error('Storage forbidden'); } }, sessionStorage: { setItem() { throw new Error('Storage forbidden'); } }
  };
  runInNewContext(code, sandbox);
  await new Promise((resolve) => setImmediate(resolve));
  return { events, form, response, selected, button, requests };
}
const basic = await scenario(null, {});
assert.equal(basic.selected.hidden, true);
assert.equal(basic.requests.length, 0);
assert.equal(basic.button.type, 'submit');
let prevented = false;
basic.events.submit({ preventDefault() { prevented = true; } });
assert.equal(prevented, true);
assert.equal(basic.response.hidden, false);
assert.match(basic.response.textContent, /No email was sent/);
assert.equal(basic.form.resetCalled, true);
assert.equal(basic.requests.length, 0);
const invalid = await scenario(null, {}, false);
invalid.events.submit({ preventDefault() {} });
assert.equal(invalid.response.hidden, true);
assert.equal(invalid.form.resetCalled, undefined);
const property = await scenario('24', { 24: 'The Cedar Court' });
assert.equal(property.selected.hidden, false);
assert.equal(property.selected.children[1].textContent, 'The Cedar Court');
assert.equal(property.selected.children[3].href, '/contact/#contact');
const unknown = await scenario('99999999', { 24: 'The Cedar Court' });
assert.equal(unknown.selected.hidden, true);
const malformed = await scenario('24<script>', { 24: 'The Cedar Court' });
assert.equal(malformed.requests.length, 0);
assert.equal(malformed.selected.hidden, true);
console.log('PASS: demo validation gate, confirmation, zero submission requests/storage, published selection, invalid/unknown selection, general inquiry.');
