#!/usr/bin/env node

const commandLineArgs = require('command-line-args');
const blinkstick = require('blinkstick');

const options = commandLineArgs([
    { name: 'index', alias: 'i', type: Number, defaultValue: 0 },
    { name: 'color', alias: 'c', type: String, defaultValue: 'green' },
]);

const device = blinkstick.findFirst();
device.setColor(options.color, { index: options.index }, function(err) {
    if (typeof(err) !== 'undefined') {
        console.log(err);
    }
});