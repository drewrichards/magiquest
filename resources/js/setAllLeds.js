#!/usr/bin/env node

const commandLineArgs = require('command-line-args');
const blinkstick = require('blinkstick');

const options = commandLineArgs([
    { name: 'data', alias: 's', type: String, defaultValue: '[]' },
]);

const device = blinkstick.findFirst();
device.setColors(0, JSON.parse(options.data), function(err) {
    if (typeof(err) !== 'undefined') {
        console.log(err);
    }
});