(function() {
    var button = document.querySelector("#button")
    var username = document.querySelector("#username")
    var password = document.querySelector("#password")

    document.onkeypress = button_onClick
    button.onclick = login

    function button_onClick(e) {
        if(e.keyCode === 13)
            login()
    }

    function login() {
        var user = username.value.trim()
        var pass = password.value

        if(!user || !pass)
            return

        Selio.selioXHR('POST', '/ajax/selio/auth', {user, pass})
            .then(function() {
                window.location.reload()
            })
    }
})()