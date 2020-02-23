#!/usr/bin/env node

const blinkstick = require('blinkstick');

const device = blinkstick.findFirst();
for (let i = 0; i < 32; i++) {
    device.setColor('black', { index: i });
}