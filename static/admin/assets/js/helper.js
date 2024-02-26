function generateSlug(title, slugInput) {
    // convert the title to lowercase, replace spaces with hyphens, and remove invalid characters
    const slug = title
        .toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^\w-]/g, '');

    // set the slug value in the slugInput element
    slugInput.value = slug;
}
function sendData(data, url, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', url);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onload = () => {
        if (xhr.status === 200) {
            // console.log('Data successfully sent.');
            callback(null, xhr.responseText);
        } else {
            console.log('Request failed. Status:', xhr.status);
            callback(xhr.status);
        }
    };
    xhr.send(JSON.stringify(data));
}
function getCookie(name) {
    var nameEQ = name + "=";
    var cookies = document.cookie.split(';');
    for (var i = 0; i < cookies.length; i++) {
        var cookie = cookies[i];
        while (cookie.charAt(0) === ' ') {
            cookie = cookie.substring(1, cookie.length);
        }
        if (cookie.indexOf(nameEQ) === 0) {
            return cookie.substring(nameEQ.length, cookie.length);
        }
    }
    return null;
}
function setMenu(value, days = 10) {
    let oldcookie = getCookie('menu_show');
    if (oldcookie && oldcookie==value) {
        days = -10
    }
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = 'menu_show' + "=" + value + expires + "; path=/";
}