import { createRequire } from 'module';
const require = createRequire(import.meta.url);
const c = require('./postcss.config.cjs');
export default c;
