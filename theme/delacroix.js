(function() {
    const selfont = document.getElementById('selfont');
    const root = document.querySelector(':root');
    if (!selfont) return;
    selfont.addEventListener('change', function(e) {
        console.log(selfont.value);
        // body.style.fontFamily = selfont.value;
        root.style.setProperty('--serif', selfont.value);
    });
})();