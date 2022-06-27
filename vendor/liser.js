'use strict';
/**
 * Interface statique, essentiellement pour la liseuse
 */


/*
// faire partir la barre fixe au scrolll ?
var prevScrollpos = window.pageYOffset;
window.onscroll = function() {
  var currentScrollPos = window.pageYOffset;
  if (prevScrollpos > currentScrollPos) {
    document.getElementById("navbar").style.top = "0";
  } else {
    document.getElementById("navbar").style.top = "-50px";
  }
  prevScrollpos = currentScrollPos;
}
*/

(function() {

    const Liser = function() {
        let vh;
        let vw;
        let noterefs = [];
        let notebox;

        function init() {
            vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
            vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);

            Liser.path = window.location.href.split('#')[0];

            // bulle au survol pour lien tronqués (ellipsis)
            var els = document.querySelectorAll(".ellipsis");
            for (var i = 0, max = els.length; i < max; i++) {
                els[i].title = els[i].innerText;
            }
            noteboxInit();
            /*
            Liser.toc();
            Liser.menu();
            Liser.notebox();
            Liser.sidebar();
            */
        }








        function sidebar() {
            var sidebar = document.getElementById("sidebar");
            if (!sidebar) return false;
            /*
            var scroll2text = 310;
            var pagetitle = document.querySelector("#text h1");
            // passer le bannière si même livre
            if (lastbook != bookpath);
            else if (pagetitle) {
              scroll2text = Liser.top(pagetitle) - 60; // petite barre
              var ccmbar = document.getElementById("ccm-toolbar");
              if (ccmbar) scroll2text -= ccmbar.offsetHeight;
              window.scroll(0, scroll2text);
            }
            */
            Liser.sidebar = sidebar;
            Liser.sidebarLinks = Liser.sidebar.getElementsByTagName("a");
            window.addEventListener('scroll', Liser.sidebarScroll);


            if (Liser.vw < 992) return; // do not scroll in the sidebar if screen is too small
            var here = sidebar.querySelector("a.nav-selected");
            if (!here) here = sidebar.querySelector("li.here > a");
            if (!here) return;
            var hereY = here.offsetTop;
            if (hereY > Liser.sidebar.clientHeight + Liser.sidebar.scrollTop) {
                // var scrollto = hereY - (Liser.sidebar.clientHeight / 2);
                // avoid scroll intoview, too much effects on global scroll
                Liser.sidebar.scrollTop = hereY - (Liser.sidebar.clientHeight / 2);
            }


        }

        function sidebarScroll(e) {
            var pos = Liser.path.length + 1;
            // loop from the end, to get the smallest section container if nested sections
            for (var i = Liser.sidebarLinks.length - 1; i >= 0; i--) {
                var a = Liser.sidebarLinks[i];
                var href = a.href;
                if (href.indexOf(Liser.path) < 0) continue;
                var id = href.substr(pos);
                if (!id) continue;
                var div = document.getElementById(id);
                if (!div) continue;
                var bounding = div.getBoundingClientRect();
                var visible = bounding.top <= (window.innerHeight || document.documentElement.clientHeight) - 200 || (bounding.bottom > 0 && bounding.bottom < (window.innerHeight || document.documentElement.clientHeight));
                if (visible) break;
            }
            if (!visible) return;
            if (Liser.linkLast == a) return;
            // clean last link hilited
            if (Liser.linkLast) Liser.linkLast.classList.remove("visible");
            a.classList.add("visible");
            Liser.linkLast = a; // keep pointer on this link
            // update running head
            var runhead = document.getElementById('runhead');
            if (!runhead) return;
            var head = div.querySelector("h1, h2, h3, h4, h5, h6");
            if (head) {
                runhead.href = "#" + id;
                var html = head.innerHTML;
                html = html.replace(/<br( [^>]*)?\/?>/g, " — ")
                runhead.innerHTML = html;
            } else {
                runhead.innerHTML = "";
                runhead.href = "";
            }
        }

        function noteboxInit() {
            notebox = document.getElementById("notebox");
            if (!notebox) return false;
            noterefs = document.querySelectorAll('a.noteref');
            // var text = notebox.parentNode;
            // notebox.style.width = getComputedStyle(text).width;
            window.addEventListener('resize', Liser.noteboxResize); // garder la largeur relative de la boîte à notes
            window.addEventListener('scroll', Liser.noteboxScroll);
            // scrollPage(); // non, trop long au démarrage
        }

        function noteboxResize(event) {
            /*
            // garder la largeur de la boite à notes
            var text = this.notebox.parentNode;
            notebox.style.width = getComputedStyle(text).width;
            */
        }


        function noteboxScroll() {
            if (!noterefs || noterefs.length < 1) return;
            var up = false;
            var scrollY = window.pageYOffset || document.documentElement.scrollTop;
            if (scrollY < Liser.lastScrollY) up = true;
            Liser.lastScrollY = (scrollY <= 0) ? 0 : scrollY; // phone scroll could be negative


            // les premières notes sont elles en vue ?
            var id = noterefs[0].hash;
            if (id[0] == '#') id = id.substring(1);
            var note = document.getElementById(id);
            if (isVisible(note)) {
                notebox.innerHTML = "";
                notebox.style.visibility = "hidden";
                return;
            }
            var count = 0;
            for (var i = 0, len = noterefs.length; i < len; i++) {
                var ref = noterefs[i];
                var id = ref.hash;
                if (id[0] == '#') id = id.substring(1);
                var idCopy = id + "Copy";
                var noteCopy = document.getElementById(idCopy);
                if (isVisible(ref)) {

                    count++;
                    if (noteCopy) continue; // déjà affichée
                    var note = document.getElementById(id);
                    if (!note) continue; // no note ? bad
                    noteCopy = note.cloneNode(true);
                    noteCopy.id = idCopy;
                    if (up) notebox.insertBefore(noteCopy, notebox.firstChild); // prepend not for MS
                    else notebox.appendChild(noteCopy);
                } else {
                    if (!noteCopy) continue;
                    noteCopy.parentNode.removeChild(noteCopy);
                }
            }
            if (!count) notebox.style.visibility = "hidden";
            else notebox.style.visibility = "visible";
        }

        /**
         * Get an absolute y coordinate for an object
         * [FG] : buggy with absolute object
         * <http://www.quirksmode.org/js/findpos.html>
         *
         * @param object element
         */
        function top(node) {
            var top = 0;
            do {
                top += node.offsetTop;
                node = node.offsetParent;
            } while (node && node.tagName.toLowerCase() != 'body');
            return top;
        }

        /**
         * Is element in viewPort
         * @param {} elem 
         * @returns 
         */
        function isVisible(elem) {
            var bounding = elem.getBoundingClientRect();
            return (
                bounding.top >= 0 &&
                bounding.left >= 0 &&
                bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                bounding.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }

        /**
         * 
         */
        function shrink() {
            if (scrollMother.scrollTop > 50) {
                if (document.body.className.match(/\bshrink\b/));
                else document.body.className += " shrink";
            } else {
                document.body.className = document.body.className.replace(/ *\bshrink\b */g, "");
            }
        }

        function getScrollParent(node) {
            if (node == null) return null;
            if (node.scrollTop) return node;
            return getScrollParent(node.parentNode);
        }

        return {
            init: init,
            noteboxResize: noteboxResize,
            noteboxScroll: noteboxScroll,
        }


    }();

    // ceate notebox at the right place
    const main = document.querySelector("article.teinte div.main");
    // bottom script, do not wait for images or other scripts
    if (main) {
        const notebox = document.createElement('div');
        notebox.id = 'notebox';
        main.appendChild(notebox);
        Liser.init();
    }

    /** Capture all hash links to avoid history entries */
    window.addEventListener('click', function(event) {
        const a = event.target.closest("a");
        if (!a) return;
        if (a.pathname != location.pathname) return; // external link, let it
        // history.replaceState(undefined, undefined, "#hash");
        const id = a.hash.substr(1);
        if (!id) return;
        const el = document.getElementById(id);
        if (!el) return;
        // scroll is smooth but element:target do not fire with history.replaceState
        // https://github.com/whatwg/html/issues/639#issuecomment-213084467
        /*
        if (el.scrollIntoView) {
          var behave = false;
          if ('scrollBehavior' in document.documentElement.style) behave = {behavior: "smooth", block: "start", inline: "nearest"};
          el.scrollIntoView(behave);
        }
          history.replaceState(null, '', '#'+id);
        */
        window.location.replace('#' + id);
        event.preventDefault();
    }, false);

})();