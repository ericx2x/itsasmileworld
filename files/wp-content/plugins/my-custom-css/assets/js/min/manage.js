!function a(o, s, c) {
    function p(t, e) {
        if (!s[t]) {
            if (!o[t]) {
                var n = "function" == typeof require && require;
                if (!e && n) return n(t, !0);
                if (u) return u(t, !0);
                var r = new Error("Cannot find module '" + t + "'");
                throw r.code = "MODULE_NOT_FOUND", r
            }
            var i = s[t] = {exports: {}};
            o[t][0].call(i.exports, function (e) {
                return p(o[t][1][e] || e)
            }, i, i.exports, a, o, s, c)
        }
        return s[t].exports
    }

    for (var u = "function" == typeof require && require, e = 0; e < c.length; e++) p(c[e]);
    return p
}({
    1: [function (e, t, n) {
        "use strict";
        !function () {
            var s = document.getElementById("custom_php_ajax_nonce").value,
                c = "-network" === pagenow.substring(pagenow.length - "-network".length);

            function e(e, t) {
                for (var n = 0; n < e.length; n++) t(e[n], n)
            }

            function i(e, t, n, r) {
                var i = t.querySelector(".column-id");
                if (i && parseInt(i.textContent)) {
                    n.id = parseInt(i.textContent), n.shared_network = !!t.className.match(/\bshared-network-snippet\b/), n.network = n.shared_network || c;
                    var a = "action=update_my_php_code&_ajax_nonce=" + s + "&field=" + e + "&snippet=" + JSON.stringify(n),
                        o = new XMLHttpRequest;
                    o.open("POST", ajaxurl, !0), o.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8"), o.onload = function () {
                        o.status < 200 || 400 <= o.status || (console.log(o.responseText), void 0 !== r && r(o))
                    }, o.send(a)
                }
            }

            function n() {
                i("priority", this.parentElement.parentElement, {priority: this.value})
            }

            function a(e, t) {
                var n = parseInt(e.textContent.replace(/\((\d+)\)/, "$1"));
                t ? n++ : n--, e.textContent = "(" + n.toString() + ")"
            }

            function r(e) {
                var n = this.parentElement.parentElement, t = n.className.match(/\b(?:in)?active-snippet\b/);
                if (t) {
                    e.preventDefault();
                    var r = "inactive-snippet" === t[0];
                    i("active", n, {active: r}, function (e) {
                        n.className = r ? n.className.replace(/\binactive-snippet\b/, "active-snippet") : n.className.replace(/\bactive-snippet\b/, "inactive-snippet");
                        var t = document.querySelector(".subsubsub");
                        a(t.querySelector(".active .count"), r), a(t.querySelector(".inactive .count"), r)
                    })
                }
            }

            e(document.getElementsByClassName("snippet-priority"), function (e, t) {
                e.addEventListener("input", n), e.disabled = !1
            }), e(document.getElementsByClassName("snippet-activation-switch"), function (e, t) {
                e.addEventListener("click", r)
            })
        }()
    }, {}]
}, {}, [1]);