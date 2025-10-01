import fs from 'fs';
const p = 'D:/quanlydiemdanh/vite.config.js';
const b = fs.readFileSync(p);
console.log('length', b.length);
console.log('first 32 bytes:', b.slice(0,32));
console.log('as utf8:', b.toString('utf8'));
