"strict mode"

var Selio = Object.freeze((function () {
    /**
     * Holds information about Selio's javascript environment.
     */
    var ENV = Object.freeze((function() {
        var element = document.querySelector("#selio_js_env")
        return element ? JSON.parse(element.textContent) : {}
    })())


    /**
     * Sends an XMLHttpRequest and returns a Promise.
     * - then(response)
     * - catch(response, status, statusText)
     * @param {string} method The request method.
     * - Any methods supported by the browser can be passed.
     * @param {string} url The request URL.
     * @param {Object} data Request data as an object of key/value pairs.
     * @param {Object} headers Additional request headers as an object of
     * key/value pairs.
     */
    function fetch(method, url, data, headers) {
        var options = {
            cache: "no-cache",
            credentials: "same-origin",
            headers: headers || {},
            method: method,
            mode: "cors",
            redirect: "follow",
            referrer: "no-referrer"
        }

        if(method === "GET") {
            var index = url.indexOf("?")
            var cleanURL = index >= 0 ? url.substring(0, index) : url
            url = cleanURL + "?" + Selio.joinQuery(data)
        }
        else {
            options.body = JSON.stringify(data)
        }

        return window.fetch(url, options)
            .then(function(response) {
                return response.text()
            })
    }

    /**
     * Sends an XMLHttpRequest with URL in Selio format and returns a Promise.
     * - then(response)
     * - catch(response, status, statusText)
     * @param {string} method The request method.
     * - Any methods supported by the browser can be passed.
     * @param {string} url The request URL.
     * @param {Object} data Request data as an object of key/value pairs.
     * @param {Object} headers Additional request headers as an object of
     * key/value pairs.
     */
    function selioFetch(method, url, data, headers) {
        var lang = Selio.ENV.language ? '/' + Selio.ENV.language : ''
        var prefix = Selio.ENV.urlPrefix || ''
        url = prefix + lang + url

        return Selio.fetch(method, url, data, headers)
    }


    /**
     * Appends a stylesheet link element to the page's head element.
     * Does not append if a link element with the same url
     * is already loaded to the page.
     * @param {string} url The url to the stylesheet.
     * @param {Object} attributes Attributes to be set when creating the element.
     */
    function loadStyle(url, attributes) {
        if(!document.querySelector('link[href="' + url + '"]')) {
            var link = document.createElement("link")
            link.rel = "stylesheet"
            link.href = url
            return setAttributesAndAppendElement(link, attributes)
        }
        return null
    }
    /**
     * Appends a script element to the page's head element.
     * Does not append if a script element with the same url
     * is already loaded to the page.
     * @param {string} url The url to the script.
     * @param {Object} attributes Attributes to be set when creating the element.
     */
    function loadScript(url, attributes) {
        if(!document.querySelector('script[src="' + url + '"]')) {
            var script = document.createElement("script")
            script.src = url
            return setAttributesAndAppendElement(script, attributes)
        }
        return null
    }
    function setAttributesAndAppendElement(element, attributes) {
        if(attributes && typeof attributes === "object") {
            for(var name in attributes)
                element.setAttribute(name, attributes[name])
        }
        document.getElementsByTagName("head")[0].appendChild(element)
        return element
    }


    /**
     * Splits a query string into an object of key/value pairs.
     * @param {string} queryStr The query string to be split into object.
     */
    function splitQuery(queryStr) {
       if(typeof queryStr == 'string' && query !== "") {
            if(queryStr.indexOf("#") != 0 && query.indexOf("?") != 0)
                var q = queryStr.trim().split('&')
            else
                var q = queryStr.trim().substring(1).split('&')

            var split = {}
            for(var i = 0; i < q.length; i++) {
                var tempSplit = q[i].split('=')
                split[decodeURIComponent(tempSplit[0])] = decodeURIComponent(tempSplit[1])
            }
            return split
        }
        return {}
    }
    /**
     * Joins object's keys and values into a valid encoded query string.
     * @param {Object} queryObj The object to be joined as a query string.
     */
    function joinQuery(queryObj) {
        if(queryObj instanceof Object) {
            var values = []
            for(var key in queryObj) {
                values.push(encodeURIComponent(key) + "="
                    + encodeURIComponent(queryObj[key]))
            }
            return values.join("&")
        }
        return ""
    }


    return {
        // Fields.
        ENV: ENV,


        // Methods.
        fetch: fetch,
        selioFetch: selioFetch,
        loadStyle: loadStyle,
        loadScript: loadScript,
        splitQuery: splitQuery,
        joinQuery: joinQuery
    }
})())
