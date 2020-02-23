#!/usr/bin/env node

const commandLineArgs = require('command-line-args');
const blinkstick = require('blinkstick');

const options = commandLineArgs([
    { name: 'index', alias: 'i', type: Number, defaultValue: 0 },
    { name: 'duration', alias: 'd', type: Number, defaultValue: 1000 },
    { name: 'color', alias: 'c', type: String, defaultValue: 'green' },
]);

const device = blinkstick.findFirst();
device.pulse(options.color, { index: options.index, duration: options.duration }, function(err) {
    if (typeof(err) !== 'undefined') {
        console.log(err);
    }
    
    device.setColor('black', { index: options.index });
});