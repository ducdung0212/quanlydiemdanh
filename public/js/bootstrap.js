(function(){
  // Minimal axios shim: try to use window.axios if present, otherwise a tiny wrapper
  try {
    window.axios = window.axios || {};
    // If you need full axios on static setup, copy axios to public/js and include it.
  } catch (e) {
    console.warn('axios shim not applied', e);
  }
})();