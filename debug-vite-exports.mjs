(async ()=>{
  try{
    const m = await import('file:///D:/quanlydiemdanh/vite.config.js');
    console.log('export keys:', Object.keys(m));
    console.log('exports:', m);
  }catch(e){
    console.error('import error:', e);
  }
})();
